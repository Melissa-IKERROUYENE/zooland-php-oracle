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

$conn    = connexion();
$success = '';
$error   = '';
$errors  = [];

// ===== CHARGEMENT DES OPTIONS FK =====
$fk_options = [];
foreach ($config['colonnes'] as $col => $def) {
    if ($def['type'] === 'fk') {
        $sql_fk = "SELECT {$def['fk_pk']}, {$def['fk_label']} FROM {$def['fk_table']} ORDER BY 2";
        $stid_fk = oci_parse($conn, $sql_fk);
        oci_execute($stid_fk);
        $fk_options[$col] = [];
        while ($row = oci_fetch_array($stid_fk, OCI_ASSOC)) {
            $fk_options[$col][] = $row;
        }
        oci_free_statement($stid_fk);
    }
}

// ===== TRAITEMENT DU FORMULAIRE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = [];

    foreach ($config['colonnes'] as $col => $def) {
        $raw = $_POST[$col] ?? '';
        $val = $def['type'] === 'password' ? $raw : trim($raw);
        if (!empty($def['pk']) && $def['type'] === 'char') { $val = strtoupper($val); }
        $problem = validateValue($val, $def);
        if ($problem !== null) { $errors[$col] = $problem; }
        if ($def['type'] === 'password' && $val !== '' && $problem === null) { $val = password_hash($val, PASSWORD_BCRYPT); }
        $values[$col] = $val === '' ? null : $val;
    }
    $errors += validateBusiness($values);

    if (empty($errors)) {
        // Construction de la requête INSERT
        $cols_sql = implode(', ', array_keys($values));
        $bind_sql = implode(', ', array_map(fn($c) => ":$c", array_keys($values)));

        // Gestion des dates avec TO_DATE
        $bind_parts = [];
        foreach ($values as $col => $val) {
            $def = $config['colonnes'][$col];
            if ($def['type'] === 'date' && $val !== null) {
                $bind_parts[] = "TO_DATE(:$col, 'DD/MM/YYYY')";
            } else {
                $bind_parts[] = ":$col";
            }
        }
        $bind_sql = implode(', ', $bind_parts);

        $sql  = "INSERT INTO {$config['table']} ($cols_sql) VALUES ($bind_sql)";
        $stid = oci_parse($conn, $sql);

        foreach ($values as $col => $val) {
            oci_bind_by_name($stid, ":$col", $values[$col]);
        }

        $res = @oci_execute($stid, OCI_COMMIT_ON_SUCCESS);
        $statementError = $res ? [] : oci_error($stid);
        oci_free_statement($stid);

        if ($res) {
            $success = "Ajout effectué avec succès !";
        } else {
            $e = $statementError;
            $error = "Erreur lors de l'ajout : " . htmlspecialchars($e['message'] ?? 'Erreur inconnue');
        }
    }
}

oci_close($conn);

$page_title = "Ajouter — " . $config['label'];
include(__DIR__ . "/includes/header.php");
?>

<div class="page-header">
    <div>
        <h1>➕ Ajouter — <?= htmlspecialchars($config['label_single']) ?></h1>
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
    <form method="POST" action="ajouter.php?table=<?= urlencode($table) ?>" autocomplete="off">
        <?= csrfField() ?>
        <?php foreach ($config['colonnes'] as $col => $def): ?>
        <div class="form-group">
            <label for="<?= $col ?>">
                <?= htmlspecialchars($def['label']) ?>
                <?php if ($def['required']): ?><span style="color:var(--erreur)">*</span><?php endif; ?>
            </label>

            <?php if ($def['type'] === 'select'): ?>
                <select id="<?= $col ?>" name="<?= $col ?>">
                    <option value="">-- Choisir --</option>
                    <?php foreach ($def['options'] as $opt): ?>
                    <option value="<?= htmlspecialchars($opt) ?>"
                        <?= ($_POST[$col] ?? '') === $opt ? 'selected' : '' ?>>
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
                        <?= ($_POST[$col] ?? '') === $opt[$fk_pk_key] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($opt[$fk_label_key]) ?>
                    </option>
                    <?php endforeach; ?>
                </select>

            <?php elseif ($def['type'] === 'bool'): ?>
                <select id="<?= $col ?>" name="<?= $col ?>">
                    <option value="0" <?= ($_POST[$col] ?? '0') === '0' ? 'selected' : '' ?>>Non</option>
                    <option value="1" <?= ($_POST[$col] ?? '') === '1' ? 'selected' : '' ?>>Oui</option>
                </select>

            <?php elseif ($def['type'] === 'date'): ?>
    		<input type="text" id="<?= $col ?>" name="<?= $col ?>"
           placeholder="JJ/MM/AAAA"
           value="<?= htmlspecialchars($_POST[$col] ?? '') ?>">
   		 <span class="hint">Format : JJ/MM/AAAA (ex: 20/03/2026)</span>

            <?php elseif ($def['type'] === 'password'): ?>
                <input type="password" id="<?= $col ?>" name="<?= $col ?>"
                       maxlength="<?= $def['max'] ?? 255 ?>"
                       placeholder="Mot de passe">
                <span class="hint">Sera stocké en hash bcrypt.</span>

            <?php elseif ($def['type'] === 'number'): ?>
                <input type="number" step="any" id="<?= $col ?>" name="<?= $col ?>"
                       value="<?= htmlspecialchars($_POST[$col] ?? '') ?>">

            <?php else: ?>
                <input type="text" id="<?= $col ?>" name="<?= $col ?>"
                       maxlength="<?= $def['max'] ?? 255 ?>"
                       value="<?= htmlspecialchars($_POST[$col] ?? '') ?>">
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
