<?php

$passwd = $_REQUEST["password"];
if ($passwd == null) {
	echo "you shit";
	exit(1);
}

$file = file_get_contents("../secure/credentials.json");
$json = json_decode($file, true);
$passwd_giusta = $json["passwdAdmin"];

if ($passwd != $passwd_giusta) {
	echo "you shitx2";
	exit(1);
}

$dbuser = $json["user"];
$dbpasswd = $json["password"];


require_once("../secure/Connection.php");
$connessione = new Connection("localhost", $dbuser, $dbpasswd, "App");
$connessione->connect();

$query = "SELECT codiceFiscale FROM Studenti2";
$result = $connessione->conn->query($query);
$codici = null;
while ($row = $result->fetch_array(MYSQLI_NUM)) {
  if (!empty($row)) {
    $codici[] .= $row[0];
  }
}
if ($codici == null) {
	echo "wrong";
	exit(1);
}

function updateClasse($codice, $connessione) {
	//GET CLASSE STRING FROM STUDENTE
	$query = "SELECT classe FROM Studenti2 WHERE codiceFiscale = ?";
	$stmt = $connessione->conn->prepare($query);
	$stmt->bind_param("s", $codice);
	$stmt->execute();
	$localClasse = null;
	$stmt->bind_result($localClasse);
	$stmt->fetch();
	$stmt->close();
	if ($localClasse == null) {
		return -1;
	}
	$localClasse = trim(preg_replace('/\s\s+/', ' ', $localClasse));
	echo "---".$localClasse."---<br/>";



	//GET IDCLASSE FROM CLASSE STRING
	$query = "SELECT idClasse FROM Classi WHERE classe = ?";
	$stmt = $connessione->conn->prepare($query);
	$stmt->bind_param("s", $localClasse);
	echo "codice:".$codice."<br/>";
	$stmt->execute();
	$idClasse = null;
	$stmt->bind_result($idClasse);
	$stmt->fetch();
	$stmt->close();
	if ($idClasse == null) {
		return -2;
	}


	//UPDATE IDCLASSE STUDENTE
	$query = "UPDATE Studenti2 SET idClasse = ? WHERE codiceFiscale = ?";
	$stmt = $connessione->conn->prepare($query);
	$stmt->bind_param("is", $idClasse, $codice);
	$stmt->execute();
	$stmt->close();

	echo "--------------------------<br/>";

	return 0;


}

foreach ($codici as $cod) {
	$result = updateClasse($cod, $connessione);
	switch ($result) {
		case 0:
			echo "YEAHHHHH! 0 returned <br/>";
			break;
		case -1:
			echo "Holy shit! -1 returned <br/>";
			break;
		case -2:
			echo "Holy shit! -2 returned <br/>";
			break;
		default:
			break;
	}
}




$connessione->disconnect();



?>