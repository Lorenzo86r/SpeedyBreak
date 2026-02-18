<?php

    class Database()
    {

      private $conn;
      private $connected;
      private $msg;
    
      function __construct($servername,$dbname,$username,$password)
      {

        try
        {
            $this->conn = new PDO ("mysql:host=$servername;dbname=$dbname",$username,$password);
            $this->conn -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $this->connected=true;
        }
        catch(PDOException $e)
        {
            $this->msg=$e->getMessage();
        }

      }

      function updateOrdine()
      {

      }

      function deleteOrdine()
      {

      }

      function getOrdineById()
      {

      }

      function changeStatus()
      {

      }

    }

?>