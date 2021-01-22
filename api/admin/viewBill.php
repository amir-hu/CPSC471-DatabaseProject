<?php
// Required HTTP headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/Database.php'; // Bring in database

if ($_SERVER["REQUEST_METHOD"] != "GET") {
    // Set response code - 405 Method not allowed
    http_response_code(405);
    $message = array('Message' => 'Request method ' . $_SERVER["REQUEST_METHOD"] . ' not allowed.');
    echo json_encode($message);
    exit();
}

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();
$database->authenticate("low");

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL getBillIds()';
// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

$limit = 100;


    // Execute stored procedure
    try {
        
        $stmt->execute();
        
        // Set response code - 200 OK
        http_response_code(200);
        
        // Returns all rows as an object
        $numOfRecords = $stmt->rowCount();
        $limit = $numOfRecords;
        
        if ($numOfRecords == 0 || $limit <= 0) {
            $message = array('Message' => 'No Bills');
            echo json_encode($message);
        }
        else if ($numOfRecords >= $limit) {
            // Set response code - 200 ok
            
            for ($x = 0; $x < $limit; $x++) {
                // Returns all rows as an object
                $conditionRows = $stmt->fetch(PDO::FETCH_OBJ);
                
               
                // Turn to JSON & output
                echo json_encode($conditionRows) ;                
                echo "~";
            }
        }
        
        else 
        {
            // Returns all rows as an object
            $conditionRows = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            // Turn to JSON & output
            echo json_encode($conditionRows);
        }
        
        $stmt->closeCursor();
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        $message = array('Message' => "Unable to get Bill Ids. " . $exception->getMessage());
        echo json_encode($message);
    }

?>