<?php

$classe = $_REQUEST['classe'];
$token = $_REQUEST['token'];

if ($classe == null) {
  echo json_encode(geterror("Manca un parametro"));
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


//GET VERIFICHE BY CLASSE
$query = "SELECT idVerifica, Data, Svolgimento, Titolo, classe, Materia, Formatore FROM Verifiche
          JOIN MateriePerClasse ON Verifiche.idMateriaClasse = MateriePerClasse.id
          JOIN Classi ON MateriePerClasse.idClasse = Classi.idClasse
          JOIN Materie ON MateriePerClasse.idMateria = Materie.idMateria
          JOIN Formatori ON Verifiche.idFormatore = Formatori.idFormatore
          WHERE Classi.classe = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("s", $classe);
$stmt->execute();
$result = $stmt->get_result();
$verifiche = null;
while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
  if (!empty($row)) {
    $verifiche[] = array(
      "idVerifica" => $row["idVerifica"],
      "data" => $row["Data"],
      "svolgimento" => $row["Svolgimento"],
      "titolo" => $row["Titolo"],
      "classe" => $row["classe"],
      "materia" => $row["Materia"],
      "formatore" => $row["Formatore"],
    );
  }
}
$stmt->close();


//CHECK IF STUDENTE HAS VOTO INVIATO
while ($token != null && $verifiche != null) {

  //GET ID_STUDENTE FROM TOKEN
  $query = "SELECT id FROM Studenti WHERE token = ?";
  $stmt = $connessione->conn->prepare($query);
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $id_studente = null;
  $stmt->bind_result($id_studente);
  $stmt->fetch();
  $stmt->close();
  if ($id_studente == null) { break; }


  //SELECT IDVERIFICHE DOVE VOTI GIà INVIATI
  $query = "SELECT idVerifica FROM Voti WHERE idStudente = ?";
  $stmt = $connessione->conn->prepare($query);
  $stmt->bind_param("i", $id_studente);
  $stmt->execute();
  $result = $stmt->get_result();
  $id_verifiche_inviate = null;
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    if (empty($row)) { break; }
    $id_verifiche_inviate[] .= $row[0];
  }
  $stmt->close();
  if ($id_verifiche_inviate == null) { break; }


  //CREATE A NEW ARRAY WITH ARRAY VERIFICHE MODIFICATE
  $newVerifiche = array();
  foreach ($verifiche as $verifica) {
    foreach ($verifica as $key => $value) {
      if ($key != "idVerifica") { continue; }
      if (in_array($value, $id_verifiche_inviate)) {
        $verifica["isVotoSent"] = True;
        $newVerifiche[] = $verifica;
        continue;
      }
      $verifica["isVotoSent"] = False;
      $newVerifiche[] = $verifica;

    }
  }

  break;
}

if ($token == null || $id_studente == null || $id_verifiche_inviate == null) {
  $newVerifiche = $verifiche;
}


//RITORNA IL RISULTATO
$response["code"] = "200";
$response["message"] = "Ottenute con successo";
$response["verifiche"] = $newVerifiche;
echo json_encode($response);
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
