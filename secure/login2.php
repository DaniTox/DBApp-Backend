<?php


//INIT VARIABILI
$email = $_REQUEST["email"];
$password = $_REQUEST["password"];
if ($email == null || $password == null) {
  echo json_encode(geterror("400","Manca email e/o password"));
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


//CHECK IF STUDENTE è REGISTRATO
$query = "SELECT isRegistered FROM Studenti2 WHERE email = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$temp = null;
$stmt->bind_result($temp);
$stmt->fetch();
$stmt->close();
if ($temp == null || $temp == 0) {
	echo json_encode(geterror("400", "Studente non ancora registrato nel database..."));
	exit(1);
}


//OTTIENI STUDENTE
$query = "SELECT * FROM Studenti2 WHERE email = ?";
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
if ($user == null) {
  echo json_encode(geterror("400","Nessuno studente trovato nel database con questa mail"));
  $connessione->disconnect();
  exit(1);
}


//GET CLASSE STRING DA ID
$idClasse = $user["idClasse"];
print_r($user);
$query = "SELECT classe FROM Classi WHERE idClasse = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("i", $idClasse);
$stmt->execute();
$classe = null;
$stmt->bind_result($classe);
$stmt->fetch();
$stmt->close();
if ($classe == null) {
  echo json_encode(geterror("400","classe string ottenuto == null"));
}


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
  unset($user["password"]);
  unset($user["isRegistered"]);
  unset($user["codiceFiscale"]);

  $value["code"] = "200";
  $value["message"] = "Password corretta";
  $value["studente"] = $user;
  echo json_encode($value);
  exit(0);
}
else {
  echo json_encode(geterror("400","Password errata"));
  exit(1);
}

?>




<?php


//FUNZIONI AUSILIARIE
 function geterror($code, $text) {
  $value["code"] = $code;
  $value["message"] = $text;
  return $value;
}

?>
