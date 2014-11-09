<?php

session_start();

require_once "classes/classDB.php";

function printPage() {
  $db = classDB::connect();
  
  switch($_GET["action"]) {
    case "createPDF":
      createPDF($db);
      break;
    case "deactivate":
      deactivateMitglied($_GET["id"]);
      break;
    case "activate":
      activateMitglied($_GET["id"]);
      break;
    case "switchaktiv":
      switchAktiv();
      break;
  }
  
  $ergebnis = $db->prepare("DESCRIBE mitglieder");
  $ergebnis->execute();
  $ergebnis2 = $db->prepare("DESCRIBE staedte");
  $ergebnis2->execute();
  $columns = array_merge($ergebnis->fetchAll(PDO::FETCH_COLUMN), $ergebnis2->fetchAll(PDO::FETCH_COLUMN));
  
  $anfrage = "SELECT * FROM mitglieder, staedte WHERE mitglieder.plz = staedte.plz AND aktiv=?";
  $execarray = array(getAktiv());
  
  $sort = $_GET["sort"];
  if(in_array(end(explode(".",$sort)), $columns)) {
    $anfrage .= " ORDER BY ".$sort;
  }
  $dir = $_GET["dir"];
  if(in_array($dir, array("asc", "desc"))) {
    $anfrage .= " ".$dir;
  }
  $ergebnis = $db->prepare($anfrage);
  $ergebnis->execute($execarray);
  
  echo "<h2>";
  if(!getAktiv()) echo "Inaktive ";
  echo "Mitglieder</h2>";
  echo "<table border='2'>";
  echo "<tr>";
  echo "<th> edit </th>";
  echo "<th> lfd </th>";
  echo "<th> <a title='Sortieren' href='?site=mitglieder&sort=name&amp;dir=".sortDir($dir)."'> Name </a> </th>";
  echo "<th> <a title='Sortieren' href='?site=mitglieder&sort=vorname&amp;dir=".sortDir($dir)."'> Vorname </a> </th>";
  echo "<th> <a title='Sortieren' href='?site=mitglieder&sort=gebdatum&amp;dir=".sortDir($dir)."'> Geburtsdatum </a> </th>";
  echo "<th> <a title='Sortieren' href='?site=mitglieder&sort=strasse&amp;dir=".sortDir($dir)."'> Stra&szlig;e </a> </th>";
  echo "<th> <a title='Sortieren' href='?site=mitglieder&sort=hausnummer&amp;dir=".sortDir($dir)."'> Hausnummer </a> </th>";
  echo "<th> <a title='Sortieren' href='?site=mitglieder&sort=mitglieder.plz&amp;dir=".sortDir($dir)."'> PLZ </a> </th>";
  echo "<th> <a title='Sortieren' href='?site=mitglieder&sort=ort&amp;dir=".sortDir($dir)."'> Ort </a> </th>";
  echo "<th> <a title='Sortieren' href='?site=mitglieder&sort=telefon&amp;dir=".sortDir($dir)."'> Telefon </a> </th>";
  echo "<th> <a title='Sortieren' href='?site=mitglieder&sort=handy&amp;dir=".sortDir($dir)."'> Handy </a> </th>";
  echo "<th> <a title='Sortieren' href='?site=mitglieder&sort=email&amp;dir=".sortDir($dir)."'> E-Mail </a> </th>";
  echo "</tr>";
  
  $counter=1;
  foreach($ergebnis as $row) {
    echo "<tr>";
    echo "<td> 
            <a title='Bearbeiten' href='?site=mitglied_eintragen&action=edit&amp;id=".$row["mitglID"]."'><img alt='bearbeiten' class='button' src='images/edit.png' /></a> ";  
    if(getAktiv()) echo "<a title='Austritt' href='?site=mitglieder&action=deactivate&amp;id=".$row["mitglID"]."'><img alt='austreten' class='button' src='images/cancel.png' /></a> ";
    else       echo "<a title='Wieder aktivieren' href='?site=mitglieder&action=activate&amp;id=".$row["mitglID"]."'><img alt='wieder aktivieren' class='button' src='images/apply.png' /></a> ";
    echo "</td>";
    echo "<td> ".$counter++."</td>";
    echo "<td> ".$row['name']." </td>";
    echo "<td> ".$row['vorname']." </td>";
    echo "<td> ".date('d.m.Y',strtotime($row['gebdatum']))."</td>";
    echo "<td> ".$row['strasse']." </td>";
    echo "<td> ".$row['hausnummer']." </td>";
    echo "<td> ".$row['plz']." </td>";
    echo "<td> ".$row['ort']." </td>";
    echo "<td> ".$row['telefon']." </td>";
    echo "<td> ".$row['handy']." </td>";
    echo "<td> ".$row['email']." </td>";
    echo "</tr>";
  }
  echo "</table>";
}

function printFunktionen() {
  echo "<h2>Mitgliederverwaltung</h2>";
  echo "<ul>";
  echo "<li><a href='?site=mitglied_eintragen&action=insert'>Neues Mitglied eintragen</a></li>";
  echo "<li><a href='?site=mitglieder&action=switchaktiv'>";
  if($_GET["action"]=="switchaktiv") {
    if(getAktiv()) echo "Aktive ";
    else  echo "Inaktive ";
  }
  else {
    if(getAktiv()) echo "Inaktive ";
    else  echo "Aktive ";
  }
  echo "Mitglieder anzeigen</a></li>";
  echo "<li><a href='?site=mitglieder&action=createPDF'>PDF erzeugen</a></li>";
  echo "</ul>";
    
}

function sortDir($dir) {
  if ($dir=="asc")  return "desc";
  else              return "asc";
}

function getAktiv() {
  if (!isset($_SESSION["aktiv"]))  $_SESSION["aktiv"] = 1;
  return $_SESSION["aktiv"];
}

function switchAktiv() {
  if($_SESSION["aktiv"]) {
    $_SESSION["aktiv"] = 0;
  }
  else {
    $_SESSION["aktiv"] = 1;
  }
}

function deactivateMitglied($id) {
  $db = classDB::connect();
  $sql = "UPDATE mitglieder SET aktiv='0' WHERE mitglID=?";
  $ergebnis = $db->prepare($sql);
  $ergebnis->execute(array($id));
}

function activateMitglied($id) {
  $db = classDB::connect();
  $sql = "UPDATE mitglieder SET aktiv='1' WHERE mitglID=?";
  $ergebnis = $db->prepare($sql);
  $ergebnis->execute(array($id));
}

function createPDF($db) {
  require('fpdf/fpdf.php');
    
  class PDF extends FPDF {
  //Kopfzeile
    function Header() {
      $this->Image('images/sck-logo.jpg',10,8,20);
      $this->SetFont('Arial','B',12);
      $this->Cell(25);
      $this->Cell(30,6,'SC 1910 Käfertal e.V. - Abt. Boule');
      $this->Ln(8);
      $this->SetFont('Arial','B',16);
      $this->Cell(25);
      $w = $this->GetStringWidth('Mitgliederliste');
      $this->Cell($w+2,5,'Mitgliederliste');
      $this->SetFont('Arial','B',12);
      $this->Cell(0,5,'(Stand: '.date('d.m.Y').')');
      $this->Ln(15);
    }
  
    //Fusszeile
    function Footer() {
      $this->SetY(-15);
      $this->SetFont('Arial','I',8);
      $this->Cell(0,10,'Seite '.$this->PageNo().' von {nb}',0,0,'C');
    }
  }
  
  $pdf=new PDF();
  $pdf->AliasNbPages();
  $pdf->AddPage("L");
  
  $anfrage = "SELECT name, vorname, DATE_FORMAT(gebdatum,'%d.%m.%Y'), CONCAT_WS(' ', strasse, hausnummer) AS strasse, mitglieder.plz, ort, telefon, handy, email 
              FROM mitglieder, staedte 
              WHERE mitglieder.plz = staedte.plz AND aktiv='1'
              ORDER BY name, vorname";
  //$ergebnis = $db->query($anfrage);
  $db->query($anfrage);
  $w = array(25,25,28,40,15,24,30,30,60);
  $pdf->SetFillColor(0,0,180);
  $pdf->SetTextColor(255);
  $pdf->SetFont('Arial','B',10);
  $header = array("Name", "Vorname", "Geburtsdatum", "Strasse", "PLZ", "Ort", "Telefon", "Handy", "E-Mail");
  for($i=0;$i<count($header);$i++)
    $pdf->Cell($w[$i],7,$header[$i],1,0,'L',1);
  $pdf->Ln();
  $pdf->SetFillColor(220,240,255);
  $pdf->SetTextColor(0);
  $pdf->SetFont('Arial','',10);
  $fill = 1;
  foreach($db->query($anfrage) as $row) {
    for($i=0; $i<count($row); $i++) {
      $pdf->Cell($w[$i],5,$row[$i],1,0,'L',$fill);
    }
    $pdf->Ln();
    $fill = !$fill;
  }
  //$pdf->Output("mitgliederliste.pdf", "D");
  $pdf->Output("mitgliederliste.pdf", "I");
}

?>