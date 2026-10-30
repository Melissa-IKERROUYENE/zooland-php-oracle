<?php
require_once __DIR__ . '/includes/session.php';

if (!empty($_SESSION['id_personnel'])) {
    header('Location: dashboard/');
    exit;
}

include(__DIR__ . "/connex.inc.php");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id  = strtoupper(trim($_POST['id_personnel'] ?? ''));
    $pwd = $_POST['mot_de_passe'] ?? '';

    if ($id == '' || $pwd == '') {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $conn = connexion();

        $sql = "SELECT id_personnel, nom_personnel, prenom_personnel,
                       mot_de_passe, fonction, id_zone
                FROM personnel
                WHERE id_personnel = :id";

        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ":id", $id);
        oci_execute($stid);

        $personnel = oci_fetch_array($stid, OCI_ASSOC);

        oci_free_statement($stid);

        if ($personnel && password_verify($pwd, $personnel['MOT_DE_PASSE'])) {
            session_regenerate_id(true);
            $_SESSION['id_personnel']     = $personnel['ID_PERSONNEL'];
            $_SESSION['nom_personnel']    = $personnel['NOM_PERSONNEL'];
            $_SESSION['prenom_personnel'] = $personnel['PRENOM_PERSONNEL'];
            $_SESSION['fonction']         = $personnel['FONCTION'];
            $_SESSION['id_zone']          = $personnel['ID_ZONE'];
                        // Vérifier si ce personnel est chef d'une zone
	$sql2 = "SELECT id_zone FROM est_chef WHERE id_personnel = :id";
	$stid2 = oci_parse($conn, $sql2);
	oci_bind_by_name($stid2, ":id", $personnel['ID_PERSONNEL']);
	oci_execute($stid2);
	$chef = oci_fetch_array($stid2, OCI_ASSOC);
	oci_free_statement($stid2);
	$_SESSION['is_chef'] = $chef ? true : false;
            oci_close($conn);

            if ($pwd === 'zoo26') {
                $_SESSION['premier_login'] = true;
                header('Location: profil.php');
            } else {
                header('Location: dashboard/');
            }
            exit;
        } else {
            oci_close($conn);
            $error = "Identifiant ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Zoo'land 2026</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <span class="logo-emoji">🦁</span>
            <h1>Zoo'land</h1>
            <p>Gestion du zoo — 2026</p>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
        <?= csrfField() ?>
            <div class="form-group">
                <label for="id_personnel">Identifiant personnel</label>
                <input type="text" id="id_personnel" name="id_personnel"
                    placeholder="Ex : P001" maxlength="4"
                    value="<?= htmlspecialchars($_POST['id_personnel'] ?? '') ?>"
                    required autofocus>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <div style="position:relative">
                    <input type="password" id="mot_de_passe" name="mot_de_passe"
                        placeholder="Votre mot de passe" required style="padding-right:2.5rem">
                    <button type="button" class="toggle-password" data-target="mot_de_passe"
                        style="position:absolute;right:.6rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:1rem">
                        👁️
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"
                style="width:100%;justify-content:center;padding:.75rem;font-size:1rem;margin-top:.5rem">
                Se connecter
            </button>
        </form>
    </div>

<script src="assets/js/main.js"></script>
</body>
</html>
