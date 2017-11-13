<?php


//INIT VARIABILI
$email = $_REQUEST["email"];
$password = $_REQUEST["password"];
if ($email == null || $password == null) {
  echo json_encode(getError("Manca email e/o password"));
  exit(1);
}


//INIT CONNESSIONE
require_once("Connection.php");
$file = file_get_contents("credentials.json");
$json = json_decode($file, true);
$dbuser = $json["user"];
$dbpasswd = $json["password"];
$connessione = new Connection("localhost", $dbuser, $dbpasswd, "App");
$connessione->connect();


//OTTIENI STUDENTE
$query = "SELECT * FROM Studenti WHERE email = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = null;
while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
  if (!empty($row)) {
    $user = $row;
  }
}
$stmt->close();



//GET CLASSE STRING DA ID
$idClasse = $user["idClasse"];
$query = "SELECT classe FROM Classi WHERE idClasse = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("i", $idClasse);
$stmt->execute();
$classe = null;
$stmt->bind_result($classe);
$stmt->fetch();
$stmt->close();



//DISCONNETTI LA CONN AL DATABASE
$connessione->disconnect();



//GENERA PASSWORD DA CONFRONTARE
$salt = $user["salt"];
$finalPasswdTyped = hash("sha512", $password.$salt);



//CONFRONTA PASSWORD
if ($finalPasswdTyped == $user["password"]) {
  $user["classe"] = $classe;
  unset($user["idClasse"]);
  unset($user["salt"]);

  $value["code"] = "200";
  $value["message"] = "Password corretta";
  $value["user"] = $user;
  echo json_encode($value);
  exit(0);
}
else {
  echo json_encode(getError("Password errata"));
  exit(1);
}

?>




<?php


//FUNZIONI AUSILIARIE
 function getError($text) {
  $value["code"] = "400";
  $value["message"] = $text;
  return $value;
}

?>
