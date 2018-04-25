<?php

$token = $_REQUEST['token'];

if ($token == null) {
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
  echo json_encode(geterror(1, "id_formatore ottenuto = null"));
  $connessione->disconnect();
  exit(1);
}


//GET VERIFICHE DEL FORMATORE
$query = "SELECT idVerifica, materia, Titolo, Data, classe, dev_mode FROM Verifiche
          JOIN MateriePerClasse ON Verifiche.idMateriaClasse = MateriePerClasse.id
          JOIN Classi ON MateriePerClasse.idClasse = Classi.idClasse
          JOIN Materie ON MateriePerClasse.idMateria = Materie.idMateria
          WHERE Verifiche.idFormatore = ? AND Svolgimento = 0";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("i", $id_formatore);
$stmt->execute();
$result = $stmt->get_result();
$verifiche = null;
while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
  if (!empty($row)) {
    $verifiche[] = array(
      "idVerifica" => $row["idVerifica"],
      "materia" => $row["materia"],
      "titolo" => $row["Titolo"],
      "data" => $row["Data"],
      "classe" => $row["classe"],
      "dev_mode" => $row["dev_mode"],
    );
  }
}
$stmt->close();
if ($verifiche == null) {
  echo json_encode(geterror(2, "Non ci sono verifiche"));
  $connessione->disconnect();
  exit(0);
}


//RITORNA IL RISULTATO
$value["code"] = 0;
$value["message"] = "Verifiche recuperate con successo";
$value["verifiche"] = $verifiche;
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
