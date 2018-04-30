<?php
ob_start();
session_start();
  require_once('includes/load.php');
  if(!$session->logout()) {redirect("index.php");}
?>
