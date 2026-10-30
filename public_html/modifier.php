<?php

include(__DIR__ . "/includes/session.php");
requireLogin();
include(__DIR__ . "/connex.inc.php");
include(__DIR__ . "/includes/tables_config.php");

$table = $_GET['table'] ?? '';
if (!isset($TABLES_CONFIG[$table])) {
    header('Location: dashboard/');
    exit;
}

$config = $TABLES_CONFIG[$table];
requireRole($config['roles_edit']);

$conn   = connexion();
$error  = '';
$success = '';
$errors = [];

$keyWhere = recordWhere($config);
$keyQuery = isset($config['pk']) ? ['id'=>$_GET['id']] : array_intersect_key($_GET, array_flip(primaryColumns($config)));
$recordUrl = http_build_query(['table'=>$table] + $keyQuery);
$selectCols = recordSelect($config);

// ===== CHARGEMENT DES OPTIONS FK =====
$fk_options = [];
foreach ($config['colonnes'] as $col => $def) {
    if ($def['type'] === 'fk') {
        $sql_fk  = "SELECT {$def['fk_pk']}, {$def['fk_label']} FROM {$def['fk_table']} ORDER BY 2";
        $stid_fk = oci_parse($conn, $sql_fk);
        oci_execute($stid_fk);
        $fk_options[$col] = [];
        while ($row = oci_fetch_array($stid_fk, OCI_ASSOC)) {
            $fk_options[$col][] = $row;
        }
        oci_free_statement($stid_fk);
    }
}

// ===== CHARGEMENT DES DONNEES EXISTANTES =====
$sql = "SELECT $selectCols FROM {$config['table']} WHERE $keyWhere";
$stid = oci_parse($conn, $sql);
bindRecordKey($stid, $config);
oci_execute($stid);
$data = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
oci_free_statement($stid);

if (!$data) {
    header('Location: liste.php?table=' . urlencode($table));
    exit;
}

// ===== TRAITEMENT DU FORMULAIRE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = [];

    foreach ($config['colonnes'] as $col => $def) {
        if (in_array($col, primaryColumns($config), true)) { continue; }
        $raw = $_POST[$col] ?? '';
        $val = $def['type'] === 'password' ? $raw : trim($raw);
        if ($def['type'] === 'password' && $val === '') { continue; }
        $problem = validateValue($val, $def);
        if ($problem !== null) { $errors[$col] = $problem; }
        if ($def['type'] === 'password' && $problem === null) { $val = password_hash($val, PASSWORD_BCRYPT); }
        $values[$col] = $val === '' ? null : $val;
    }
    $originalValues = array_change_key_case($data, CASE_LOWER);
    $errors += validateBusiness($values + $originalValues);
    if (!$values) { $errors['_form'] = 'Aucun champ modifiable.'; $error = $errors['_form']; }

    if (empty($errors)) {
        $set_parts = [];
        foreach ($values as $col => $val) {
            $def = $config['colonnes'][$col];
            if ($def['type'] === 'date' && $val !== null) {
                $set_parts[] = "$col = TO_DATE(:$col, 'DD/MM/YYYY')";
            } else {
                $set_parts[] = "$col = :$col";
            }
        }
        $set_sql = implode(', ', $set_parts);
        $sql     = "UPDATE {$config['table']} SET $set_sql WHERE $keyWhere";
        $stid    = oci_parse($conn, $sql);

        foreach ($values as $col => $val) {
            oci_bind_by_name($stid, ":$col", $values[$col]);
        }
        bindRecordKey($stid, $config);

        $res = @oci_execute($stid, OCI_COMMIT_ON_SUCCESS);
        $statementError = $res ? [] : oci_error($stid);
        oci_free_statement($stid);

        if ($res) {
            $success = "Modification effectuée avec succès !";
            // Recharger les données
            $sql  = "SELECT $selectCols FROM {$config['table']} WHERE $keyWhere";
            $stid = oci_parse($conn, $sql);
            bindRecordKey($stid, $config);
            oci_execute($stid);
            $data = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
            oci_free_statement($stid);
        } else {
            $e = $statementError;
            $error = "Erreur : " . htmlspecialchars($e['message'] ?? 'Erreur inconnue');
        }
    }
}

oci_close($conn);

$page_title = "Modifier — " . $config['label_single'];
include(__DIR__ . "/includes/header.php");
?>

<div class="page-header">
    <div>
        <h1>✏️ Modifier — <?= htmlspecialchars($config['label_single']) ?></h1>
    </div>
    <a href="liste.php?table=<?= urlencode($table) ?>" class="btn btn-secondary">← Retour à la liste</a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="modifier.php?<?= htmlspecialchars($recordUrl, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
        <?= csrfField() ?>
        <?php foreach ($config['colonnes'] as $col => $def): ?>
        <div class="form-group">
            <label for="<?= $col ?>">
                <?= htmlspecialchars($def['label']) ?>
                <?php if ($def['required']): ?><span style="color:var(--erreur)">*</span><?php endif; ?>
            </label>

            <?php
            // Valeur actuelle en BD
            $current_val = $data[strtoupper($col)] ?? '';
            // Valeur soumise par POST (si formulaire déjà soumis)
            $post_val = $_POST[$col] ?? $current_val;
            ?>

            <?php if (in_array($col, primaryColumns($config), true)): ?>
                <input type="text" value="<?= htmlspecialchars($current_val) ?>" disabled style="background:#f5f5f5">

            <?php elseif ($def['type'] === 'select'): ?>
                <select id="<?= $col ?>" name="<?= $col ?>">
                    <option value="">-- Choisir --</option>
                    <?php foreach ($def['options'] as $opt): ?>
                    <option value="<?= htmlspecialchars($opt) ?>"
                        <?= $post_val === $opt ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($opt)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>

            <?php elseif ($def['type'] === 'fk'): ?>
                <select id="<?= $col ?>" name="<?= $col ?>">
                    <option value="">-- Choisir --</option>
                    <?php foreach ($fk_options[$col] as $opt): ?>
                    <?php
                        $fk_pk_key    = strtoupper($def['fk_pk']);
                        $fk_label_key = strtoupper($def['fk_label']);
                    ?>
                    <option value="<?= htmlspecialchars($opt[$fk_pk_key]) ?>"
                        <?= trim($post_val) === trim($opt[$fk_pk_key]) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($opt[$fk_label_key]) ?>
                    </option>
                    <?php endforeach; ?>
                </select>

            <?php elseif ($def['type'] === 'bool'): ?>
                <select id="<?= $col ?>" name="<?= $col ?>">
                    <option value="0" <?= $post_val == 0 ? 'selected' : '' ?>>Non</option>
                    <option value="1" <?= $post_val == 1 ? 'selected' : '' ?>>Oui</option>
                </select>

            <?php elseif ($def['type'] === 'date'): ?>
		    <?php
		    // Formater la date en DD/MM/YYYY si elle vient d'Oracle (format court)
		    $date_val = $post_val ?? '';
		    if (isset($_POST[$col])) {
			$date_val = $_POST[$col];
		    } elseif ($current_val) {
			// Oracle retourne DD/MM/YY → on convertit en DD/MM/YYYY
			if (preg_match('/^(\d{2})\/(\d{2})\/(\d{2})$/', $current_val, $m)) {
			    $date_val = $m[1] . '/' . $m[2] . '/20' . $m[3];
			} else {
			    $date_val = $current_val;
			}
		    }
		    ?>
		    <input type="text" id="<?= $col ?>" name="<?= $col ?>"
			   value="<?= htmlspecialchars($date_val) ?>"
			   placeholder="JJ/MM/AAAA">
		    <span class="hint">Format : JJ/MM/AAAA</span>

            <?php elseif ($def['type'] === 'password'): ?>
                <input type="password" id="<?= $col ?>" name="<?= $col ?>"
                       placeholder="Laisser vide pour ne pas modifier">
                <span class="hint">Laisser vide pour conserver l'actuel.</span>

            <?php elseif ($def['type'] === 'number'): ?>
                <input type="number" step="any" id="<?= $col ?>" name="<?= $col ?>"
                       value="<?= htmlspecialchars($post_val ?? '') ?>">

            <?php else: ?>
                <input type="text" id="<?= $col ?>" name="<?= $col ?>"
                       maxlength="<?= $def['max'] ?? 255 ?>"
                       value="<?= htmlspecialchars($post_val ?? '') ?>">
            <?php endif; ?>

            <?php if (isset($errors[$col])): ?>
                <span class="error">⚠️ <?= htmlspecialchars($errors[$col]) ?></span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <div style="display:flex;gap:1rem;margin-top:1.5rem">
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
            <a href="liste.php?table=<?= urlencode($table) ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php include(__DIR__ . "/includes/footer.php"); ?>
