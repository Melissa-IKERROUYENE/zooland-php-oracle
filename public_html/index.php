<?php
require_once __DIR__ . '/includes/session.php';
header('Location: ' . (!empty($_SESSION['id_personnel']) ? 'dashboard/' : 'login.php'));
exit;
