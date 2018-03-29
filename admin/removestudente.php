<?php

$passwd = $_REQUEST["password"];
$nome = $_REQUEST["nome"];

if ($passwd == null || $nome == null) {
	echo "u shit. no passwd || no name";
	exit(1);
}

$file = file_get_contents("../secure/credentials.json");
$json = json_decode($file, true);
$r_passwd = $json["passwdAdmin"];

if ($passwd != $r_passwd) {
	echo "u shitx2";
	exit(1);
}

$dbn = $json["user"];
$dbp = $json["password"];

require_once("../secure/Connection.php");
$c = new Connection("localhost", $dbn, $dbp, "App");
$c->connect();


$nome = strtoupper($nome);

$query = "UPDATE Studenti2 SET email = '', password = '', salt = '', token = '', isRegistered = 0 WHERE nome = ?";
$stmt = $c->conn->prepare($query);
$stmt->bind_param("s", $nome);
$stmt->execute();
$stmt->close();

echo "done.";

$c->disconnect();



?>
