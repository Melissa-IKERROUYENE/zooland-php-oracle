<?php

include(__DIR__ . "/includes/session.php");
requireLogin();
include(__DIR__ . "/connex.inc.php");

$conn = connexion();
$user = currentUser();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 🔒 CHANGER MOT DE PASSE
    if ($action === 'change_password') {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $cnf = $_POST['confirm_password'] ?? '';

        $sql = "SELECT mot_de_passe FROM personnel WHERE id_personnel = :id";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ":id", $user['id']);
        oci_execute($stid);
        $row = oci_fetch_array($stid, OCI_ASSOC);
        oci_free_statement($stid);

        if (!$row || !password_verify($old, $row['MOT_DE_PASSE'])) {
            $error = "Ancien mot de passe incorrect.";
        } elseif (strlen($new) > 72 || str_contains($new, "\0")) {
            $error = 'Mot de passe invalide (72 octets maximum).';
        } elseif (strlen($new) < 3) {
            $error = "Mot de passe trop court (minimum 3 caractères).";
        } elseif ($new !== $cnf) {
            $error = "Les mots de passe ne correspondent pas.";
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $sql = "UPDATE personnel SET mot_de_passe = :hash WHERE id_personnel = :id";
            $stid = oci_parse($conn, $sql);
            oci_bind_by_name($stid, ":hash", $hash);
            oci_bind_by_name($stid, ":id", $user['id']);
            $res = oci_execute($stid, OCI_COMMIT_ON_SUCCESS);
            oci_free_statement($stid);
            if ($res) {
                unset($_SESSION['premier_login']);
                $success = "Mot de passe modifié avec succès !";
            } else {
                $error = "Erreur lors de la modification.";
            }
        }
    }

    // 👤 MODIFIER INFOS
    if ($action === 'modifier_infos') {
        $nom    = trim($_POST['nom_personnel'] ?? '');
        $prenom = trim($_POST['prenom_personnel'] ?? '');

        if ($nom == '' || $prenom == '') {
            $error = "Nom et prénom obligatoires.";
        } else {
            $sql = "UPDATE personnel SET nom_personnel = :nom, prenom_personnel = :prenom WHERE id_personnel = :id";
            $stid = oci_parse($conn, $sql);
            oci_bind_by_name($stid, ":nom", $nom);
            oci_bind_by_name($stid, ":prenom", $prenom);
            oci_bind_by_name($stid, ":id", $user['id']);
            $res = oci_execute($stid, OCI_COMMIT_ON_SUCCESS);
            oci_free_statement($stid);
            if ($res) {
                $_SESSION['nom_personnel']    = $nom;
                $_SESSION['prenom_personnel'] = $prenom;
                $success = "Informations mises à jour !";
            } else {
                $error = "Erreur lors de la mise à jour.";
            }
        }
    }
}

// ---- INFOS PERSONNEL ----
$sql = "SELECT p.id_personnel,
               p.nom_personnel,
               p.prenom_personnel,
               p.fonction,
               p.salaire,
               TO_CHAR(p.date_entree, 'DD/MM/YYYY') AS date_entree,
               p.id_zone,
               z.nom_zone,
               chef.nom_personnel    AS nom_chef,
               chef.prenom_personnel AS prenom_chef
        FROM personnel p
        LEFT JOIN zone z ON p.id_zone = z.id_zone
        LEFT JOIN personnel chef ON p.id_personnel_chef = chef.id_personnel
        WHERE p.id_personnel = :id";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":id", $user['id']);
oci_execute($stid);
$info = oci_fetch_array($stid, OCI_ASSOC);
oci_free_statement($stid);
oci_close($conn);

$page_title = "Mon profil";
include(__DIR__ . "/includes/header.php");
?>

<div class="page-header">
    <div><h1>⚙️ Mon profil</h1></div>
</div>

<?php if (!empty($_SESSION['premier_login'])): ?>
    <div class="alert alert-warning">
        ⚠️ <strong>Première connexion détectée.</strong> Veuillez changer votre mot de passe par défaut <strong>zoo26</strong> avant de continuer.
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(380px,1fr));gap:1.5rem">

    <!-- Informations personnelles -->
    <div class="card">
        <div class="card-title">👤 Mes informations</div>
        <form method="POST" action="profil.php" autocomplete="off">
        <?= csrfField() ?>
            <input type="hidden" name="action" value="modifier_infos">
            <div class="form-group">
                <label>Identifiant</label>
                <input type="text" value="<?= htmlspecialchars($info['ID_PERSONNEL']) ?>" disabled style="background:#f5f5f5">
            </div>
            <div class="form-group">
                <label for="nom_personnel">Nom</label>
                <input type="text" id="nom_personnel" name="nom_personnel" maxlength="50"
                    value="<?= htmlspecialchars($info['NOM_PERSONNEL']) ?>" required>
            </div>
            <div class="form-group">
                <label for="prenom_personnel">Prénom</label>
                <input type="text" id="prenom_personnel" name="prenom_personnel" maxlength="50"
                    value="<?= htmlspecialchars($info['PRENOM_PERSONNEL']) ?>" required>
            </div>
            <div class="form-group">
                <label>Fonction</label>
                <input type="text" value="<?= htmlspecialchars(ucfirst(str_replace('_',' ',$info['FONCTION']))) ?>" disabled style="background:#f5f5f5">
            </div>
            <div class="form-group">
                <label>Zone</label>
                <input type="text" value="<?= htmlspecialchars($info['NOM_ZONE'] ?? '—') ?>" disabled style="background:#f5f5f5">
            </div>
            <div class="form-group">
                <label>Date d'entrée</label>
                <input type="text" value="<?= htmlspecialchars($info['DATE_ENTREE']) ?>" disabled style="background:#f5f5f5">
            </div>
            <?php if (!empty($info['NOM_CHEF'])): ?>
            <div class="form-group">
                <label>Chef hiérarchique</label>
                <input type="text" value="<?= htmlspecialchars($info['PRENOM_CHEF'] . ' ' . $info['NOM_CHEF']) ?>" disabled style="background:#f5f5f5">
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </form>
    </div>

    <!-- Changement mot de passe -->
    <div class="card">
        <div class="card-title">🔒 Changer mon mot de passe</div>
        <?php if (!empty($_SESSION['premier_login'])): ?>
        <p style="font-size:.85rem;color:var(--erreur);margin-bottom:1rem">
            ⚠️ Changez le mot de passe par défaut <strong>zoo26</strong>.
        </p>
        <?php endif; ?>
        <form method="POST" action="profil.php" id="change-pwd-form" autocomplete="off">
        <?= csrfField() ?>
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
                <label for="old_password">Ancien mot de passe</label>
                <div style="position:relative">
                    <input type="password" id="old_password" name="old_password" required style="padding-right:2.5rem">
                    <button type="button" class="toggle-password" data-target="old_password"
                        style="position:absolute;right:.6rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer">👁️</button>
                </div>
            </div>
            <div class="form-group">
                <label for="new_password">Nouveau mot de passe</label>
                <div style="position:relative">
                    <input type="password" id="new_password" name="new_password" minlength="3" required style="padding-right:2.5rem">
                    <button type="button" class="toggle-password" data-target="new_password"
                        style="position:absolute;right:.6rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer">👁️</button>
                </div>
                <span class="hint">Minimum 3 caractères. Stocké en hash bcrypt.</span>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">🔒 Mettre à jour</button>
            <?php if (empty($_SESSION['premier_login'])): ?>
            <a href="dashboard/" class="btn btn-secondary" style="margin-left:.5rem">Annuler</a>
            <?php endif; ?>
        </form>
    </div>

</div>

<?php include(__DIR__ . "/includes/footer.php"); ?>
