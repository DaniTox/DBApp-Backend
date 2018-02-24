<?php

$currVers = $_REQUEST['version'];
$currSubv = $_REQUEST['subv'];
$currSubv2 = $_REQUEST['subv2'];
$currBuild = $_REQUEST['build'];

if ($currVers == null || $currBuild == null) {
    echo json_encode(geterror("400", "Manca qualche parametro"));
    exit(1);
}

//INIT CONNECTION   
$file = file_get_contents("../secure/credentials.json");
$json = json_decode($file, true);
$user = $json["user"];
$passwd = $json["password"];
require_once("../secure/Connection.php");
$connessione = new Connection("localhost", $user, $passwd, "App");
$connessione->connect();

//GET MOST RECENT VERSION
$query = "SELECT version, sub_version, sub_version2, build, hotfix FROM Versions ORDER BY id DESC LIMIT 1";
$stmt = $connessione->conn->prepare($query);
$stmt->execute();

$up_vers = null;
$up_sub = null;
$up_sub2 = null;
$up_build = null;
$hotfix = null;
$stmt->bind_result($up_vers, $up_sub, $up_sub2, $up_build, $hotfix);
$stmt->fetch();
$stmt->close();
if ($up_build == null || $up_vers == null) {
    echo json_encode(geterror("400", "Errore mentre ottenevo le versioni"));
    exit(1);
}






if ($up_vers > $currVers) {
	goto update;
}

if ($up_vers == $currVers) {
	if ($up_sub > $currSubv) {
		goto update;
	} else if ($up_sub == $currSubv) {
		if ($up_sub2 > $currSubv2) {
			goto update;
		} else if ($up_sub2 == $currSubv2) {
			if ($up_build > $currBuild) {
				goto update;
			} else if ($up_build == $currBuild) {
				goto discard;
			}
		}
	}
}






update:

if ($up_sub2 == null) { $up_sub2 = 0; }
if ($up_sub == null) { $up_sub = 0; }

$newVers = $up_vers.".".$up_sub.".".$up_sub2;

$hotfix = ($hotfix == 1) ? True : False;

$value["code"] = "1";
$value["message"] = "Nuova versione disponibile";
$value["update"] = array(
	"newVersionAvailable" => True,
	"version" => strval($newVers),
	"build" => strval($up_build),
	"hotfix" => $hotfix,
);
echo json_encode($value);
$connessione->disconnect();

exit(0);




discard:


$value["code"] = "0";
$value["message"] = "Nessuna versione disponibile. L'ultima versione è: v".strval($up_vers)." (".strval($up_build).")";
$value["update"] = array(
	"newVersionAvailable" => False,
);
$connessione->disconnect();
echo json_encode($value);
exit(1);





?>

<?php

function geterror($code, $msg) {
    $value["code"] = $code;
    $value["message"] = $msg;
    return $value;
}

?>