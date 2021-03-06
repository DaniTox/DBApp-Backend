<?php

$token = $_REQUEST['token'];
$id_verifica = $_REQUEST['idVerifica'];
$voto = $_REQUEST['voto'];

if ($token == null || $id_verifica == null || $voto == null) {
  echo json_encode(geterror("Manca qualche valore"));
  exit(1);
}

//CHECK VOTO
if ($voto < 0 || $voto > 100) {
  echo json_encode(geterror("Voto troppo basso o troppo alto"));
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


//GET ID STUDENTE DAL TOKEN
$query = "SELECT id, idClasse FROM Studenti WHERE token = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$id_studente = null;
$id_classe = null;
$stmt->bind_result($id_studente, $id_classe);
$stmt->fetch();
$stmt->close();
if ($id_studente == null) {
  echo json_encode(geterror("Errore: id_studente ottenuto = null"));
  $connessione->disconnect();
  exit(1);
}
if ($id_classe == 1 || $id_classe == 5) {
  if ($voto > 3) {
    echo json_encode(geterror("Visto che sei in prima, i voti vanno da 0 a 3. Inserisci il tuo voto vero compreso tra questo range."));
    exit(1);
  }
}


//CHECK IF VERIFICA ESISTE
$query = "SELECT idVerifica FROM Verifiche WHERE idVerifica = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("i", $id_verifica);
$stmt->execute();
$temp = null;
$stmt->bind_result($temp);
$stmt->fetch();
$stmt->close();
if ($temp == null) {
  echo json_encode(geterror("idVerifica non trovata nel database. Probabilmente il professore ha eliminato la verifica. Prova a riaggiornare la pagina delle verifiche e riprova."));
  $connessione->disconnect();
  exit(1);
}


//CHECK IF STUDENTE ALREADY SENT THIS VOTO
$query = "SELECT id FROM Voti WHERE idStudente = ? AND idVerifica = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("ii", $id_studente, $id_verifica);
$stmt->execute();
$id = null;
$stmt->bind_result($id);
$stmt->fetch();
$stmt->close();
if ($id != null) {
  echo json_encode(geterror("Hai già inserito questo voto. Torna nella pagina delle verifiche e trascina per aggiornare le verifiche."));
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
