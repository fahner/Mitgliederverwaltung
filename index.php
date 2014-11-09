<?php

session_start();

include_once 'classes/classSite.php';

$page = new classSite();
$page->printHeader();
$getsite = $_GET["site"];
$sites = array("mitglieder", "lizenzen", "mitglied_eintragen", "login");
if(!in_array($getsite, $sites)) $getsite="includes/mitglieder.php";
else $getsite = "includes/".$getsite.".php";
if(isset($_SESSION["username"])) {
  $page->printBody($getsite);
}
else {
  $page->printBody("includes/login.php");
}


?>