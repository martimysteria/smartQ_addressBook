<?php

class DB 
{
private $db_name = "addressbook";
private $user = "root";
private $pass = "IvanA1994";
private $server = "127.0.0.1";  
private $db_conn;

public function __construct(){

    try{
        $this->db_conn = new PDO(
            "mysql:host=$this->server;dbname=$this->db_name",$this->user,$this->pass
        );
        $this->db_conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    catch(Exception $e) {
        echo 'No coonectio to DB : ' .$e->getMessage();
      }




}

public function getDb(){
    return $this->db_conn;
}

}



?>