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

      private function get_Result_Set($sql,$tabella="",$id="",$fetch=PDO::FETCH_BOTH)
      {
        $stmt= $this->conn -> prepare($sql);
        $stmt->execute();

        $data=$stmt->fetchAll($fetch);

        return $data;
      }

      private function getSql($tabella,$id)
      {

      }

      function updateOrdine($tabella,$id)
      {
        $query=getSql($tabella,$id,0);
      }

      function deleteOrdine()
      {
        $query=getSql($tabella,$id,1);
      }

      function getOrdineById()
      {
        $query=getSql($tabella,$id,2);
      }

      function changeStatus()
      {
        $query=getSql($tabella,$id,3);
      }

    }

?>