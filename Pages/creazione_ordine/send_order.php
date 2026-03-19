<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_utente'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Non autenticato']);
    exit;
}

require_once __DIR__ . '/../config.php';

$id_utente = intval($_SESSION['id_utente']);
$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data) || empty($data)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Carrello vuoto o non valido']);
    exit;
}

$conn = get_mysqli();

$conn->begin_transaction();
try {
    $data_ritiro = date("Y-m-d H:i:s", strtotime("+20 minutes"));

    // Creazione ordine con prepared statement
    $stmt_ordine = $conn->prepare("INSERT INTO SB_ordine (stato, metodo, id_utente, nota, data_ritiro) VALUES ('In attesa', 'Contanti', ?, '', ?)");
    $stmt_ordine->bind_param("is", $id_utente, $data_ritiro);
    if (!$stmt_ordine->execute())
        throw new Exception("Errore creazione ordine");

    $id_ordine = $conn->insert_id;
    $stmt_ordine->close();

    // Loop sui prodotti
    $stmt_cerca_prod = $conn->prepare("SELECT id_prodotto, prezzo FROM SB_prodotto WHERE nome = ? LIMIT 1");
    $stmt_ins_det = $conn->prepare("INSERT INTO SB_dettaglio_ordine (id_ordine, id_prodotto, quantita) VALUES (?, ?, ?)");

    foreach ($data as $item) {
        if (!isset($item['name']) || !isset($item['quantity']))
            continue;

        $nome = trim($item['name']);
        $quantita = intval($item['quantity']);
        if ($quantita <= 0)
            continue;

        // Cerca id prodotto
        $stmt_cerca_prod->bind_param("s", $nome);
        $stmt_cerca_prod->execute();
        $res = $stmt_cerca_prod->get_result();
        $row = $res->fetch_assoc();

        if (!$row) {
            throw new Exception("Prodotto '$nome' non trovato nel listino.");
        }

        $id_prodotto = $row['id_prodotto'];

        // Inserisci dettaglio
        $stmt_ins_det->bind_param("iii", $id_ordine, $id_prodotto, $quantita);
        if (!$stmt_ins_det->execute())
            throw new Exception("Errore inserimento dettaglio");
    }

    $conn->commit();
    echo json_encode(['status' => 'ok', 'id_ordine' => $id_ordine]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
