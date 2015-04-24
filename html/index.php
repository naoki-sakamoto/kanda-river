<?php
global $directory_access;
$directory_access = "/";

//print getcwd()."<br>";
chdir("../bin");
//print getcwd()."<br>";
require_once("app.php");
?>