<?php
//SCRIPT FOR UPDATING LATEST VERSION

$password = $_REQUEST["password"];
if ($password == null ) {
	echo "NO PASSWORD";
	exit(1);
}
$file = file_get_contents("../secure/credentials.json");
$json = json_decode($file, true);
$rightPasswd = $json["passwdAdmin"];

if ($password != $rightPasswd) {
	echo "PASSWORD ERRATA";
	exit(1);
}


$version = $_REQUEST["version"];
$sub_vers = $_REQUEST["sub_vers"];
$sub_vers2 = $_REQUEST["sub_vers2"];
$build = $_REQUEST["build"];
$hotfix = $_REQUEST["hotfix"];

if ($version == null || $build == null) {
	echo json_encode(geterror("400", "Manca qualche parametro..."));
	exit(1);
}
if ($hotfix == null) {
	$hotfix = 0;
}

//INIT CONNECTION   
$file = file_get_contents("../secure/credentials.json");
$json = json_decode($file, true);
$user = $json["user"];
$passwd = $json["password"];
require_once("../secure/Connection.php");
$connessione = new Connection("localhost", $user, $passwd, "App");
$connessione->connect();


$currDate = date("Y-m-d H:i:s");

if ($sub_vers == null) {
	$sub_vers = 0;
}
if ($sub_vers2 == null) {
	$sub_vers2 = 0;
}

//QUERY
$query = "INSERT INTO Versions (version, sub_version, sub_version2, build, released, hotfix) VALUES (?,?,?,?,?,?)";
$stmt = $connessione->conn->prepare($query);
$stmt->bind_param("iiidsi", $version, $sub_vers, $sub_vers2, $build, $currDate, $hotfix);
$stmt->execute();
$stmt->close();
$connessione->disconnect();

echo $currDate;

?>


<?php

function geterror($code, $msg) {
    $value["code"] = $code;
    $value["message"] = $msg;
    return $value;
}

?>