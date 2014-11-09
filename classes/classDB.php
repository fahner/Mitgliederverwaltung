<?php

class classDb {
    
  //stellt die Datenbankverbindung her
  static function connect(){
    try {
      $dbh = new PDO("mysql:host=localhost;dbname=mitgliederverwaltung", "testuser", "testuser");
    } 
    catch (PDOException $e) {
      echo 'Connection failed: ' . $e->getMessage();
    }
    $dbh->query("SET NAMES 'utf8_unicode_ci'");
    return $dbh;
  }

}
?>