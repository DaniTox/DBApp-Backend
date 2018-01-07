<?php

$versione = $_REQUEST['version'];
$build = $_REQUEST['build'];

if ($versione == null || $build == null) {
    echo json_encode(geterror("400", "Manca qualche parametro"));
    exit(1);
}

//INIT CONNECTION   
$file = file_get_contents("../secure/credentials.json");
$json = json_decode($file, true);
$user = json["user"];
$passwd = json["password"];
require_once("../secure/Connection.php");
$connessione = new Connection("localhost", $user, $passwd, "App");
$connessione->connect();

//GET MOST RECENT VERSION
$query = "SELECT version, build FROM Versions ORDER BY id DESC LIMIT 1";
$stmt = $connessione->conn->prepare($query);
$stmt->execute();
$up_vers = null;
$up_build = null;
$stmt->bind_result($up_vers, $up_build);
$stmt->fetch();
$stmt->close();
if ($up_build == null || $up_vers == null) {
    echo json_encode(geterror("400", "Errore mentre ottenevo le versioni"));
    exit(1);
}


//CONFRONTO VERSIONE STUDENTE VS VERSIONE AGGIORNATA
if ($up_build > $build && $up_vers >= $versione) {
    
}


?>

<?php

function geterror($code, $msg) {
    $value["code"] = $code;
    $value["message"] = $msg;
    return $value;
}

?>