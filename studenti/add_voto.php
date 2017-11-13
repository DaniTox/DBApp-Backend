<?php

$token = $_REQUEST['token'];
$id_verifica = $_REQUEST['idVerifica'];
$voto = $_REQUEST['voto'];

if ($token == null || $id_verifica == null || $voto == null) {
  echo json_encode(geterror("Manca qualche valore"));
  exit(1);
}


//SET UP CONNECTION
$file = file_get_contents("credentials.json");
$json = json_decode($file, true);
$dbuser = $json["user"];
$dbpasswd = $json["password"];
require_once("../secure/Connection.php");
$connessione = new Connection("localhost", $dbuser, $dbpasswd, "App");
$connessione->connect();


//GET ID STUDENTE DAL TOKEN
$query = "SELECT id FROM Studenti WHERE token = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$id_studente = null;
$stmt->bind_result($id_studente);
$stmt->fetch();
$stmt->close();
if ($id_studente == null) {
  echo json_encode(geterror("Errore: id_studente ottenuto = null"));
  $connessione->disconnect();
  exit(1);
}


//INSERISCI IL VOTO NEL DATABASE
$query = "INSERT INTO Voti (idStudente, idVerifica, Voto) VALUES (?, ?, ?)";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("iii", $id_studente, $id_verifica, $voto);
$stmt->execute();
$stmt->close();


//RITORNA IL RISULTATO
$value["code"] = "200";
$value["message"] = "Il voto è stato inserito correttamente";
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
