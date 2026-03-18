<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Non autorizzato']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['id_ordine'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID ordine mancante']);
    exit;
}

$id_ordine = (int) $data['id_ordine'];
$id_utente = $_SESSION['user_id'];

require_once __DIR__ . '/../config.php';

$conn = get_mysqli();

// Check if order belongs to user and is in "In attesa" state
$stmt = $conn->prepare("SELECT stato FROM SB_ordine WHERE id_ordine = ? AND id_utente = ?");
$stmt->bind_param("ii", $id_ordine, $id_utente);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Ordine non trovato o non appartiene a te']);
    $stmt->close();
    $conn->close();
    exit;
}

$row = $res->fetch_assoc();
$stmt->close();

if (strtolower($row['stato']) !== 'in attesa') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Puoi cancellare solo ordini "In attesa"']);
    $conn->close();
    exit;
}

// Non eliminiamo la riga dal DB (per lo storico), ma la passiamo a stato "Cancellato"
$stmt_update = $conn->prepare("UPDATE SB_ordine SET stato = 'Cancellato' WHERE id_ordine = ? AND id_utente = ?");
$stmt_update->bind_param("ii", $id_ordine, $id_utente);

if ($stmt_update->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Impossibile aggiornare lo stato']);
}

$stmt_update->close();
$conn->close();
?>