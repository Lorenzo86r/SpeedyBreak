<?php

class Database
{
    private $conn;

    function __construct($servername, $dbname, $username, $password)
    {
        try {
            $this->conn = new PDO(
                "mysql:host=$servername;dbname=$dbname;charset=utf8",
                $username,
                $password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Errore DB: " . $e->getMessage());
        }
    }

    private function get_Result_Set($sql, $params = [], $fetch = PDO::FETCH_ASSOC)
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll($fetch);
    }

    function getAllOrdiniAttivi()
    {
        $sql = "SELECT 
    				a.id_utente,
    				c.id_ordine,
    				a.username,
    				a.email,
    				b.nome,
    				c.quantita,
    				d.data_ordine,
    				d.data_ritiro
                    
				FROM SB_utente AS a
				JOIN SB_ordine AS d ON a.id_utente = d.id_utente
				JOIN SB_dettaglio_ordine AS c ON c.id_ordine = d.id_ordine
				JOIN SB_prodotto AS b ON c.id_prodotto = b.id_prodotto
				JOIN SB_categoria AS e ON b.id_categoria = e.id_categoria
				WHERE d.stato != 'Completato'
				ORDER BY d.data_ordine DESC, a.id_utente";
                
        return $this->get_Result_Set($sql);
    }

    function getStoricoOrdini()
    {
        $sql = "SELECT 
    				a.id_utente,
    				c.id_ordine,
    				a.username,
    				a.email,
    				b.nome,
    				c.quantita,
    				d.data_ordine,
    				d.data_ritiro,
                    d.metodo,
                    d.nota
                    
				FROM SB_utente AS a
				JOIN SB_ordine AS d ON a.id_utente = d.id_utente
				JOIN SB_dettaglio_ordine AS c ON c.id_ordine = d.id_ordine
				JOIN SB_prodotto AS b ON c.id_prodotto = b.id_prodotto
				JOIN SB_categoria AS e ON b.id_categoria = e.id_categoria
				WHERE d.stato = 'Completato'
				ORDER BY d.data_ordine DESC, a.id_utente";
                
        return $this->get_Result_Set($sql);
    }

    function getOrdineById($id)
    {
        $sql = "SELECT o.*, u.username, u.email, u.ruolo
                FROM SB_ordine o
                JOIN SB_utente u ON o.id_utente = u.id_utente
                WHERE o.id_ordine = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":id" => $id]);
        $ordine = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ordine) return null;

        $sql2 = "SELECT p.nome, p.prezzo, d.quantita
                 FROM SB_dettaglio_ordine d
                 JOIN SB_prodotto p ON d.id_prodotto = p.id_prodotto
                 WHERE d.id_ordine = :id";

        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute([":id" => $id]);
        $ordine["prodotti"] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return $ordine;
    }

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

    function deleteOrdine($id)
    {
        try {
            $this->conn->beginTransaction();

            $this->conn->prepare("DELETE FROM SB_dettaglio_ordine WHERE id_ordine = :id")
                       ->execute([":id" => $id]);

            $this->conn->prepare("DELETE FROM SB_ordine WHERE id_ordine = :id")
                       ->execute([":id" => $id]);

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    function changeStatus($id, $nuovoStato)
    {
        $sql = "UPDATE SB_ordine SET stato = :stato WHERE id_ordine = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([":stato" => $nuovoStato, ":id" => $id]);
    }
}

?>
