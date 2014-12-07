<?php

session_start();
require_once "classes/classDB.php";

function printPage() {
  switch($_GET["action"]) {
    case "insert":
      showMitgliedsformular();
      break;
    case "edit":
      showMitgliedsformular($_GET["id"]);
      break;
  }
  
  switch($_POST["action"]) {
    case "insert":
      $mitglied = getMitgliedFromPOST();
      insertDB($mitglied);
      break;
    case "edit":
      $mitglied = getMitgliedFromPOST($_POST["mitglID"]);
      editDB($mitglied);
      break;
    case "insertPLZ":
      insertPLZ($_POST["plz"],$_POST["ort"]);
      break;
  }
}

// ======== METHODEN ========

function showMitgliedsformular($index=false) {

  if(!$index) {
    echo "<h1>Neues Mitglied eintragen</h1>";
  }
  else {
    echo "<h1>Mitglied bearbeiten</h1>";
    $mitglied = getMitgliedFromDB($index);
  }

  echo "<form action='?site=mitglied_eintragen' method='post'>";
  echo "<input type='hidden' name='mitglID' value ='$index'/>";
  echo "Vorname: <br />";
  echo "<input type=\"text\" name=\"vorname\" size=\"30\" value='".$mitglied["vorname"]."'> <br /><br />";
  echo "Nachname: <br />";
  echo "<input type=\"text\" name=\"name\" size=\"30\" value='".$mitglied["name"]."'> <br /><br />";
  echo "Geburtsdatum (bitte im Format TT.MM.YYYY): <br />";
  if($index and strtotime($mitglied["gebdatum"])) $gebvalue = date('d.m.Y',strtotime($mitglied["gebdatum"]));
  else       $gebvalue = "";
  echo "<input type=\"date\" name=\"geburtsdatum\" size='10' value='$gebvalue'> <br /><br />";
  echo "Stra&szlig;e: <br />";
  echo "<input type=\"text\" name=\"strasse\" size=\"30\" value='".$mitglied["strasse"]."'> <br /><br />";
  echo "Hausnummer: <br />";
  echo "<input type=\"text\" name=\"hausnummer\" size=\"30\" value='".$mitglied["hausnummer"]."'> <br /><br />";
  echo "PLZ: <br />";
  echo "<input type=\"number\" name=\"plz\" size=\"5\" value='".$mitglied["plz"]."'> <br /><br />";
  echo "Telefon: <br />";
  echo "<input type=\"tel\" name=\"telefon\" size=\"30\" value='".$mitglied["telefon"]."'> <br /><br />";
  echo "Handy: <br />";
  echo "<input type=\"tel\" name=\"handy\" size=\"30\" value='".$mitglied["handy"]."'> <br /><br />";
  echo "E-Mail: <br />";
  echo "<input type=\"email\" name=\"email\" size=\"30\" value='".$mitglied["email"]."'> <br /><br />";
  if ($index) {
		echo "<input type='hidden' name='action' value ='edit'/>";
    echo "<input type=\"submit\" value=\"Mitglied bearbeiten\" />";
	}
	else {
		echo "<input type='hidden' name='action' value ='insert'/>";
    echo "<input type=\"submit\" value=\"Mitglied eintragen\" />";
	}
  echo "</form>";
}

function getMitgliedFromDB($index) {
 
  $mitglied = array();
  $db = classDB::connect();
  $sql = "SELECT * FROM mitglieder WHERE mitglID=?";
  $ergebnis = $db->prepare($sql);
  $ergebnis->execute(array($index));
  
  foreach($ergebnis as $row) {
    $mitglied["vorname"] = $row["vorname"];
    $mitglied["name"] = $row["name"];
    $mitglied["gebdatum"] = $row["gebdatum"];
    $mitglied["strasse"] = $row["strasse"];
    $mitglied["hausnummer"] = $row["hausnummer"];
    $mitglied["plz"] = $row["plz"];
    $mitglied["telefon"] = $row["telefon"];
    $mitglied["handy"] = $row["handy"];
    $mitglied["email"] = $row["email"];
    return $mitglied;
  }
}

function getMitgliedFromPOST($index=false) {
  $mitglied = array();
  if ($index) $mitglied["mitglID"]=$index;
  $mitglied["vorname"] = $_POST["vorname"];
  $mitglied["name"] = $_POST["name"];
  $geb = explode(".",$_POST["geburtsdatum"]);
  $mitglied["gebdatum"]  = $geb[2]."-".$geb[1]."-".$geb[0];
  $mitglied["strasse"] = $_POST["strasse"];
  $mitglied["hausnummer"] = $_POST["hausnummer"];
  $mitglied["plz"] = $_POST["plz"];
  $mitglied["telefon"] = $_POST["telefon"];
  $mitglied["handy"] = $_POST["handy"];
  $mitglied["email"] = $_POST["email"];
  return $mitglied;
}

function insertDB($mitglied) {

  $db = classDB::connect();
  $sql = "INSERT INTO mitglieder (vorname, name, gebdatum, strasse, hausnummer, plz, telefon, handy, email) VALUES (?,?,?,?, ?, ?, ?, ?, ?)";
  $ergebnis = $db->prepare($sql);
  $ergebnis->bindParam(1, $mitglied["vorname"]);
  $ergebnis->bindParam(2, $mitglied["name"]);
  $ergebnis->bindParam(3, $mitglied["gebdatum"]);
  $ergebnis->bindParam(4, $mitglied["strasse"]);
  $ergebnis->bindParam(5, $mitglied["hausnummer"]);
  $ergebnis->bindParam(6, $mitglied["plz"]);
  $ergebnis->bindParam(7, $mitglied["telefon"]);
  $ergebnis->bindParam(8, $mitglied["handy"]);
  $ergebnis->bindParam(9, $mitglied["email"]);
  $ergebnis->execute();
  
  echo "Mitglied erfolgreich eingetragen. <br /> ";
  if (checkPLZ($mitglied["plz"])) {
    echo "Zur&uuml;ck zur <a href='?site=mitglieder'>Mitgliederverwaltung</a>";
  }
}

function editDB($mitglied) {
  $db = classDB::connect();
  $sql = "UPDATE mitglieder 
          SET vorname = ?,
                  name = ?,
                  gebdatum = ?,
                  strasse = ?,
                  hausnummer = ?,
                  plz = ?,
                  telefon = ?,
                  handy = ?,
                  email = ? 
          WHERE mitglID = ?";
  $ergebnis = $db->prepare($sql);
  $ergebnis->bindParam(1, $mitglied["vorname"]);
  $ergebnis->bindParam(2, $mitglied["name"]);
  $ergebnis->bindParam(3, $mitglied["gebdatum"]);
  $ergebnis->bindParam(4, $mitglied["strasse"]);
  $ergebnis->bindParam(5, $mitglied["hausnummer"]);
  $ergebnis->bindParam(6, $mitglied["plz"]);
  $ergebnis->bindParam(7, $mitglied["telefon"]);
  $ergebnis->bindParam(8, $mitglied["handy"]);
  $ergebnis->bindParam(9, $mitglied["email"]);
  $ergebnis->bindParam(10, $mitglied["mitglID"]);
  $ergebnis->execute();
  echo "Mitglied erfolgreich bearbeitet. <br /> ";
  if(checkPLZ($mitglied["plz"])) {
    echo "Zur&uuml;ck zur <a href='?site=mitglieder'>Mitgliederverwaltung</a>";
  }
}

function checkPLZ($plz) {
  if(!$plz) return true;
  $db = classDB::connect();
  $sql = "SELECT * FROM staedte WHERE plz = ?";
  $ergebnis = $db->prepare($sql);
  $ergebnis->execute(array($plz));
    
  if($ergebnis->rowCount()==0) {
    echo "Die angegebene Postleitzahl ".$plz." ist noch nicht in der Datenbank enthalten. Bitte zugeh&ouml;rige Stadt eingeben:";
    echo "<form action='?site=mitglied_eintragen' method='post'>";
    echo "Ort: ";
    echo "<input type=\"text\" name=\"ort\" size=\"30\"> <br /><br />";
    echo "<input type='hidden' name='plz' value ='$plz'/>";
    echo "<input type='hidden' name='action' value ='insertPLZ'/>";
    echo "<input type=\"submit\" value=\"PLZ eintragen\" />";
    echo "</form>";
    return false;
  }
  else {
    return true;
  }
}

function insertPLZ($plz, $ort) {
  $db = classDB::connect();
  $sql = "INSERT INTO staedte VALUES (?, ?)";
  $ergebnis = $db->prepare($sql);
  $ergebnis->execute(array($plz, $ort));
  echo "Stadt erfolgreich ins Postleitzahlregister eingetragen. <br />";  
}

function printFunktionen() {

}


?>