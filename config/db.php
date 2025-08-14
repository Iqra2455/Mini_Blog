<?php

$host = "localhost";
$user = "root";
$pass = "";
$db_name = "mini_blog";


$conn = new mysqli($host , $user ,$pass , $db_name);
if($conn->connect_error){
    die("connection falied" .$conn->connect_errno);
}

?>