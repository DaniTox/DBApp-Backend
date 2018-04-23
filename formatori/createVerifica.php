<?php

$materia = $_REQUEST['materia'];
$classe = $_REQUEST['classe'];
$data = $_REQUEST['data'];
$titolo = $_REQUEST['titolo'];
$token = $_REQUEST['token'];

$note = $_REQUEST['note'];

if ($materia == null || $classe == null || $data == null || $titolo == null || $token == null) {
  echo json_encode(geterror("Manca qualche parametro"));
  exit(1);
}


//SETTING UP THE CONNECTION TO DATABASE
require_once("../secure/Connection.php");
$file = file_get_contents("../secure/credentials.json");
$json = json_decode($file, true);
$dbuser = $json["user"];
$dbpasswd = $json["password"];
$connessione = new Connection("localhost", $dbuser, $dbpasswd, "App");
$connessione->connect();


//GET ID MATERIA - CLASSE
$query = "SELECT id FROM MateriePerClasse
          JOIN Materie ON MateriePerClasse.idMateria = Materie.idMateria
          JOIN Classi ON MateriePerClasse.idClasse = Classi.idClasse
          WHERE Materie.materia = ? AND Classi.classe = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("ss", $materia, $classe);
$stmt->execute();
$id_materia_classe = null;
$stmt->bind_result($id_materia_classe);
$stmt->fetch();
$stmt->close();
if ($id_materia_classe == null) {
  echo json_encode(geterror("id_materia_classe ottenuto uguale a null"));
  $connessione->disconnect();
  exit(1);
}


//GET ID FORMATORE DAL SUO TOKEN
$query = "SELECT idFormatore FROM Formatori WHERE token = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$id_formatore = null;
$stmt->bind_result($id_formatore);
$stmt->fetch();
$stmt->close();
if ($id_formatore == null) {
  echo json_encode(geterror("id_formatore ottenuto  uguale a null"));
  $connessione->disconnect();
  exit(1);
}


//CREATE VERIFICA
$query = "INSERT INTO Verifiche (idMateriaClasse, Data, Titolo, idFormatore, note) VALUES (?,?,?,?,?)";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("issis", $id_materia_classe, $data, $titolo, $id_formatore, $note);
$stmt->execute();
$stmt->close();


//RITORNA IL RISULTATO
$value["code"] = 0;
$value["message"] = "Verifica creata con successo";
echo json_encode($value);
$connessione->disconnect();
exit(0);


?>


<?php

function geterror($text) {
  $value["code"] = 1;
  $value["message"] = $text;
  return $value;
}

?>
