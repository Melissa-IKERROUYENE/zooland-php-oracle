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
requireRole($config['roles_voir']);



$conn = connexion();

// ===== CHARGEMENT DONNEES AVEC JOINTURES FK =====
$select_parts = [];
$join_parts   = [];
$alias_count  = 0;

foreach ($config['colonnes'] as $col => $def) {
    if ($def['type'] === 'fk') {
        $alias = 'fk' . $alias_count++;
        $select_parts[] = "t.$col, $alias.{$def['fk_label']} AS {$col}_label";
        $join_parts[]   = "LEFT JOIN {$def['fk_table']} $alias ON t.$col = $alias.{$def['fk_pk']}";
    } elseif ($def['type'] === 'date') {
        $select_parts[] = "TO_CHAR(t.$col, 'DD/MM/YYYY') AS $col";
    } else {
        $select_parts[] = "t.$col";
    }
}

$select = implode(', ', $select_parts);
$joins  = implode(' ', $join_parts);


// ===== GESTION PK SIMPLE OU MULTIPLE =====
if (isset($config['pk'])) {
    $pk = $config['pk'];
$id = $_GET['id'] ?? '';


    if ($id === '') {
        header('Location: liste.php?table=' . urlencode($table));
        exit;
    }

    $sql  = "SELECT $select FROM {$config['table']} t $joins WHERE t.$pk = :$pk";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":$pk", $id);

} elseif (isset($config['pk_multiple'])) {

    $conditions = [];
    foreach ($config['pk_multiple'] as $pkc) {
        if (!isset($_GET[$pkc])) {
            header('Location: liste.php?table=' . urlencode($table));
            exit;
        }
        $conditions[] = $config['colonnes'][$pkc]['type'] === 'date' ? "t.$pkc = TO_DATE(:$pkc, 'DD/MM/YYYY')" : "RTRIM(t.$pkc) = RTRIM(:$pkc)";
    }

    $where_pk = implode(' AND ', $conditions);
    $sql  = "SELECT $select FROM {$config['table']} t $joins WHERE $where_pk";
    $stid = oci_parse($conn, $sql);

    foreach ($config['pk_multiple'] as $pkc) {
        oci_bind_by_name($stid, ":$pkc", $_GET[$pkc]);
    }
}
oci_execute($stid);
$data = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
oci_free_statement($stid);

if (!$data) {
    oci_close($conn);
    header('Location: liste.php?table=' . urlencode($table));
    exit;
}

// ===== CHARGEMENT DES RELATIONS =====
$relations_data = [];
if (!empty($config['relations'])) {
    foreach ($config['relations'] as $rel) {
        if (!hasRole($rel['roles'])) continue;

        $rel_config = $TABLES_CONFIG[$rel['table']] ?? null;
        if (!$rel_config || !hasRole($rel_config['roles_voir'])) continue;

        if (isset($config['pk'])) {
            $fk_val = $data[strtoupper($config['pk'])];
        } else {
            $fk_val = $data[strtoupper($rel['fk'])] ?? null;
        }

        if ($fk_val === null) continue;

        $sql_rel = "SELECT * FROM {$rel['table']} WHERE {$rel['fk']} = :fk_val ORDER BY 1";
        $stid_rel = oci_parse($conn, $sql_rel);
        oci_bind_by_name($stid_rel, ":fk_val", $fk_val);
        oci_execute($stid_rel);

        $rows_rel = [];
        while ($row = oci_fetch_array($stid_rel, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $rows_rel[] = $row;
        }

        oci_free_statement($stid_rel);

        $relations_data[] = [
            'label'  => $rel['label'],
            'table'  => $rel['table'],
            'config' => $rel_config,
            'rows'   => $rows_rel,
        ];
    }
}

oci_close($conn);

$page_title = "Détail — " . $config['label_single'];
include(__DIR__ . "/includes/header.php");
?>

<div class="page-header">
    <div>
        <h1>👁️ <?= htmlspecialchars($config['label_single']) ?></h1>
    </div>
    <div style="display:flex;gap:.5rem">
        <?php if (hasRole($config['roles_edit'])): ?>
        <?php
if (isset($config['pk'])) {
    $params = 'id=' . urlencode($_GET['id']);
} else {
    $parts = [];
    foreach ($config['pk_multiple'] as $pkc) {
        $parts[] = $pkc . '=' . urlencode($_GET[$pkc]);
    }
    $params = implode('&', $parts);
}
?>
<a href="modifier.php?table=<?= urlencode($table) ?>&<?= $params ?>" class="btn btn-or">✏️ Modifier</a>
        <?php endif; ?>
        <a href="liste.php?table=<?= urlencode($table) ?>" class="btn btn-secondary">← Retour à la liste</a>
    </div>
</div>

<!-- INFORMATIONS PRINCIPALES -->
<div class="card" style="margin-bottom:2rem">
    <div class="card-title">Informations</div>
    <div class="detail-grid">
        <?php foreach ($config['colonnes'] as $col => $def): ?>
        <div class="detail-item">
            <span class="detail-label"><?= htmlspecialchars($def['label']) ?></span>
            <span class="detail-value">
                <?php
                if ($def['type'] === 'fk') {
                    $val = $data[strtoupper($col . '_label')] ?? '—';
                } elseif ($def['type'] === 'bool') {
                    $val = ($data[strtoupper($col)] == 1) ? '⚠️ Oui' : 'Non';
                } elseif ($def['type'] === 'password') {
                    $val = '••••••••';
                } else {
                    $val = $data[strtoupper($col)] ?? '—';
                }
                echo htmlspecialchars($val ?? '—');
                ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- RELATIONS -->
<?php foreach ($relations_data as $rel): ?>
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-title"><?= htmlspecialchars($rel['label']) ?> (<?= count($rel['rows']) ?>)</div>
    <?php if (empty($rel['rows'])): ?>
        <p style="color:var(--texte-doux);font-size:.9rem">Aucun élément.</p>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <?php foreach ($rel['config']['liste_colonnes'] as $col): ?>
                    <th><?= htmlspecialchars($rel['config']['colonnes'][$col]['label']) ?></th>
                    <?php endforeach; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rel['rows'] as $row): ?>
                <tr>
                    <?php foreach ($rel['config']['liste_colonnes'] as $col): ?>
                    <td><?= htmlspecialchars($row[strtoupper($col)] ?? '—') ?></td>
                    <?php endforeach; ?>

<td class="td-actions">

<?php

if (isset($rel['config']['pk'])) {
    $params = 'id=' . urlencode($row[strtoupper($rel['config']['pk'])]);
} else {
    $parts = [];
    foreach ($rel['config']['pk_multiple'] as $pkc) {
        $parts[] = $pkc . '=' . urlencode($row[strtoupper($pkc)]);
    }
    $params = implode('&', $parts);
}
?>

<?php if ($rel['config']['detail']): ?>
<a href="detail.php?table=<?= urlencode($rel['table']) ?>&<?= $params ?>"
   class="btn btn-sm btn-secondary">👁️</a>
<?php endif; ?>

<?php if (hasRole($rel['config']['roles_edit'])): ?>
<a href="modifier.php?table=<?= urlencode($rel['table']) ?>&<?= $params ?>"
   class="btn btn-sm btn-or">✏️</a>
<?php endif; ?>

</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    <?php if (hasRole($rel['config']['roles_edit'])): ?>
    <div style="margin-top:1rem">
        <a href="ajouter.php?table=<?= urlencode($rel['table']) ?>" class="btn btn-sm btn-primary">➕ Ajouter</a>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<?php include(__DIR__ . "/includes/footer.php"); ?>
