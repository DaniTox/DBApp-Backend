<?php

class Connection {

  private $host = null;
  private $username = null;
  private $password = null;
  private  $name = null;

  var $conn = null;


  function __construct($dbhost, $dbusername, $dbpasswd, $dbname) {
      $this->host = $dbhost;
      $this->username = $dbusername;
      $this->password = $dbpasswd;
      $this->name = $dbname;
  }

  function connect() {
      $this->conn = new mysqli($this->host,$this->username,$this->password,
          $this->name);

      if (mysqli_connect_errno()) {
          echo "Impossibile connetersi al database: " . mysqli_connect_error() . "\n";
      }

      $this->conn->set_charset("utf8");
  }

  function disconnect() {
      if ($this->conn != null) {
          $this->conn->close();
      }
  }


}

?>
