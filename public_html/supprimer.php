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
requireRole($config['roles_delete']);

$conn = connexion();

// ===== RECUPERATION DE LA PK (simple ou composite) =====
if (isset($config['pk'])) {
    // PK simple — transmise via ?id=...
    $pk  = $config['pk'];
    $id  = $_GET['id'] ?? '';
    if ($id === '') {
        header('Location: liste.php?table=' . urlencode($table));
        exit;
    }
} elseif (isset($config['pk_multiple'])) {
    // PK composite — transmise via ?col1=...&col2=...
    foreach ($config['pk_multiple'] as $pkc) {
        if (!isset($_GET[$pkc])) {
            header('Location: liste.php?table=' . urlencode($table));
            exit;
        }
    }
} else {
    header('Location: dashboard/');
    exit;
}

// ===== CHARGEMENT DE L'ENREGISTREMENT A SUPPRIMER =====
// (pour afficher un résumé à l'utilisateur avant confirmation)
if (isset($config['pk'])) {
    $sql_select  = "SELECT * FROM {$config['table']} WHERE $pk = :id";
    $stid_select = oci_parse($conn, $sql_select);
    oci_bind_by_name($stid_select, ":id", $id);
} else {
    $conditions = [];
    foreach ($config['pk_multiple'] as $pkc) {
        $conditions[] = $config['colonnes'][$pkc]['type'] === 'date' ? "$pkc = TO_DATE(:$pkc, 'DD/MM/YYYY')" : "RTRIM($pkc) = RTRIM(:$pkc)";
    }
    $sql_select  = "SELECT * FROM {$config['table']} WHERE " . implode(' AND ', $conditions);
    $stid_select = oci_parse($conn, $sql_select);
    foreach ($config['pk_multiple'] as $pkc) {
        oci_bind_by_name($stid_select, ":$pkc", $_GET[$pkc]);
    }
}

oci_execute($stid_select);
$data = oci_fetch_array($stid_select, OCI_ASSOC + OCI_RETURN_NULLS);
oci_free_statement($stid_select);

if (!$data) {
    oci_close($conn);
    header('Location: liste.php?table=' . urlencode($table));
    exit;
}

$error   = '';
$success = '';

// ===== TRAITEMENT DE LA SUPPRESSION =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer'])) {

    if (isset($config['pk'])) {
        $sql_del  = "DELETE FROM {$config['table']} WHERE $pk = :id";
        $stid_del = oci_parse($conn, $sql_del);
        oci_bind_by_name($stid_del, ":id", $id);
    } else {
        $conditions = [];
        foreach ($config['pk_multiple'] as $pkc) {
            $conditions[] = $config['colonnes'][$pkc]['type'] === 'date' ? "$pkc = TO_DATE(:$pkc, 'DD/MM/YYYY')" : "RTRIM($pkc) = RTRIM(:$pkc)";
        }
        $sql_del  = "DELETE FROM {$config['table']} WHERE " . implode(' AND ', $conditions);
        $stid_del = oci_parse($conn, $sql_del);
        foreach ($config['pk_multiple'] as $pkc) {
            oci_bind_by_name($stid_del, ":$pkc", $_GET[$pkc]);
        }
    }

    $res = @oci_execute($stid_del, OCI_COMMIT_ON_SUCCESS);
    $statementError = $res ? [] : oci_error($stid_del);
    oci_free_statement($stid_del);

    if ($res) {
        oci_close($conn);
        header('Location: liste.php?table=' . urlencode($table) . '&deleted=1');
        exit;
    } else {
        $e = $statementError;
        $error = "Erreur : " . htmlspecialchars($e['message'] ?? 'Erreur inconnue');
    }
}

oci_close($conn);

$page_title = "Supprimer — " . $config['label_single'];
include(__DIR__ . "/includes/header.php");
?>

<div class="page-header">
    <div>
        <h1>🗑️ Supprimer — <?= htmlspecialchars($config['label_single']) ?></h1>
    </div>
    <a href="liste.php?table=<?= urlencode($table) ?>" class="btn btn-secondary">← Retour à la liste</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- RESUME DE L'ENREGISTREMENT A SUPPRIMER -->
<div class="card" style="margin-bottom:2rem">
    <div class="card-title">⚠️ Êtes-vous sûr de vouloir supprimer cet élément ?</div>
    <p style="color:var(--texte-doux);margin-bottom:1.5rem">
        Cette action est <strong>irréversible</strong>. L'élément suivant sera définitivement supprimé :
    </p>
    <div class="detail-grid">
        <?php foreach ($config['colonnes'] as $col => $def): ?>
        <div class="detail-item">
            <span class="detail-label"><?= htmlspecialchars($def['label']) ?></span>
            <span class="detail-value">
                <?php
                if ($def['type'] === 'password') {
                    echo '••••••••';
                } elseif ($def['type'] === 'bool') {
                    echo ($data[strtoupper($col)] == 1) ? '⚠️ Oui' : 'Non';
                } else {
                    echo htmlspecialchars($data[strtoupper($col)] ?? '—');
                }
                ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- FORMULAIRE DE CONFIRMATION -->
<form method="POST" action="supprimer.php?table=<?= urlencode($table) ?><?php
    // On retransmet la PK dans l'URL pour le POST
    if (isset($config['pk'])) {
        echo '&id=' . urlencode($id);
    } else {
        foreach ($config['pk_multiple'] as $pkc) {
            echo '&' . $pkc . '=' . urlencode($_GET[$pkc]);
        }
    }
?>">
    <?= csrfField() ?>
    <div style="display:flex;gap:1rem;margin-top:1rem">
        <button type="submit" name="confirmer" value="1" class="btn btn-danger">
            🗑️ Confirmer la suppression
        </button>
        <a href="liste.php?table=<?= urlencode($table) ?>" class="btn btn-secondary">
            Annuler
        </a>
    </div>
</form>

<?php include(__DIR__ . "/includes/footer.php"); ?>
