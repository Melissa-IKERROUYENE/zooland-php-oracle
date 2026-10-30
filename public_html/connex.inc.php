<?php
function zooConfig(): array {
    $file = dirname(__DIR__) . '/config.local.php';
    $config = is_file($file) ? require $file : [];
    if (!is_array($config)) { $config = []; }
    if (!is_file($file) && is_file(__DIR__ . '/myparam.inc.php')) {
        require_once __DIR__ . '/myparam.inc.php';
        foreach (['host'=>'MYHOST','user'=>'MYUSER','password'=>'MYPASS'] as $key=>$constant) {
            if (defined($constant)) { $config[$key] = constant($constant); }
        }
    }
    foreach (['host'=>'ORACLE_HOST','user'=>'ORACLE_USER','password'=>'ORACLE_PASSWORD'] as $key=>$env) {
        $value = getenv($env);
        if ($value !== false) { $config[$key] = $value; }
    }
    return $config;
}
function connexion() {
    if (!extension_loaded('oci8')) {
        http_response_code(503);
        exit('Extension PHP OCI8 manquante. Consultez le README.');
    }
    $c = zooConfig();
    foreach (['host','user','password'] as $key) {
        if (empty($c[$key])) { http_response_code(503); exit('Renseignez la configuration Oracle privée décrite dans le README.'); }
    }
    $conn = @oci_connect($c['user'], $c['password'], $c['host']);
    if (!$conn) {
        error_log('Zooland Oracle: ' . (oci_error()['message'] ?? 'connexion impossible'));
        http_response_code(503); exit('Connexion Oracle indisponible. Vérifiez la configuration et le service.');
    }
    // Formats explicites, identiques dans les formulaires et les clés composées.
    $stmt = oci_parse($conn, "ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY'");
    if (!oci_execute($stmt)) { http_response_code(503); exit('Impossible de configurer la session Oracle.'); }
    oci_free_statement($stmt);
    return $conn;
}
