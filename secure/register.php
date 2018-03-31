<?php

//FUNZIONI AUSILIARIE DELLO SCRIPT
function dbabort() {
  if ($connessione != null) {
    $connessione->disconnect();
  }
  exit(1);
}


//OTTIENI VARIABILI
$nome = $_REQUEST['nome'];
$cognome = $_REQUEST['cognome'];
$email = $_REQUEST['email'];
$password = $_REQUEST['password'];
$classe = $_REQUEST['classe'];

if ($nome == null || $cognome == null || $email == null || $password == null || $classe == null) {
  echo json_encode(geterror("Manca qualche valore"));
  exit(1);
}


if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
  echo json_encode(geterror("Formato e-mail non corretto. Controlla se è scritto correttamente"));
  exit(1);
}


//INIT LA CONNESSIONE
require_once("Connection.php");
$file = file_get_contents("credentials.json");
$json = json_decode($file, true);
$dbuser = $json["user"];
$dbpasswd = $json["password"];
$connessione = new Connection("localhost", $dbuser, $dbpasswd, "App");
$connessione->connect();


//CHECK IF ALREADY EXIST IN DATABASE
$query = "SELECT cognome FROM Studenti WHERE email = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$emails = null;
$result = $stmt->get_result();
while ($row = $result->fetch_array(MYSQLI_NUM)) {
  if (!empty($row)) {
    $emails = $row;
  }
}
$stmt->close();
if (count($emails) > 0) {
  echo json_encode(geterror("Account già presente nel database"));
  dbabort();
}



//GET ID CLASSE FROM STRING
$query = "SELECT idClasse FROM Classi WHERE classe = ?";
$idClasse = null;
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("s", $classe);
$stmt->execute();
$stmt->bind_result($idClasse);
$stmt->fetch();
$stmt->close();
if ($idClasse == null) {
  echo json_encode(geterror("idClasse ottenuto è null"));
  dbabort();
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
$query = "INSERT INTO Studenti (nome, cognome, email, password, salt, idClasse, token) VALUES (?,?,?,?,?,?,?)";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("sssssis", $nome, $cognome, $email, $passwdHashed, $salt, $idClasse, $token);
$stmt->execute();
$stmt->close();


//OTTIENI LO STUDENTE APPENA REGISTRATO
$query = "SELECT * FROM Studenti WHERE email = ?";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("s", $email);
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


//FUNZIONI AUSILIARIE
function geterror($text) {
  $value["code"] = "400";
  $value["message"] = $text;
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
