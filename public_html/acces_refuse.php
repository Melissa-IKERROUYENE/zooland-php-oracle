<?php
$page_title = "Accès refusé";
include(__DIR__ . "/includes/header.php");
?>
<div class="page-header">
    <div>
        <h1>🚫 Accès refusé</h1>
        <p>Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>
    </div>
    <a href="<?= $base ?>dashboard/" class="btn btn-secondary">← Retour au tableau de bord</a>
</div>
<?php include(__DIR__ . "/includes/footer.php"); ?>
