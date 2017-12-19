<?php

require_once("../secure/Connection.php");
$connessione = new Connection("localhost", "pi", "", "App");
$connessione->connect();


$query = "SELECT Formatore FROM Formatori";
$stmt = $connessione->conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$array_form = null;
while ($row = $result->fetch_array(MYSQLI_NUM)) {
  if (!empty($row)) {
    $array_form[] = $row[0];
  }
}
$stmt->close();
print_r($array_form);

// foreach ($array_form as $formatore) {
//   $salt = createSalt();
//   $token = generateToken();
//   $password = hash("sha512", ($formatore.$salt));
//
//   $query = "UPDATE Formatori SET password = ?, salt = ?, token = ? WHERE Formatore = ?";
//   $stmt = $connessione->conn->prepare($query);
//   $stmt->bind_param("ssss", $password, $salt, $token, $formatore);
//   $stmt->execute();
//   $stmt->close();
// }



$connessione->disconnect();

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
    for ($i = 0; $i < 9; $i++) {
        $tempChar = $lettere[rand(0, strlen($lettere) - 1)];
        $salt .= $tempChar;
    }
    return $salt;
}

?>
