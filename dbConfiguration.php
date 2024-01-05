<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
$conn=mysqli_connect("localhost","root","f0rg0t","NVGroup");
mysqli_set_charset($conn, 'utf8');
?>