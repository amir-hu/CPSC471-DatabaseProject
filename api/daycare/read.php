<?php

/*
// Headers
header('Access-Control-Allow-Origin: *'); // 
header('Content-Type: application/json'); // Accepts json

include_once '../../config/Database.php'; // Bring in database
include_once '../../models/Daycare.php'; // Bring in Daycare model

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();


// Turn to JSON & output
echo "yooo";

 */

$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../../config/Database.php'); // Bring in database	
$database = new Database();
$db = $database->connect();
switch($requestMethod) {
	case 'POST':
		echo 'hahaha';
		break;
	default:
		header("HTTP/1.0 405 Method Not Allowed");
		echo 'matata';
		break;
}

?>