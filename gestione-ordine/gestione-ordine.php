
<?php

class Database
{
    private $conn;
    private $connected = false;
    private $msg = "";

    // Costruttore: connessione al DB
    function __construct($servername, $dbname, $username, $password)
    {
        try {
            $this->conn = new PDO(
                "mysql:host=$servername;dbname=$dbname;charset=utf8",
                $username,
                $password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connected = true;
        } catch (PDOException $e) {
            $this->msg = $e->getMessage();
        }
    }

    // Metodo interno per query SELECT
    private function get_Result_Set($sql, $params = [], $fetch = PDO::FETCH_ASSOC)
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll($fetch);
    }

    // ------------------------------
    // FUNZIONI RICHIESTE
    // ------------------------------

    // Aggiorna un ordine
    function updateOrdine($id, $data)
    {
        $sql = "UPDATE SB_ordine 
                SET stato = :stato,
                    metodo = :metodo,
                    nota = :nota,
                    data_ritiro = :data_ritiro
                WHERE id_ordine = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":stato" => $data["stato"],
            ":metodo" => $data["metodo"],
            ":nota" => $data["nota"],
            ":data_ritiro" => $data["data_ritiro"],
            ":id" => $id
        ]);
    }

    // Elimina un ordine (prima dettagli, poi ordine)
    function deleteOrdine($id)
    {
        try {
            $this->conn->beginTransaction();

            // Elimina dettagli
            $sql1 = "DELETE FROM SB_dettaglio_ordine WHERE id_ordine = :id";
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->execute([":id" => $id]);

            // Elimina ordine
            $sql2 = "DELETE FROM SB_ordine WHERE id_ordine = :id";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute([":id" => $id]);

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Recupera un ordine completo (ordine + utente + prodotti)
    function getOrdineById($id)
    {
        // Recupero ordine + utente
        $sql = "SELECT o.*, u.nome, u.cognome, u.email
                FROM SB_ordine o
                JOIN SB_utente u ON o.id_utente = u.id_utente
                WHERE o.id_ordine = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":id" => $id]);
        $ordine = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ordine) {
            return null;
        }

        // Recupero prodotti dell’ordine
        $sql2 = "SELECT p.nome, p.prezzo, d.quantità
                 FROM SB_dettaglio_ordine d
                 JOIN SB_prodotto p ON d.id_prodotto = p.id_prodotto
                 WHERE d.id_ordine = :id";

        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute([":id" => $id]);
        $prodotti = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $ordine["prodotti"] = $prodotti;

        return $ordine;
    }

    // Cambia lo stato di un ordine
    function changeStatus($id, $nuovoStato)
    {
        $sql = "UPDATE SB_ordine SET stato = :stato WHERE id_ordine = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":stato" => $nuovoStato,
            ":id" => $id
        ]);
    }
}

?>
