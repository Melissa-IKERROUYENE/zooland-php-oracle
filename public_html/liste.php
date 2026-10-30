<?php

include(__DIR__ . "/includes/session.php");
requireLogin();
include(__DIR__ . "/connex.inc.php");
include(__DIR__ . "/includes/tables_config.php");

// Vérification que la table existe dans la config
$table = $_GET['table'] ??  '';
if (!isset($TABLES_CONFIG[$table])) {
    header('Location: dashboard/');
    exit;
}

$config = $TABLES_CONFIG[$table];
$user   = currentUser();

// Vérification des droits
requireRole($config['roles_voir']);

$conn   = connexion();
$search = trim($_GET['search'] ?? '');

// ===== CONSTRUCTION DE LA REQUETE =====
// On récupère les colonnes à afficher dans la liste
$liste_cols = $config['liste_colonnes'];

// Pour chaque colonne FK on joint la table étrangère
$select_parts = [];
$join_parts   = [];
$alias_count  = 0;

foreach ($config['colonnes'] as $col => $def) {
    if (!in_array($col, $liste_cols)) continue;

    if ($def['type'] === 'fk') {
        $alias = 'fk' . $alias_count++;
        $fk_table = $def['fk_table'];
        $fk_pk    = $def['fk_pk'];
        $fk_label = $def['fk_label'];
        $select_parts[] = "$alias.$fk_label AS {$col}_label";
        $join_parts[]   = "LEFT JOIN $fk_table $alias ON t.$col = $alias.$fk_pk";
    } elseif ($def['type'] === 'bool') {
        $select_parts[] = "t.$col";
    } elseif ($def['type'] === 'date') {
        $select_parts[] = "TO_CHAR(t.$col, 'DD/MM/YYYY') AS $col";
    } else {
        $select_parts[] = "t.$col";
    }
}

// Toujours sélectionner la PK (simple ou composite)
if (isset($config['pk'])) {
    array_unshift($select_parts, "t." . $config['pk']);
} elseif (isset($config['pk_multiple'])) {
    foreach (array_reverse($config['pk_multiple']) as $pkc) {
        if ($config['colonnes'][$pkc]['type'] !== 'date') { array_unshift($select_parts, "t.$pkc"); }
    }
}

$select = implode(', ', $select_parts);
$joins  = implode(' ', $join_parts);

// Clause WHERE pour la recherche
$where = '';
$search_conditions = [];
if ($search !== '' && !empty($config['searchable'])) {
    foreach ($config['searchable'] as $scol) {
        $search_conditions[] = "UPPER(t.$scol) LIKE UPPER('%' || :search || '%')";
    }
    $where = "WHERE (" . implode(" OR ", $search_conditions) . ")";
}

// Filtre optionnel par colonne
// Sécurité : on vérifie que filtre_col est bien une colonne connue dans la config
// pour éviter une injection SQL via l'URL
$filtre_col = $_GET['filtre_col'] ?? '';
$filtre_val = $_GET['filtre_val'] ?? '';

if ($filtre_col !== '' && !isset($config['colonnes'][$filtre_col])) {
    $filtre_col = '';
    $filtre_val = '';
}

if ($filtre_col !== '' && $filtre_val !== '') {
    $where = ($where === '') 
        ? "WHERE t.$filtre_col = :filtre_val" 
        : "$where AND t.$filtre_col = :filtre_val";
}

$sql  = "SELECT $select FROM {$config['table']} t $joins $where ORDER BY 1";
$stid = oci_parse($conn, $sql);

if ($search !== '' && !empty($config['searchable'])) {
    oci_bind_by_name($stid, ":search", $search);
}
if ($filtre_col !== '' && $filtre_val !== '') {
    oci_bind_by_name($stid, ":filtre_val", $filtre_val);
}

oci_execute($stid);

$rows = [];
while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
    $rows[] = $row;
}
oci_free_statement($stid);
oci_close($conn);

$page_title = $config['label'];
include(__DIR__ . "/includes/header.php");
?>

<div class="page-header">
    <div>
        <h1><?= htmlspecialchars($config['label']) ?></h1>
        <p><?= count($rows) ?> résultat(s)</p>
    </div>
    <?php if (hasRole($config['roles_edit'])): ?>
    <a href="ajouter.php?table=<?= urlencode($table) ?>" class="btn btn-primary">
        ➕ Ajouter
    </a>
    <?php endif; ?>
</div>

<!-- BARRE DE RECHERCHE -->
<?php if (!empty($config['searchable'])): ?>
<form method="GET" action="liste.php" class="search-bar">
    <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
    <input type="text" name="search" placeholder="Rechercher..."
           value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
    <a href="liste.php?table=<?= urlencode($table) ?>" class="btn btn-secondary">✖ Réinitialiser</a>
</form>
<?php endif; ?>

<!-- TABLEAU -->
<?php if (empty($rows)): ?>
    <div class="alert alert-info">Aucun résultat trouvé.</div>
<?php else: ?>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <?php foreach ($liste_cols as $col): ?>
                    <th><?= htmlspecialchars($config['colonnes'][$col]['label']) ?></th>
                <?php endforeach; ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <?php foreach ($liste_cols as $col): ?>
                <td>
                    <?php
                    $def = $config['colonnes'][$col];
                    // Valeur à afficher
                    if ($def['type'] === 'fk') {
                        $val = $row[strtoupper($col . '_label')] ?? '—';
                    } elseif ($def['type'] === 'bool') {
                        $val = ($row[strtoupper($col)] == 1) ? '<span class="badge badge-menace">⚠️ Oui</span>' : '<span class="badge badge-ok">Non</span>';
                        echo $val;
                        continue;
                    } else {
                        $val = $row[strtoupper($col)] ?? '—';
                    }

                    // Lien cliquable sur la première colonne si detail activé
			if ($col === $liste_cols[0] && $config['detail'] && (isset($config['pk']) || isset($config['pk_multiple']))) {

			    if (isset($config['pk'])) {
$pk_val = 'id=' . urlencode($row[strtoupper($config['pk'])]);
			    } else {
				$pk_parts = [];
				foreach ($config['pk_multiple'] as $pkc) {
				    $pk_parts[] = $pkc . '=' . urlencode($row[strtoupper($pkc)]);
				}
				$pk_val = implode('&', $pk_parts);
			    }
                        echo '<a href="detail.php?table=' . urlencode($table) . '&' . $pk_val . '" style="color:var(--vert-moyen);font-weight:500">' . htmlspecialchars($val) . '</a>';
                    } else {
                        echo htmlspecialchars($val ?? '—');
                    }
                    ?>
                </td>
                <?php endforeach; ?>
                <td class="td-actions">
	<?php
	// Récupérer la PK
	if (isset($config['pk'])) {
	    // PK simple → on préfixe avec "id="
	    $pk_val = 'id=' . urlencode($row[strtoupper($config['pk'])]);
	} else {
	    // Clé composite → col1=val1&col2=val2
	    $pk_parts = [];
	    foreach ($config['pk_multiple'] as $pkc) {
		$pk_parts[] = $pkc . '=' . urlencode($row[strtoupper($pkc)]);
	    }
	    $pk_val = implode('&', $pk_parts);
	}
	?>
		<?php if ($config['detail'] && (isset($config['pk']) || isset($config['pk_multiple']))): ?>
                    <a href="detail.php?table=<?= urlencode($table) ?>&<?= $pk_val ?>" class="btn btn-sm btn-secondary">👁️</a>
                    <?php endif; ?>
			<?php if (hasRole($config['roles_edit']) && count($config['colonnes']) > count(primaryColumns($config))): ?>
                 <a href="modifier.php?table=<?= urlencode($table) ?>&<?= $pk_val ?>" class="btn btn-sm btn-or">✏️</a>
                    <?php endif; ?>
                    <?php if (hasRole($config['roles_delete'])): ?>
                    <a href="supprimer.php?table=<?= urlencode($table) ?>&<?= $pk_val ?>" class="btn btn-sm btn-danger" data-confirm="Confirmer la suppression ?">🗑️</a>

                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include(__DIR__ . "/includes/footer.php"); ?>
