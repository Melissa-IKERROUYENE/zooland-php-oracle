<?php
include(__DIR__ . "/../includes/session.php");
requireLogin();
if (!empty($_SESSION['premier_login'])) {
    header('Location: ../profil.php');
    exit;
}
include(__DIR__ . "/../connex.inc.php");
$user = currentUser();
$conn = connexion();

$sql = "SELECT COUNT(*) AS NB FROM animal";
$stid = oci_parse($conn, $sql);
oci_execute($stid);
$row = oci_fetch_array($stid, OCI_ASSOC);
$nb_animaux = $row['NB'];
oci_free_statement($stid);

$sql = "SELECT COUNT(*) AS NB FROM espece";
$stid = oci_parse($conn, $sql);
oci_execute($stid);
$row = oci_fetch_array($stid, OCI_ASSOC);
$nb_especes = $row['NB'];
oci_free_statement($stid);

$sql = "SELECT COUNT(*) AS NB FROM enclos";
$stid = oci_parse($conn, $sql);
oci_execute($stid);
$row = oci_fetch_array($stid, OCI_ASSOC);
$nb_enclos = $row['NB'];
oci_free_statement($stid);

$sql = "SELECT COUNT(*) AS NB FROM animal a, espece e
        WHERE a.id_espece = e.id_espece AND e.est_menace = 1";
$stid = oci_parse($conn, $sql);
oci_execute($stid);
$row = oci_fetch_array($stid, OCI_ASSOC);
$nb_menaces = $row['NB'];
oci_free_statement($stid);

oci_close($conn);
$page_title = "Tableau de bord";
include(__DIR__ . "/../includes/header.php");
?>

<div class="page-header">
    <div>
        <h1>Bonjour, <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?> 👋</h1>
        <p>Bienvenue sur Zoo'land — <?= date('d/m/Y') ?></p>
    </div>
</div>

<div class="stat-cards">
    <?php if (hasRole(ROLES_ANIMAUX)): ?>
    <a href="<?= $base ?>liste.php?table=animal" class="stat-card stat-card--link">
        <span class="stat-icon">🐾</span>
        <span class="stat-number"><?= $nb_animaux ?></span>
        <span class="stat-label">Animaux</span>
    </a>
    <a href="<?= $base ?>liste.php?table=espece" class="stat-card stat-card--link">
        <span class="stat-icon">🔬</span>
        <span class="stat-number"><?= $nb_especes ?></span>
        <span class="stat-label">Espèces</span>
    </a>
    <?php endif; ?>
    <?php if (hasRole(ROLES_ENCLOS)): ?>
    <a href="<?= $base ?>liste.php?table=enclos" class="stat-card stat-card--link">
        <span class="stat-icon">🏠</span>
        <span class="stat-number"><?= $nb_enclos ?></span>
        <span class="stat-label">Enclos</span>
    </a>
    <?php endif; ?>
    <?php if (hasRole(ROLES_ANIMAUX)): ?>
    <a href="<?= $base ?>liste.php?table=animal" class="stat-card stat-card--link rouge">
        <span class="stat-icon">⚠️</span>
        <span class="stat-number"><?= $nb_menaces ?></span>
        <span class="stat-label">Animaux menacés</span>
    </a>
    <?php endif; ?>
</div>

<?php include(__DIR__ . "/../includes/footer.php"); ?>
