<?php

$codFiscale = $_REQUEST['codice_fiscale'];
$email = $_REQUEST['email'];
$password = $_REQUEST['password'];

if ($codFiscale == null || $email == null || $password == null) {
  echo json_encode(geterror('400', 'Manca qualche parametro'));
  exit(1);
}


$file = $file_get_contents("credentials.json");
$json = json_decode($file, true);
$dbuser = $json['user'];
$dbpasswd = $json['password'];
require_once("Connection.php");
$connessione = new Connection("localhost", $dbuser, $dbpasswd, "App");
$connessione->connect();

//CHECK IF ALREADY EXIST
$query = "SELECT email FROM Studenti WHERE codiceFiscale = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("s", $codFiscale);
$stmt->execute();
$stds = null;
$stmt->bind_result($stds);
$stmt->fetch();
$stmt->close();
if (count($stds) > 0 ) {
  echo json_encode(geterror("400", "Hai già effettuato la registrazione. Prova ad accedere"));
  $connessione->disconnect();
  exit(1);
}


//GENERA TOKEN, SALT, PASSWD HASHED
$query = "SELECT token FROM Studenti";
$result = $connessione->conn->query($query);
$tokens = null;
while ($row = $result->fetch_array(MYSQLI_NUM)) {
  if (!empty($row)) {
    $tokens[] .= $row[0];
  }
}
$token = generateToken();
if ($tokens != null) {
  while (in_array($token, $tokens)) {
      $token = generateToken();
  }
}
$salt = createSalt();
$passwdHashed = hash("sha512", ($password.$salt));


//REGISTRA L'UTENTE
$query = "UPDATE Studenti SET email = ?, password = ?, salt= ?, token= ? WHERE codiceFiscale = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("sssss", $email, $password, $salt, $token, $codFiscale);
$stmt->execute();
$stmt->close();


//OTTIENI LO STUDENTE APPENA REGISTRATO
$query = "SELECT * FROM Studenti WHERE codiceFiscale = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("s", $codFiscale);
$stmt->execute();
$user = null;
$result = $stmt->get_result();
while($row = $result->fetch_array(MYSQLI_ASSOC)) {
  if (!empty($row)) {
    $user = $row;
  }
}
$stmt->close();
if ($user == null) {
  echo json_encode(geterror("user ottenuto = null"));
  dbabort();
}


//OTTIENI LA CLASSE DAL SUO ID
$idClasse = $user["idClasse"];
$query = "SELECT classe FROM Classi WHERE idClasse = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("i", $idClasse);
$stmt->execute();
$classe = null;
$stmt->bind_result($classe);
$stmt->fetch();
$stmt->close();



//CONVERTI VAR DELL'UTENTE
unset($user["idClasse"]);
unset($user["salt"]);
unset($user["password"]);
$user["classe"] = $classe;


//FINE DELLA PROCEDURA
$connessione->disconnect();
$value["code"] = "200";
$value["message"] = "Utente registrato con successo";
$value["studente"] = $user;
echo json_encode($value);
exit(0);



?>

<?php
function geterror($code, $msg) {
  $value['code'] = $code;
  $value['message'] = $msg;
  return $value;
}
function generateToken() {
  $lettere = "avcgj38e5sf25sa0jcn3862r5c86vae217329r3hfbc81625261";
    $token = null;
    for ($i = 0; $i < 60; $i++ ) {
        $tempChar = $lettere[rand(0, strlen($lettere) - 1)];
        $token .= $tempChar;
    }
    return $token;
}
function createSalt() {
  $lettere = "avcgj38e5sf25sa0jcn3862r5c86vae217329r3hfbc81625261";
    $salt = null;
    for ($i = 0; $i < 50; $i++) {
        $tempChar = $lettere[rand(0, strlen($lettere) - 1)];
        $salt .= $tempChar;
    }
    return $salt;
}

?>