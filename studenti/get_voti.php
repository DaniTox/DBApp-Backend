<?php

$token = $_REQUEST['token'];

if ($token == null) {
  echo json_encode(geterror("Manca qualche parametro"));
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
  echo json_encode(geterror("id_studente ottenuto = null"));
  $connessione->disconnect();
  exit(1);
}


//OTTIENI VOTI DELLO STUDENTE
$query = "SELECT Voto, Titolo, Data, materia FROM Voti
          JOIN Verifiche ON Voti.idVerifica = Verifiche.idVerifica
          JOIN MateriePerClasse ON Verifiche.idMateriaClasse = MateriePerClasse.id
          JOIN Materie ON MateriePerClasse.idMateria = Materie.idMateria
          WHERE Voti.idStudente = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("i", $id_studente);
$stmt->execute();
$result = $stmt->get_result();
$voti = null;
while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
  if (!empty($row)) {
    $voti[] = array(
      'voto' => $row["Voto"],
      'titolo' => $row["Titolo"],
      'data' => $row["Data"],
      'materia' => $row["materia"],
    );
  }
}
$stmt->close();
if ($voti == null) {
  echo json_encode(geterror("Non ci sono voti"));
  $connessione->disconnect();
  exit(1);
}



//RITORNA IL RISULTATO
$values["code"] = "200";
$values["message"] = "Voti ottenuti correttamente";
$values["voti"] = $voti ;
echo json_encode($values);
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
