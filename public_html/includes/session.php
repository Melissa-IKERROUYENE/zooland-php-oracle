<?php
// ================================================
// includes/session.php — Gestion sessions & droits
// ================================================
if (session_status() == PHP_SESSION_NONE) {
    session_start(['cookie_httponly'=>true, 'cookie_samesite'=>'Lax', 'cookie_secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
}

function requireLogin(): void {
    if (empty($_SESSION['id_personnel'])) {
        header('Location: ' . appBasePath() . 'login.php');
        exit;
    }
}

function requireRole(array $roles): void {
    requireLogin();
    if (!isset($_SESSION['fonction']) || !in_array($_SESSION['fonction'], $roles)) {
        header('Location: ' . appBasePath() . 'acces_refuse.php');
        exit;
    }
}

function hasRole(array $roles): bool {
    return isset($_SESSION['fonction']) && in_array($_SESSION['fonction'], $roles);
}

function isChef(): bool {
    return !empty($_SESSION['is_chef']);
}

function currentUser(): array {
    return [
        'id'       => $_SESSION['id_personnel'] ?? null,
        'nom'      => $_SESSION['nom_personnel'] ?? '',
        'prenom'   => $_SESSION['prenom_personnel'] ?? '',
        'fonction' => $_SESSION['fonction'] ?? '',
        'id_zone'  => $_SESSION['id_zone'] ?? null,
        'is_chef'  => $_SESSION['is_chef'] ?? false,
    ];
}

// ---- Permissions par fonctionnalité ----
const ROLES_ALL = ['soigneur','veterinaire','personnel_technique','entretien','boutique','comptable','gestionnaire','dirigeant'];

const ROLES_ANIMAUX = ['soigneur','veterinaire','gestionnaire','dirigeant'];
const ROLES_SOINS = ['soigneur','veterinaire','gestionnaire'];
const ROLES_REPARATIONS = ['personnel_technique','gestionnaire','dirigeant'];
const ROLES_BOUTIQUES = ['boutique','gestionnaire','dirigeant'];
const ROLES_CA = ['boutique','comptable','gestionnaire','dirigeant'];
const ROLES_PERSONNEL = ['gestionnaire','dirigeant'];
const ROLES_ENCLOS = ['personnel_technique','gestionnaire','dirigeant','entretien'];

function appBasePath(): string {
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    if (basename($dir) === 'dashboard') { $dir = dirname($dir); }
    return rtrim($dir, '/') . '/';
}
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403); exit('Formulaire expiré. Rechargez la page puis réessayez.');
    }
    foreach ($_POST as $value) {
        if (!is_string($value)) { http_response_code(400); exit('Saisie invalide.'); }
    }
}
foreach ($_GET as $value) {
    if (!is_string($value)) { http_response_code(400); exit('Paramètre invalide.'); }
}
