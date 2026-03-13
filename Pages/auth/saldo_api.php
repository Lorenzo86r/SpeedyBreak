<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Non autenticato']);
    exit;
}

$host = "localhost";
$user = "root";
$pass = "";
$db = "my_saqlain";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Errore connessione DB']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? '';

switch ($action) {

    case 'get_balance':
        $stmt = $conn->prepare("SELECT saldo FROM SB_utente WHERE id_utente = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        echo json_encode(['status' => 'success', 'saldo' => (float)$res['saldo']]);
        break;

    case 'recharge':
        $amount = isset($data['amount']) ? floatval($data['amount']) : 0;
        if ($amount <= 0 || $amount > 500) {
            echo json_encode(['status' => 'error', 'message' => 'Importo non valido (min €0.01, max €500)']);
            break;
        }

        $stmt = $conn->prepare("UPDATE SB_utente SET saldo = saldo + ? WHERE id_utente = ?");
        $stmt->bind_param("di", $amount, $user_id);
        if ($stmt->execute()) {
            // Get new balance
            $stmt2 = $conn->prepare("SELECT saldo FROM SB_utente WHERE id_utente = ?");
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();
            $new = $stmt2->get_result()->fetch_assoc();
            echo json_encode(['status' => 'success', 'saldo' => (float)$new['saldo'], 'message' => 'Ricarica effettuata con successo!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Errore durante la ricarica']);
        }
        break;

    case 'transfer':
        $recipient = trim($data['recipient'] ?? '');
        $amount = isset($data['amount']) ? floatval($data['amount']) : 0;

        if (empty($recipient)) {
            echo json_encode(['status' => 'error', 'message' => 'Inserisci un destinatario (username o email)']);
            break;
        }
        if ($amount <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Importo non valido']);
            break;
        }

        // Find recipient
        $stmt = $conn->prepare("SELECT id_utente, username, email FROM SB_utente WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $recipient, $recipient);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Utente destinatario non trovato']);
            break;
        }

        $dest = $res->fetch_assoc();
        if ($dest['id_utente'] == $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Non puoi trasferire a te stesso']);
            break;
        }

        // Check sender balance
        $stmt_bal = $conn->prepare("SELECT saldo FROM SB_utente WHERE id_utente = ?");
        $stmt_bal->bind_param("i", $user_id);
        $stmt_bal->execute();
        $sender = $stmt_bal->get_result()->fetch_assoc();

        if ((float)$sender['saldo'] < $amount) {
            echo json_encode(['status' => 'error', 'message' => 'Saldo insufficiente']);
            break;
        }

        // Transaction
        $conn->begin_transaction();
        try {
            $stmt_sub = $conn->prepare("UPDATE SB_utente SET saldo = saldo - ? WHERE id_utente = ? AND saldo >= ?");
            $stmt_sub->bind_param("did", $amount, $user_id, $amount);
            $stmt_sub->execute();

            if ($stmt_sub->affected_rows === 0) {
                throw new Exception("Saldo insufficiente");
            }

            $stmt_add = $conn->prepare("UPDATE SB_utente SET saldo = saldo + ? WHERE id_utente = ?");
            $stmt_add->bind_param("di", $amount, $dest['id_utente']);
            $stmt_add->execute();

            $conn->commit();

            // Get new balance
            $stmt_new = $conn->prepare("SELECT saldo FROM SB_utente WHERE id_utente = ?");
            $stmt_new->bind_param("i", $user_id);
            $stmt_new->execute();
            $new_bal = $stmt_new->get_result()->fetch_assoc();

            $dest_name = $dest['username'] ?: $dest['email'];
            echo json_encode([
                'status' => 'success',
                'saldo' => (float)$new_bal['saldo'],
                'message' => "€" . number_format($amount, 2, ',', '.') . " trasferiti a " . $dest_name
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Azione non valida']);
        break;
}

$conn->close();
?>
