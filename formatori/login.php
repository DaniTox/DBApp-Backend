<?php

$nome = $_REQUEST['nome'];
$password = $_REQUEST['password'];
if ($nome == null || $password == null) {
  echo json_encode(geterror("400", "Manca qualche parametro"));
  exit(1);
}

//INIT CONNESSIONE
require_once("../secure/Connection.php");
$file = file_get_contents("../secure/credentials.json");
$json = json_decode($file, true);
$dbuser = $json["user"];
$dbpasswd = $json["password"];
$connessione = new Connection("localhost", $dbuser, $dbpasswd, "App");
$connessione->connect();


//OTTIENI FORMATORE
$query = "SELECT * FROM Formatori WHERE Formatore = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("s", $nome);
$stmt->execute();
$result = $stmt->get_result();
$formatore = null;
$i = 0;
while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
  if (!empty($row)) {
    if ($i > 0) {
      echo json_encode(geterror("400", "SERVER: Ho trovato più formatori con questo nome. Contatta il mio creatore Bazzani per risolvere."));
      $connessione->disconnect();
      exit(1);
    }
    $formatore = $row;
    $i += 1;
  }
}
$stmt->close();
if ($formatore == null) {
  echo json_encode(geterror("400", "Nessun formatore trovato nel database con questo nome. Controlla di aver scritto il nome nel modo giusto e se il problema persiste, contatta il mio creatore Sir Dan Bazzani"));
  $connessione->disconnect();
  exit(1);
}


//DISCONNETTI DAL DATABASE VISTO CHE NON SERVE PIù
$connessione->disconnect();


//GENERA PASSWORD DA CONFRONTARE CON QUELLA SALVATA
$salt = $formatore["salt"];
$password_typed = hash("sha512", $password.$salt);


//CONFRONTA PASSWORD CON QUELLA SALVATA
if ($password_typed == $formatore["password"]) {
  unset($formatore["password"]);
  unset($formatore["salt"]);
  $formatore["nome"] = $formatore["Formatore"];
  unset($formatore["Formatore"]);

  $value["code"] = "200";
  $value["message"] = "Password corretta";
  $value["formatore"] = $formatore;
  echo json_encode($value);
  exit(0);
}
else {
  echo json_encode(geterror("400", "Password errata"));
  exit(1);
}


?>

<?php

function geterror($code, $text) {
  $value["code"] = $code;
  $value["message"] = $text;
  return $value;
}

?>
