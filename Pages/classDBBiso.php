<?php

namespace Pages;
class classDBBiso
{
    // variabili membro (attributi) //
    private $conn, $connected, $msg;

    // costruttore //
    function __construct($serverName, $dbName, $email, $password)
    {
        $this->msg = "";
        try {
            // Fondamentale usare $this->conn
            $this->conn = new PDO("mysql:host=$serverName;dbname=$dbName", $email, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connected = true;
        } catch (PDOException $e) {
            $this->connected = false;
            $this->msg = $e->getMessage();
        }
    }

    // GETTER //
    function get_conn()
    {
        return $this->conn;
    }

    function get_connected()
    {
        return $this->connected;
    }

    function get_msg()
    {
        return $this->msg;
    }

    // METODI //

    // salvataggio credenziali sessione sul db
    public function save_credentials($username, $password)
    {
        // controllo esistenza utente
        $exist = $this->conn->query("SELECT username FROM sessione_utente WHERE username = '$username'");
        if ($exist->rowCount() > 0) return true; // username esistente => non creato

        $sql = "INSERT INTO sessione_utente (username, password_hash) VALUES (:username, :password)"; // query per db
        $cmd = $this->conn->prepare($sql);
        $cmd->bindParam(":username", $username);
        $cmd->bindParam(":password", $password);
        $cmd->execute();
        return false; // username non esistente => creato
    }

    // verifica credenziali sessione sul db
    public function check_credentials($username, $password)
    {
        $sql = "SELECT password_hash FROM sessione_utente WHERE username = :username"; // query per db

        // preparazione comando
        $cmd = $this->conn->prepare($sql);

        $cmd->execute([':username' => $username]);
        $hash = $cmd->fetchColumn();

        if (!$hash) return false; // user non trovato

        return password_verify($password, $hash); // verifica password
    }
}