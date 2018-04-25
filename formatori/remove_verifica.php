<?php

$token = $_REQUEST['token'];
$id_verifica = $_REQUEST['idVerifica'];

if ($token == null || $id_verifica == null) {
  echo json_encode(geterror(1, "Manca qualche valore"));
  exit(1);
}


//SET UP CONNECTION
$file = file_get_contents("../secure/credentials.json");
$json = json_decode($file, true);
$dbuser = $json["user"];
$dbpasswd = $json["password"];
require_once("../secure/Connection.php");
$connessione = new Connection("localhost", $dbuser, $dbpasswd, "App");
$connessione->connect();


//CHECK IF TOKEN ESISTE NEL DATABASE
$query = "SELECT token FROM Formatori";
$stmt = $connessione->conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$tokens = null;
while ($row = $result->fetch_array(MYSQLI_NUM)) {
  if (!empty($row)) {
    $tokens[] .= $row[0];
  }
}
$stmt->close();
if ($tokens == null) {
  echo json_encode(geterror(1, "tokens nel database = null. Token professore non trovato"));
  $connessione->disconnect();
  exit(1);
}
if (!in_array($token, $tokens)) {
  echo json_encode(geterror(1, "Token non presente nel database"));
  $connessione->disconnect();
  exit(1);
}


//CHECK IF ID VERIFICA ESISTE
$query = "SELECT Titolo FROM Verifiche WHERE idVerifica = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("i", $id_verifica);
$stmt->execute();
$temp_verifica = null;
$stmt->bind_result($temp_verifica);
$stmt->fetch();
$stmt->close();
if ($temp_verifica == null) {
  echo json_encode(geterror(1, "temp_verifica from id_verifica = null"));
  $connessione->disconnect();
  exit(1);
}


//FINALLY ELIMINA VERIFICA
$query = "DELETE FROM Verifiche WHERE idVerifica = ? LIMIT 1";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("i", $id_verifica);
$stmt->execute();
$stmt->close();


//CHECK IF VERIFICA è ELIMINATA VERAMENTE
$query = "SELECT Titolo FROM Verifiche WHERE idVerifica = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("i", $id_verifica);
$stmt->execute();
$temp_verifica_delete = null;
$stmt->bind_result($temp_verifica_delete);
$stmt->fetch();
$stmt->close();
if ($temp_verifica_delete != null) {
  echo json_encode(geterror(1, "Verifica non eliminata correttamente"));
  $connessione->disconnect();
  exit(1);
}


//RITORNA IL RISULTATO
$value["code"] = 0;
$value["message"] = "Verifica eliminata correttamente";
echo json_encode($value);
$connessione->disconnect();
exit(0);


?>


<?php
function geterror($code, $text) {
  $value["code"] = $code;
  $value["message"] = $text;
  return $value;
}
?>
