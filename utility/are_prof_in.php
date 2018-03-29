<?php


$file = file_get_contents("areIn.json");
$json = json_decode($file, true);

if ($json["areIn"] == 1) {
	echo json_encode(get_response("1", "Sono daccordo!!!"));
	exit();
} else {
	echo json_encode(get_response("0", "Non sono ancora daccordo"));
	exit();
}



?>
<?php
function get_response($code, $msg) {
	$value["code"] = $code;
	$value["message"] = $msg;
	return $value;
}

?>