<?php
session_start();

require_once "classes/classDB.php";

function printPage() {

  if($_GET["action"]!="createPDF") echo "<h1>Lizenzspielerverwaltung</h1>";

  switch($_POST["action"]) {
    case "insert":
      $lizenz = getLizenzspielerFromPOST();
      insertDB($lizenz);
      break;
    case "edit":
      $lizenz = getLizenzspielerFromPOST();
      editDB($lizenz);
      break;
    case "delete":
      $lizenz = getLizenzspielerFromPOST();
      deleteDB($lizenz);
      break;
  }

  switch($_GET["action"]) {
    case "insert":
      showFormular();
      break;
    case "edit":
      showFormular($_GET["id"], $_GET["jahr"]);
      break;
    case "delete":
      ensureDelete($_GET["id"], $_GET["jahr"]);
      break;
    case "createPDF":
      createPDF(getJahr());
      break;
    default:
      chooseJahr();
      showLizenzspieler($_GET["jahr"]);
      break;
  }
}

function printFunktionen() {    
      echo "<h2>Lizenzspielerverwaltung</h2>";
      echo "<ul>";
      echo "<li><a href='?site=lizenzen&action=insert'>Neuen Lizenzspieler hinzuf&uuml;gen</a></li>";
      echo "<li><a href='?site=lizenzen&action=createPDF&amp;jahr=".getJahr()."&amp;sort=".getSort()."'>PDF erzeugen</a></li>";
      echo "</ul>";
}

function showLizenzspieler($year) {
  if(!isset($year)) $year=date('Y');
  echo "<h2> Lizenzspieler $year </h2>";
  $db = classDB::connect();
  
  $ergebnis = $db->prepare("DESCRIBE lizenzen");
  $ergebnis->execute();
  $ergebnis2 = $db->prepare("DESCRIBE mitglieder");
  $ergebnis2->execute();
  $columns = array_merge($ergebnis->fetchAll(PDO::FETCH_COLUMN), $ergebnis2->fetchAll(PDO::FETCH_COLUMN));
  
  $sql = "SELECT lizNr, Name, Vorname
          FROM lizenzen, mitglieder
          WHERE lizenzen.mitglID = mitglieder.mitglID
          AND lizenzen.jahr = ?";
  $sort = $_GET["sort"];
  if(in_array(end(explode(".",$sort)), $columns)) {
    $sql .= " ORDER BY ".$sort;
  }
  $dir = $_GET["dir"];
  if(in_array($dir, array("asc", "desc"))) {
    $sql .= " ".$dir;
  }
  $ergebnis = $db->prepare($sql);
  $ergebnis->execute(array($year));
  
  $header = array("Lizenznummer", "Name", "Vorname", "Optionen");
  $sortNames = array("lizNr", "name", "vorname");
  echo "<table border='2'>";
  echo "<tr>";
  for($i=0; $i<count($header); $i++) {
    echo "<th>";
    if($i!=count($header)-1) echo "<a title='Sortieren' href='?site=lizenzen&sort=".$sortNames[$i]."&amp;dir=".sortDir($dir)."'>";
    echo $header[$i];
    if($i!=count($header)-1) echo "</a>";
    echo "</th>";
  }
  echo "</tr>";

  foreach($ergebnis as $row) {
    echo "<tr>";
    for($i=0; $i < count($row)/2; $i++) { // Halbierung, da auf row assoziativ und numerisch zugegriffen werden kann
      echo "<td>".$row[$i]."</td>";
    }
    echo "<td>"; 
    echo "<a title='Bearbeiten' href='?site=lizenzen&action=edit&amp;id=".$row["lizNr"]."&amp;jahr=$year'><img alt='bearbeiten' class='button' src='images/edit.png' /></a>"; 
    echo "<a title='L&ouml;schen' href='?site=lizenzen&action=delete&amp;id=".$row["lizNr"]."&amp;jahr=$year'><img alt='Lizenz l&ouml;schen' class='button' src='images/cancel.png' /></a> "; 
    echo "</td>";
    echo "</tr>";
  }
  echo "</table>";

}

function showFormular($index=false, $year=0) {
  if($year==0) $year=date('Y');
  if(!$index) {
      echo "<h2>Lizenzspieler hinzuf&uuml;gen </h2>";
  }
  else {
    echo "<h2>Lizenzspieler bearbeiten</h2>";
    $spieler = getLizenzspielerFromDB($index, $year);
  }
 
  if(!isset($_GET["jahr"])) {
    echo "<form action='' method='get'>";
    echo "<input type='hidden' name='site' value ='lizenzen' />";
    echo "<input type='hidden' name='action' value ='insert' />";
    echo "Jahr: <br />";
    echo "<input type='number' name='jahr' size='4' value='".$year."'> <br /><br />";
    echo "<input type='submit' value='Weiter' />";
    echo "</form>";
  }
  else {
    echo "<form action='?site=lizenzen' method='post'>";
    echo "Jahr: <br />";
    if(!$index) echo "<input type='number' name='jahr' size='4' value='".$_GET["jahr"]."' readonly> <br /><br />"; 
    else        echo "<input type='number' name='jahr' size='4' value='".$spieler["jahr"]."' readonly> <br /><br />"; 
    echo "Lizenznummer: <br />";
    echo "<input type='number' name='lizNr' size=\"3\" value='".$spieler["lizNr"]."'";
         if($index) echo "readonly";
    echo "> <br /><br />";
    
    echo "Mitglied: <br />";
    echo "<select name='mitglID' size='10'>";
    $db = classDB::connect();
    $sql = "SELECT mitglID, CONCAT_WS(', ', name, vorname) as name
            FROM mitglieder
            WHERE NOT EXISTS (Select * FROM lizenzen WHERE jahr=? AND mitglieder.mitglID = lizenzen.mitglID)
            ORDER BY name";
    $ergebnis = $db->prepare($sql);
    $ergebnis->execute(array($_GET["jahr"]));
    foreach($ergebnis as $row) {
      echo "<option value='".$row["mitglID"]."'>".$row["name"]."</option>";
    }
    echo "</select>";
    echo "<br /><br />";
    if(!$index) {
      echo "<input type='hidden' name='action' value ='insert' />";
      echo "<input type='submit' value='Lizenz hinzuf&uuml;gen' />";
    }
    else {
      echo "<input type='hidden' name='action' value ='edit' />";
      echo "<input type='submit' value='Lizenz bearbeiten' />";
    }
    echo "</form>";
  }
}

function getLizenzspielerFromDB($index, $year) {
  $lizenz = array();
  $db = classDB::connect();
  $sql = "SELECT *
          FROM lizenzen
          WHERE lizNr=? AND jahr=? LIMIT 1";
  $ergebnis = $db->prepare($sql);
  $ergebnis->execute(array($index, $year));
  return $ergebnis->fetch();
}

function getLizenzspielerFromPOST() {
  $lizenz = array();
  $lizenz["lizNr"]=$_POST["lizNr"];
  $lizenz["jahr"]=$_POST["jahr"];
  $lizenz["mitglID"]=$_POST["mitglID"];
  return $lizenz;
}

function insertDB($lizenz) {
  $db = classDB::connect();
  $sql = "INSERT INTO lizenzen (lizNr, jahr, mitglID)
          VALUES (?, ?, ?)";
  $ergebnis = $db->prepare($sql);
  $ergebnis->execute(array($lizenz["lizNr"], $lizenz["jahr"], $lizenz["mitglID"]));
}

function editDB($lizenz) {
  $db = classDB::connect();
  $sql = "UPDATE lizenzen
          SET mitglID=?
          WHERE lizNr=?
          AND jahr=?";
  $ergebnis = $db->prepare($sql);
  $ergebnis->execute(array($lizenz["mitglID"], $lizenz["lizNr"], $lizenz["jahr"]));
}

function chooseJahr() {
  echo "<p>Jahr ausw&auml;hlen:</p>";
  echo "<form action='' method='get'>";
  echo "<input type='hidden' name='site' value='lizenzen' />";
  echo "<select name='jahr' size='1'>";
  $db = classDB::connect();
  $sql = "SELECT DISTINCT jahr
          FROM lizenzen";
  $ergebnis = $db->query($sql);
  foreach($ergebnis as $row) {
    echo "<option value='".$row[0]."'";
    if($row[0]==$_GET["jahr"]) echo "selected";
    echo ">$row[0]</option>";
  }
  echo "</select>";
  echo "<input type='submit' value='Aktualisieren' />";
  echo "</form>";
}

function ensureDelete($index, $year) {
  echo "<h2>Lizenz l&ouml;schen</h2>";
  if(!isset($index) or !isset($year)) {
    echo "<p>Ung&uuml;ltige Eingabe.</p>";
  }
  else {
    $db = classDB::connect();
    $sql = "SELECT lizNr, jahr, CONCAT_WS(' ', vorname, name) as name
            FROM lizenzen, mitglieder
            WHERE lizNr=? AND jahr=?
            AND lizenzen.mitglID = mitglieder.mitglID";
    $ergebnis = $db->prepare($sql);
    $ergebnis->execute(array($index, $year));
    $row = $ergebnis->fetch();
    echo "<p>Lizenz Nr. ".$row['lizNr']." (".$row["name"].") aus dem Jahr ".$row["jahr"]." wirklich l&ouml;schen?</p>";
    echo "<form action='?site=lizenzen' method='post'>";
    echo "<input type='hidden' name='lizNr' value ='".$row['lizNr']."' />";
    echo "<input type='hidden' name='jahr' value ='".$row["jahr"]."' />";
    echo "<input type='hidden' name='action' value ='delete' />";
    echo "<input type='submit' value='L&ouml;schen best&auml;tigen' />";
    echo "</form>";
  }
}

function deleteDB($lizenz) {
  $db = classDB::connect();
  $sql = "DELETE FROM lizenzen
          WHERE jahr=? AND lizNr=?";
  $ergebnis = $db->prepare($sql);
  $ergebnis->execute(array($lizenz["jahr"], $lizenz["lizNr"]));
}

function sortDir($dir) {
  if ($dir=="asc")  return "desc";
  else              return "asc";
}

function createPDF($year) {
  require_once('classes/classPDF.php');
  $pdf=new PDF();
  $pdf->AliasNbPages();
  $pdf->setHeadertitle("Lizenzspieler ".$year);
  $pdf->AddPage();
  $header = array("Lizenznummer", "Name", "Vorname");
  $db = classDB::connect();
  $ergebnis = $db->prepare("DESCRIBE lizenzen");
  $ergebnis->execute();
  $ergebnis2 = $db->prepare("DESCRIBE mitglieder");
  $ergebnis2->execute();
  $columns = array_merge($ergebnis->fetchAll(PDO::FETCH_COLUMN), $ergebnis2->fetchAll(PDO::FETCH_COLUMN));
  $sql = "SELECT lizNr, name, vorname
          FROM lizenzen, mitglieder
          WHERE lizenzen.mitglID = mitglieder.mitglID
          AND jahr = ?";
  $sort = getSort();
  if(in_array(end(explode(".",$sort)), $columns)) {
    $sql .= " ORDER BY ".$sort;
  }

  $ergebnis = $db->prepare($sql);
  $ergebnis->execute(array($year));
  $width = array(30,35,35);
  $pdf->Table($header, $ergebnis, $width);
  //$pdf->Output("mitgliederliste.pdf", "D");
  $pdf->Output("mitgliederliste.pdf", "I");
}

function getJahr() {
  if(!isset($_GET["jahr"])) return date('Y');
  else                      return $_GET["jahr"];
}

function getSort() {
  if(!isset($_GET["sort"])) return "lizNr";
  else                      return $_GET["sort"];
}
?>