<?php
// ================================================
// includes/header.php — En-tête commun
// ================================================
require_once(__DIR__ . "/session.php");
$user = currentUser();
$page_title = $page_title ?? "Zoo'land 2026";
$base = appBasePath();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — Zoo'land</title>
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>

<?php if (!empty($_SESSION['id_personnel'])): ?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="brand-icon">🦁</span>
        <div class="brand-name">
            Zoo'land
            <em>2026</em>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li><a href="<?= $base ?>dashboard/"><span class="nav-icon">🏠</span> Accueil</a></li>
        <li><a href="<?= $base ?>recherche.php"><span class="nav-icon">🔍</span> Recherche</a></li>

        <li><div class="nav-sep"></div></li>

        <?php if (hasRole(ROLES_ANIMAUX)): ?>
        <li><a href="<?= $base ?>liste.php?table=animal"><span class="nav-icon">🐾</span> Animaux</a></li>
        <li><a href="<?= $base ?>liste.php?table=espece"><span class="nav-icon">🔬</span> Espèces</a></li>
        <li><a href="<?= $base ?>liste.php?table=soin"><span class="nav-icon">💊</span> Soins</a></li>
        <li><a href="<?= $base ?>liste.php?table=nourriture"><span class="nav-icon">🥩</span> Nourriture</a></li>
        <?php endif; ?>

        <?php if (hasRole(ROLES_ENCLOS)): ?>
        <li><a href="<?= $base ?>liste.php?table=enclos"><span class="nav-icon">🏠</span> Enclos</a></li>
        <li><a href="<?= $base ?>liste.php?table=zone"><span class="nav-icon">🗺️</span> Zones</a></li>
        <?php endif; ?>

        <?php if (hasRole(ROLES_REPARATIONS)): ?>
        <li><a href="<?= $base ?>liste.php?table=reparation"><span class="nav-icon">🔧</span> Réparations</a></li>
        <?php endif; ?>

        <?php if (hasRole(ROLES_BOUTIQUES)): ?>
        <li><div class="nav-sep"></div></li>
        <li><a href="<?= $base ?>liste.php?table=boutique"><span class="nav-icon">🛍️</span> Boutiques</a></li>
        <?php endif; ?>
        <?php if (hasRole(ROLES_CA)): ?>
        <li><a href="<?= $base ?>liste.php?table=ca"><span class="nav-icon">📊</span> Chiffre d'affaires</a></li>
        <?php endif; ?>
        <?php if (hasRole(ROLES_BOUTIQUES)): ?>
        <li><a href="<?= $base ?>liste.php?table=travailler"><span class="nav-icon">📋</span> Emplois</a></li>
        <?php endif; ?>

        <?php if (hasRole(ROLES_PERSONNEL)): ?>
        <li><div class="nav-sep"></div></li>
        <li><a href="<?= $base ?>liste.php?table=personnel"><span class="nav-icon">👥</span> Personnel</a></li>
        <li><a href="<?= $base ?>liste.php?table=est_chef"><span class="nav-icon">⭐</span> Chefs de zone</a></li>
        <?php endif; ?>

        <?php if (hasRole(['gestionnaire','dirigeant'])): ?>
        <li><div class="nav-sep"></div></li>
        <li><a href="<?= $base ?>liste.php?table=visiteur"><span class="nav-icon">👤</span> Visiteurs</a></li>
        <li><a href="<?= $base ?>liste.php?table=parrainer"><span class="nav-icon">🤝</span> Parrainages</a></li>
        <li><a href="<?= $base ?>liste.php?table=prestataire"><span class="nav-icon">🏢</span> Prestataires</a></li>
        <li><a href="<?= $base ?>liste.php?table=zoo"><span class="nav-icon">🌍</span> Zoos</a></li>
        <?php endif; ?>
    </ul>

    <div class="sidebar-user">
        <div class="user-info">
            <div>
                <span class="user-badge user-badge--<?= htmlspecialchars($user['fonction']) ?>">
                    <?= htmlspecialchars(ucfirst(str_replace('_',' ',$user['fonction']))) ?>
                </span>
                <div class="user-name"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></div>
            </div>
        </div>
        <div class="user-actions">
            <a href="<?= $base ?>profil.php" class="btn-profil">⚙️ Profil</a>
            <a href="<?= $base ?>logout.php" class="btn-logout">Déconnexion</a>
        </div>
    </div>
</aside>

<div class="main-wrapper">
<?php endif; ?>

<main class="main-content">
