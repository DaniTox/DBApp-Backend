<?php

$classe = $_REQUEST["classe"];

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
    $verifiche[] = $row;
  }
}
$stmt->close();


//RIORDINA IL RISULTATO
$newVerifiche = array();
foreach ($verifiche as $verifica) {
  foreach ($verifica as $key => $value) {
    if ($key == "Materia") {
      if ($newVerifiche[$value] == null) {
        $newVerifiche[$value] = array();
      }
      array_push($newVerifiche[$value], $verifica);
    }
  }
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
