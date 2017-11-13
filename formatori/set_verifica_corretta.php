<?php

$id_verifica = $_REQUEST["idVerifica"];
$token = $_REQUEST["token"];

if ($id_verifica == null || $token == null) {
  echo json_encode(geterror("Manca qualche parametro"));
  exit(1);
}


//SET UP CONNECTION
require_once("../secure/Connection.php");
$file = file_get_contents("credentials.json");
$json = json_decode($file, true);
$dbuser = $json["user"];
$dbpasswd = $json["password"];
$connessione = new Connection("localhost", $dbuser, $dbpasswd, "App");
$connessione->connect();


//CHECK SE IL TOKEN DEL FORMATORE ESISTE NEL DATABASE
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
if (!in_array($token, $tokens)) {
  echo json_encode(geterror("Token professore non presente nel database"));
  $connessione->disconnect();
  exit(1);
}


//CHECK SE L'ID VERIFICA ESISTE
$query = "SELECT Titolo FROM Verifiche WHERE idVerifica = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("i", $id_verifica);
$stmt->execute();
$temp_verifica = null;
$stmt->bind_result($temp_verifica);
$stmt->fetch();
$stmt->close();
if($temp_verifica == null) {
  echo json_encode(geterror("ID VERIFICA NON PRESENTE NEL DATABASE"));
  $connessione->disconnect();
  exit(1);
}


//SET VERIFICA CORRETTA
$query = "UPDATE Verifiche SET Svolgimento = 1 WHERE idVerifica = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("i", $id_verifica);
$stmt->execute();
$stmt->close();


//RITORNA IL RISULTATO
$value["code"] = "200";
$value["message"] = "Verifica aggiornata in modo corretto!";
echo json_encode($value);
$connessione->disconnect();
exit(0);

?>


<?php

function geterror($text) {
  $value["code"] = "400";
  $value["message"] = $text;
  return $value;
}

?>
