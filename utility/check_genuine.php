<?php

$token = $_REQUEST['token'];

if ($token == null) {
	echo json_encode(geterror("400", "Manca qualche parametro"));
	exit(1);
}


$file = file_get_contents("../secure/credentials.json");
$json = json_decode($file, true);
$dbuser = $json["user"];
$dbpasswd = $json["password"];

require_once("../secure/Connection.php");
$connessione = new Connection("localhost", $dbuser, $dbpasswd, "App");
$connessione->connect();


$query = "SELECT id FROM Studenti WHERE token = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$id_user = null;
$stmt->bind_result($id_user);
$stmt->fetch();
$stmt->close();

$connessione->disconnect();

if ($id_user == null) {
  echo json_encode(geterror("-1", "L'account non esiste più"));
  exit(1);
}
else {
 echo json_encode(geterror("200", "still genuine"));
  exit(0);
}

?>

<?php
function geterror($code, $msg) {
  $value["code"] = $code;
  $value["message"] = $msg;
  return $value;
}
?>
