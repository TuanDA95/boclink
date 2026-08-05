<?php

$servername = "localhost";
$username = "binhbun";
$password = "binhbun";
$dbname = "keygmvdb";

$conn = mysqli_connect($servername,$username,$password,$dbname);

if(!$conn) {

die(" PROBLEM WITH CONNECTION : " . mysqli_connect_error());

}
  
?>