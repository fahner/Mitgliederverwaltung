<?php

class classSite {

  public function printHeader() {
    if($_GET["action"] != "createPDF") {
      echo "<head>";
      echo "<link rel='stylesheet' type='text/css' href='css/main.css'>";
      echo "</head>";
    }
  }
  
  public function printBody($site) {
    include($site);
    if($_GET["action"] != "createPDF") {
      echo "<body>";
      
      echo "<div id='header'>";
      echo "<div class='brand'> Bouleverwaltung </div>";
      $this->printMenu();
      echo "</div>";
      
      echo "<div id='sidebar'>";
      $this->printFunktionen();
      printFunktionen();
      echo "</div>";
      
      echo "<div id='body'>";
    }
    printPage();
    echo "</div>";   
    
    echo "<div id='footer'>";

    echo "</div>";

    echo "</body>";
  }
  
  private function printMenu() {
    echo "<div class='navbar'>";
    echo "<ul>";
    echo "<li><a href=?site=mitglieder> Mitglieder </a></li>";
    echo "<li><a href=?site=lizenzen> Lizenzen </a></li>";
    echo "<li><a> Kasse </a></li>";
    echo "<li><a> Sonstiges </a></li>";
    echo "</ul>";
    echo "</div>";
  }
  
  private function printFunktionen() {
    if(isset($_SESSION["username"]) && $_GET["action"]!="logout") {
      echo "<h2>Hallo ".$_SESSION["vorname"]."</h2>";
      echo "<ul>";
      echo "<li><a href='?site=login&action=logout'>ausloggen</a></li>";
      echo "</ul>";
    }
  }

}
?>