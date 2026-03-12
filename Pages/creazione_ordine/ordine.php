
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
$metodo = isset($data['metodo']) ? $conn->real_escape_string($data['metodo']) : "Contanti";
$nota = isset($data['nota']) ? $conn->real_escape_string($data['nota']) : "";
$data_ritiro = date("Y-m-d H:i:s", strtotime("+20 minutes"));

$items = isset($data['items']) ? $data['items'] : $data;

$conn->query("
    INSERT INTO SB_ordine (stato,metodo,id_utente,nota,data_ritiro)
    VALUES ('In attesa','$metodo',$id_utente,'$nota','$data_ritiro')
    ");

    $id_ordine = $conn->insert_id;


foreach ($items as $item) {

    $nome = $conn->real_escape_string($item['name']);
        $quantita = $item['quantity'];

        $res = $conn->query("SELECT id_prodotto FROM SB_prodotto WHERE nome='$nome'");
        $row = $res->fetch_assoc();

        $id_prodotto = $row['id_prodotto'];

        $conn->query("
        INSERT INTO SB_dettaglio_ordine (id_ordine,id_prodotto,quantita)
        VALUES ($id_ordine,$id_prodotto,$quantita)
        ");
    }

    echo "Ordine salvato";

?>