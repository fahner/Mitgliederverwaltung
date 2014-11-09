<?php

session_start();

require_once "classes/classDB.php";

function printPage() {
  switch($_POST["action"]) {
    case "login":
      doLogin($_POST["username"], md5($_POST["password"]));
      break;
    default:
      printLoginForm();
      break;
  }
  
  switch($_GET["action"]) {
    case "logout":
      doLogout();
      break;
  }
}

function printLoginForm() {
  echo "<form action='?site=login' method='post'>";
  echo "Dein Username:<br />";
  echo "<input type='text' size='24' maxlength='50' name='username'><br /><br />";
  
  echo "Dein Passwort:<br />";
  echo "<input type='password' size='24' maxlength='50' name='password'><br /><br />";
  echo "<input type='submit' value='Login'>";
  echo "<input type='hidden' name='action' value ='login'/>";
  echo "</form>";
}

function doLogin($username, $md5password) {
  $db = classDB::connect();
  $sql = "SELECT * FROM user WHERE username LIKE ? LIMIT 1";
  $ergebnis = $db->prepare($sql);
  $ergebnis->execute(array($username));
  $row = $ergebnis->fetch(PDO::FETCH_ASSOC);
  if($row["passwort"] == $md5password) {
    $_SESSION["username"] = $row["username"];
    $_SESSION["vorname"] = $row["vorname"];
    $_SESSION["name"] = $row["name"];
    echo "<p>Sei gegr&uuml;&szlig;t ".$row["vorname"]."!</p>";
  }
  else {
    echo "Benutzername und/oder Passwort waren falsch. <br> <a href=?site=login>Neuer Versuch</a>";
  }    
}

function doLogout() {
  session_destroy();
}

function printFunktionen() {

}

?>