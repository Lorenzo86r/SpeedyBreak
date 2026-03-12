
<?php
    session_start();

    // controllo se l'utente ha una sessione attiva
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403); // Accesso negato
        echo "Errore: Devi essere loggato per ordinare.";
        exit;
    }

    $host="localhost";
    $user="root";
    $pass="";
    $db="my_saqlain";

    $conn = new mysqli($host,$user,$pass,$db);

    if($conn->connect_error){
        die("Errore connessione");
    }

    $data = json_decode(file_get_contents("php://input"),true);

    $id_utente = $_SESSION['user_id'];
    $metodo = isset($data['metodo']) ? $data['metodo'] : "Contanti";
    $nota = isset($data['nota']) ? $data['nota'] : "";
    $data_ritiro = date("Y-m-d H:i:s", strtotime("+20 minutes"));

    // In the old version, $data was the array of items. 
    // In the new version, $data contains 'cart', 'metodo', 'nota'.
    $items = isset($data['cart']) ? $data['cart'] : (isset($data['items']) ? $data['items'] : (is_array($data) && !isset($data['cart']) && !isset($data['metodo']) ? $data : []));
    
    if (empty($items)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Carrello vuoto"]);
        exit;
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO SB_ordine (stato, metodo, id_utente, nota, data_ritiro) VALUES ('In attesa', ?, ?, ?, ?)");
        $stmt->bind_param("siss", $metodo, $id_utente, $nota, $data_ritiro);
        if (!$stmt->execute()) {
            throw new Exception("Errore inserimento ordine");
        }
        $id_ordine = $conn->insert_id;
        $stmt->close();

        $stmt_prod = $conn->prepare("SELECT id_prodotto FROM SB_prodotto WHERE nome = ?");
        $stmt_dettaglio = $conn->prepare("INSERT INTO SB_dettaglio_ordine (id_ordine, id_prodotto, quantita) VALUES (?, ?, ?)");

        foreach ($items as $item) {
            $nome = $item['name'];
            $quantita = (int)$item['quantity'];

            $stmt_prod->bind_param("s", $nome);
            $stmt_prod->execute();
            $res = $stmt_prod->get_result();
            if ($res->num_rows === 0) {
                throw new Exception("Prodotto non trovato: $nome");
            }
            $row = $res->fetch_assoc();
            $id_prodotto = $row['id_prodotto'];

            $stmt_dettaglio->bind_param("iii", $id_ordine, $id_prodotto, $quantita);
            if (!$stmt_dettaglio->execute()) {
                throw new Exception("Errore inserimento dettaglio");
            }
        }

        $conn->commit();
        echo json_encode(["status" => "success", "id_ordine" => $id_ordine]);

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
?>