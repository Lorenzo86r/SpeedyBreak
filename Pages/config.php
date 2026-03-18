<?php
// === CONFIGURAZIONE CENTRALIZZATA DATABASE ===
// Tutti i file del progetto devono includere questo file
// invece di hardcodare le credenziali.

define('DB_HOST', 'localhost');
define('DB_NAME', 'my_saqlain');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Restituisce una connessione mysqli al database.
 */
function get_mysqli(): mysqli
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }
    return $conn;
}

/**
 * Genera e memorizza un CSRF token in sessione se non esiste già.
 * Restituisce il token corrente.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica che il CSRF token inviato via POST sia valido.
 * Se non valido, interrompe con errore 403.
 */
function csrf_verify(): void
{
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        die("CSRF token non valido");
    }
}

/**
 * Genera l'input hidden HTML per il CSRF token.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}
?>