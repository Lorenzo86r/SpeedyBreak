<?php

    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "my_saqlain";

    $conn = new mysqli($host, $user, $pass, $db);

    $data = json_decode(file_get_contents("php://input"), true);

    $id_utente = $_SESSION['user_id'];;

    $data_ritiro = date("Y-m-d H:i:s", strtotime("+20 minutes"));

    $conn->query("
    INSERT INTO SB_ordine (stato,metodo,id_utente,nota,data_ritiro)
    VALUES ('In attesa','Contanti',$id_utente,'',$data_ritiro)
    ");

    $id_ordine = $conn->insert_id;

    foreach ($data as $item) {

        $name = $conn->real_escape_string($item['name']);
        $q = $item['quantity'];

        $res = $conn->query("SELECT id_prodotto FROM SB_prodotto WHERE nome='$name'");
        $row = $res->fetch_assoc();

        $id_prodotto = $row['id_prodotto'];

        $conn->query("
    INSERT INTO SB_dettaglio_ordine (id_ordine,id_prodotto,quantita)
    VALUES ($id_ordine,$id_prodotto,$q)
    ");

    }

    echo "ok";
?>