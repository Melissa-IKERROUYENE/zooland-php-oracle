<?php

include(__DIR__ . "/includes/session.php");
requireLogin();
include(__DIR__ . "/connex.inc.php");

$conn = connexion();
$user = currentUser();

// ===== PARAMETRES DE RECHERCHE =====
$type   = $_GET['type']   ?? '';
$search = trim($_GET['search'] ?? '');
$filtre = trim($_GET['filtre'] ?? '');

$searchRoles = [
 'historique_emplois'=>ROLES_BOUTIQUES, 'soigneurs_par_animal'=>ROLES_SOINS,
 'ca_mensuel'=>ROLES_CA, 'ca_annuel'=>ROLES_CA, 'cohabitations'=>ROLES_ANIMAUX,
 'especes_incompatibles'=>ROLES_ANIMAUX, 'animaux_sans_soin'=>ROLES_SOINS,
 'parrainages'=>['gestionnaire','dirigeant'], 'poids_moyen_zone'=>ROLES_ANIMAUX,
 'enclos_sans_reparation'=>ROLES_REPARATIONS,
];
if ($type !== '') {
 if (!isset($searchRoles[$type])) { http_response_code(400); exit('Recherche inconnue.'); }
 requireRole($searchRoles[$type]);
}

$results = [];
$colonnes = [];
$titre_result = '';

// ===================================================
// 1. HISTORIQUE DES EMPLOIS
// ===================================================
if ($type === 'historique_emplois') {
    $titre_result = "Historique des emplois";
    $colonnes = ['Nom', 'Prénom', 'Boutique', 'Type boutique', 'Date embauche', 'Date fin'];

    $where = "WHERE 1=1";
    if ($search !== '') {
        $where .= " AND (UPPER(p.nom_personnel) LIKE UPPER('%'||:search||'%')
                     OR UPPER(p.prenom_personnel) LIKE UPPER('%'||:search||'%'))";
    }
    if ($filtre !== '') {
        $where .= " AND b.id_boutique = :filtre";
    }

    $sql = "SELECT p.nom_personnel, p.prenom_personnel,
                   b.nom_boutique, b.type_boutique,
                   TO_CHAR(t.date_embauche, 'DD/MM/YYYY') AS date_embauche,
                   TO_CHAR(t.date_fin_embauche, 'DD/MM/YYYY') AS date_fin
            FROM travailler t
            JOIN personnel p ON t.id_personnel = p.id_personnel
            JOIN boutique b  ON t.id_boutique  = b.id_boutique
            $where
            ORDER BY p.nom_personnel, t.date_embauche";

    $stid = oci_parse($conn, $sql);
    if ($search !== '') oci_bind_by_name($stid, ":search", $search);
    if ($filtre !== '') oci_bind_by_name($stid, ":filtre", $filtre);
    oci_execute($stid);
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $results[] = array_values($row);
    }
    oci_free_statement($stid);
}

// ===================================================
// 2. SOIGNEURS AYANT SOIGNE UN ANIMAL
// ===================================================
if ($type === 'soigneurs_par_animal') {
    $titre_result = "Soigneurs ayant soigné un animal";
    $colonnes = ['Animal', 'Espèce', 'Soigneur', 'Prénom', 'Fonction', 'Date soin', 'Dose'];

    $where = "WHERE 1=1";
    if ($search !== '') {
        $where .= " AND (UPPER(a.nom_animal) LIKE UPPER('%'||:search||'%')
                     OR UPPER(p.nom_personnel) LIKE UPPER('%'||:search||'%'))";
    }

    $sql = "SELECT a.nom_animal, e.nom_usuel AS espece,
                   p.nom_personnel, p.prenom_personnel, p.fonction,
                   TO_CHAR(s.date_soin, 'DD/MM/YYYY') AS date_soin,
                   s.dose_soin
            FROM soin s
            JOIN personnel p ON s.id_personnel = p.id_personnel
            JOIN animal a    ON s.id_rfid       = a.id_rfid
            JOIN espece e    ON a.id_espece      = e.id_espece
            $where
            ORDER BY a.nom_animal, s.date_soin DESC";

    $stid = oci_parse($conn, $sql);
    if ($search !== '') oci_bind_by_name($stid, ":search", $search);
    oci_execute($stid);
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $results[] = array_values($row);
    }
    oci_free_statement($stid);
}

// ===================================================
// 3. CA MENSUEL PAR BOUTIQUE
// ===================================================
if ($type === 'ca_mensuel') {
    $titre_result = "Chiffre d'affaires mensuel par boutique";
    $colonnes = ['Boutique', 'Type', 'Mois', 'CA mensuel (€)'];

    $where = "WHERE 1=1";
    if ($search !== '') {
        $where .= " AND UPPER(b.nom_boutique) LIKE UPPER('%'||:search||'%')";
    }

    $sql = "SELECT b.nom_boutique, b.type_boutique,
                   TO_CHAR(c.date_ca, 'MM/YYYY') AS mois,
                   SUM(c.chiffre_d_affaire) AS ca_mensuel
            FROM ca c
            JOIN boutique b ON c.id_boutique = b.id_boutique
            $where
            GROUP BY b.nom_boutique, b.type_boutique, TO_CHAR(c.date_ca, 'MM/YYYY')
            ORDER BY b.nom_boutique, mois";

    $stid = oci_parse($conn, $sql);
    if ($search !== '') oci_bind_by_name($stid, ":search", $search);
    oci_execute($stid);
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $results[] = array_values($row);
    }
    oci_free_statement($stid);
}

// ===================================================
// 4. CA ANNUEL PAR BOUTIQUE
// ===================================================
if ($type === 'ca_annuel') {
    $titre_result = "Chiffre d'affaires annuel par boutique";
    $colonnes = ['Boutique', 'Type', 'Année', 'CA annuel (€)'];

    $where = "WHERE 1=1";
    if ($search !== '') {
        $where .= " AND UPPER(b.nom_boutique) LIKE UPPER('%'||:search||'%')";
    }

    $sql = "SELECT b.nom_boutique, b.type_boutique,
                   TO_CHAR(c.date_ca, 'YYYY') AS annee,
                   SUM(c.chiffre_d_affaire) AS ca_annuel
            FROM ca c
            JOIN boutique b ON c.id_boutique = b.id_boutique
            $where
            GROUP BY b.nom_boutique, b.type_boutique, TO_CHAR(c.date_ca, 'YYYY')
            ORDER BY b.nom_boutique, annee";

    $stid = oci_parse($conn, $sql);
    if ($search !== '') oci_bind_by_name($stid, ":search", $search);
    oci_execute($stid);
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $results[] = array_values($row);
    }
    oci_free_statement($stid);
}

// ===================================================
// 5. ESPECES COMPATIBLES (COHABITATIONS)
// ===================================================
if ($type === 'cohabitations') {
    $titre_result = "Espèces pouvant cohabiter";
    $colonnes = ['Espèce 1', 'Espèce 2'];

    $where = "WHERE 1=1";
    if ($search !== '') {
        $where .= " AND (UPPER(e1.nom_usuel) LIKE UPPER('%'||:search||'%')
                     OR UPPER(e2.nom_usuel) LIKE UPPER('%'||:search||'%'))";
    }

    $sql = "SELECT e1.nom_usuel AS espece_1, e2.nom_usuel AS espece_2
            FROM cohabiter c
            JOIN espece e1 ON c.id_espece_1 = e1.id_espece
            JOIN espece e2 ON c.id_espece_2 = e2.id_espece
            $where
            ORDER BY e1.nom_usuel";

    $stid = oci_parse($conn, $sql);
    if ($search !== '') oci_bind_by_name($stid, ":search", $search);
    oci_execute($stid);
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $results[] = array_values($row);
    }
    oci_free_statement($stid);
}

// ===================================================
// 6. ESPECES SANS COHABITATION
// ===================================================
if ($type === 'especes_incompatibles') {
    $titre_result = "Espèces ne cohabitant avec aucune autre";
    $colonnes = ['ID', 'Nom latin', 'Nom usuel', 'Menacée'];

    $sql = "SELECT id_espece, nom_latin, nom_usuel,
                   CASE WHEN est_menace = 1 THEN 'Oui' ELSE 'Non' END AS menace
            FROM espece
            WHERE id_espece NOT IN (
                SELECT id_espece_1 FROM cohabiter
                UNION
                SELECT id_espece_2 FROM cohabiter
            )
            ORDER BY nom_usuel";

    $stid = oci_parse($conn, $sql);
    oci_execute($stid);
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $results[] = array_values($row);
    }
    oci_free_statement($stid);
}

// ===================================================
// 7. ANIMAUX SANS SOIN
// ===================================================
if ($type === 'animaux_sans_soin') {
    $titre_result = "Animaux n'ayant reçu aucun soin";
    $colonnes = ['Nom', 'Espèce', 'Poids (kg)', 'Régime', 'Enclos'];

    $where = "WHERE 1=1";
    if ($search !== '') {
        $where .= " AND UPPER(a.nom_animal) LIKE UPPER('%'||:search||'%')";
    }

    $sql = "SELECT a.nom_animal, e.nom_usuel, a.poids,
                   a.regime_alimentaire, a.id_enclos
            FROM animal a
            JOIN espece e ON a.id_espece = e.id_espece
            $where
            AND a.id_rfid NOT IN (SELECT id_rfid FROM soin)
            ORDER BY a.nom_animal";

    $stid = oci_parse($conn, $sql);
    if ($search !== '') oci_bind_by_name($stid, ":search", $search);
    oci_execute($stid);
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $results[] = array_values($row);
    }
    oci_free_statement($stid);
}

// ===================================================
// 8. PARRAINAGES PAR VISITEUR
// ===================================================
if ($type === 'parrainages') {
    $titre_result = "Parrainages par visiteur";
    $colonnes = ['Visiteur', 'Prénom', 'Animal', 'Espèce', 'Niveau'];

    $where = "WHERE 1=1";
    if ($search !== '') {
        $where .= " AND (UPPER(v.nom_visiteur) LIKE UPPER('%'||:search||'%')
                     OR UPPER(a.nom_animal) LIKE UPPER('%'||:search||'%'))";
    }

    $sql = "SELECT v.nom_visiteur, v.prenom_visiteur,
                   a.nom_animal, e.nom_usuel, n.nom_niveau
            FROM parrainer p
            JOIN visiteur v            ON p.id_visiteur = v.id_visiteur
            JOIN animal a              ON p.id_rfid      = a.id_rfid
            JOIN espece e              ON a.id_espece    = e.id_espece
            JOIN niveau_contribution n ON p.id_niveau    = n.id_niveau
            $where
            ORDER BY v.nom_visiteur, a.nom_animal";

    $stid = oci_parse($conn, $sql);
    if ($search !== '') oci_bind_by_name($stid, ":search", $search);
    oci_execute($stid);
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $results[] = array_values($row);
    }
    oci_free_statement($stid);
}

// ===================================================
// 9. POIDS MOYEN PAR ZONE
// ===================================================
if ($type === 'poids_moyen_zone') {
    $titre_result = "Poids moyen des animaux par zone";
    $colonnes = ['Zone', 'Nb animaux', 'Poids moyen (kg)', 'Poids min (kg)', 'Poids max (kg)'];

    $sql = "SELECT z.nom_zone,
                   COUNT(a.id_rfid) AS nb_animaux,
                   ROUND(AVG(a.poids), 2) AS poids_moyen,
                   MIN(a.poids) AS poids_min,
                   MAX(a.poids) AS poids_max
            FROM animal a
            JOIN enclos en ON a.id_enclos = en.id_enclos
            JOIN zone z    ON en.id_zone  = z.id_zone
            GROUP BY z.nom_zone
            ORDER BY poids_moyen DESC";

    $stid = oci_parse($conn, $sql);
    oci_execute($stid);
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $results[] = array_values($row);
    }
    oci_free_statement($stid);
}

// ===================================================
// 10. ENCLOS SANS REPARATION RECENTE
// ===================================================
if ($type === 'enclos_sans_reparation') {
    $titre_result = "Enclos sans réparation après le 05/03/2026";
    $colonnes = ['Enclos', 'Surface (m²)', 'Particularité', 'Zone'];

    $sql = "SELECT e.id_enclos, e.surface, e.particularite, z.nom_zone
            FROM enclos e
            JOIN zone z ON e.id_zone = z.id_zone
            WHERE e.id_enclos NOT IN (
                SELECT id_enclos FROM reparation
                WHERE date_reparation > TO_DATE('05/03/2026','DD/MM/YYYY')
            )
            ORDER BY e.id_enclos";

    $stid = oci_parse($conn, $sql);
    oci_execute($stid);
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $results[] = array_values($row);
    }
    oci_free_statement($stid);
}

oci_close($conn);

$page_title = "Recherche avancée";
include(__DIR__ . "/includes/header.php");
?>

<div class="page-header">
    <div>
        <h1>🔍 Recherche avancée</h1>
        <p>Recherches complexes sur la base de données</p>
    </div>
</div>

<!-- CHOIX DU TYPE DE RECHERCHE -->
<div class="card" style="margin-bottom:2rem">
    <div class="card-title">Type de recherche</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.75rem;margin-top:.5rem">

        <?php if (hasRole(ROLES_BOUTIQUES)): ?>
        <a href="recherche.php?type=historique_emplois" class="btn <?= $type==='historique_emplois' ? 'btn-primary' : 'btn-secondary' ?>">
            📋 Historique emplois
        </a>
        <?php endif; ?>
        <?php if (hasRole(ROLES_CA)): ?>
        <a href="recherche.php?type=ca_mensuel" class="btn <?= $type==='ca_mensuel' ? 'btn-primary' : 'btn-secondary' ?>">
            📊 CA mensuel
        </a>
        <a href="recherche.php?type=ca_annuel" class="btn <?= $type==='ca_annuel' ? 'btn-primary' : 'btn-secondary' ?>">
            📊 CA annuel
        </a>
        <?php endif; ?>

        <?php if (hasRole(ROLES_SOINS)): ?>
        <a href="recherche.php?type=soigneurs_par_animal" class="btn <?= $type==='soigneurs_par_animal' ? 'btn-primary' : 'btn-secondary' ?>">
            💊 Soigneurs par animal
        </a>
        <a href="recherche.php?type=animaux_sans_soin" class="btn <?= $type==='animaux_sans_soin' ? 'btn-primary' : 'btn-secondary' ?>">
            🐾 Animaux sans soin
        </a>
        <?php endif; ?>

        <?php if (hasRole(ROLES_ANIMAUX)): ?>
        <a href="recherche.php?type=cohabitations" class="btn <?= $type==='cohabitations' ? 'btn-primary' : 'btn-secondary' ?>">
            🤝 Cohabitations
        </a>
        <a href="recherche.php?type=especes_incompatibles" class="btn <?= $type==='especes_incompatibles' ? 'btn-primary' : 'btn-secondary' ?>">
            ❌ Espèces incompatibles
        </a>
        <a href="recherche.php?type=poids_moyen_zone" class="btn <?= $type==='poids_moyen_zone' ? 'btn-primary' : 'btn-secondary' ?>">
            ⚖️ Poids moyen par zone
        </a>
        <?php endif; ?>

        <?php if (hasRole(ROLES_ENCLOS)): ?>
        <a href="recherche.php?type=enclos_sans_reparation" class="btn <?= $type==='enclos_sans_reparation' ? 'btn-primary' : 'btn-secondary' ?>">
            🔧 Enclos sans réparation
        </a>
        <?php endif; ?>

        <?php if (hasRole(['gestionnaire','dirigeant'])): ?>
        <a href="recherche.php?type=parrainages" class="btn <?= $type==='parrainages' ? 'btn-primary' : 'btn-secondary' ?>">
            🤝 Parrainages
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- BARRE DE RECHERCHE (si applicable) -->
<?php if ($type !== '' && $type !== 'especes_incompatibles' && $type !== 'poids_moyen_zone' && $type !== 'enclos_sans_reparation'): ?>
<form method="GET" action="recherche.php" class="search-bar">
    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
    <input type="text" name="search" placeholder="Rechercher par mots-clés..."
           value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
    <a href="recherche.php?type=<?= urlencode($type) ?>" class="btn btn-secondary">✖ Réinitialiser</a>
</form>
<?php endif; ?>

<!-- RESULTATS -->
<?php if ($type !== '' && !empty($titre_result)): ?>
<div class="card">
    <div class="card-title"><?= htmlspecialchars($titre_result) ?> — <?= count($results) ?> résultat(s)</div>
    <?php if (empty($results)): ?>
        <div class="alert alert-info">Aucun résultat trouvé.</div>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <?php foreach ($colonnes as $col): ?>
                    <th><?= htmlspecialchars($col) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                <tr>
                    <?php foreach ($row as $val): ?>
                    <td><?= htmlspecialchars($val ?? '—') ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include(__DIR__ . "/includes/footer.php"); ?>
