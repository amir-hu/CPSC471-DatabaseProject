<?php

// Required HTTP headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/Database.php'; // Bring in database

// Check if any paramters were passed and return that.
$childName = isset($_GET['ChildName']) ? $_GET['ChildName'] : '';
$familyName = isset($_GET['FamilyName']) ? $_GET['FamilyName'] : '';
$adminEmpId = isset($_GET['SubmittedById']) ? $_GET['SubmittedById'] : '';

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();

// SQL statement to call the stored proc
// Positional paramaters. Act as placeholders.
$sql = 'CALL AddToWaitlist(:childName, :familyName, :adminEmpId)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data
// Remove html characters and strip any tags
$childName = htmlspecialchars(strip_tags($childName)); 
$familyName = htmlspecialchars(strip_tags($familyName)); 
$adminEmpId = htmlspecialchars(strip_tags($adminEmpId)); 

// Bind data
$stmt->bindParam(':childName', $childName);
$stmt->bindParam(':familyName', $familyName);
$stmt->bindParam(':adminEmpId', $adminEmpId);

// Validate request:

// Check if the data is empty
if (empty($childName) || empty($familyName) || empty($adminEmpId)) {

    // set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to add to waitlist. Data is incomplete.';

// Make sure that the input data types (field type, length, etc.) matches model
}else if (strlen($childName) > 30 && strlen($familyName) > 30 && strlen($adminEmpId) > 11) {

    // set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to add to waitlist. Data does not match the defined model.';

}else {

    // Execute stored procedure
    if ($stmt->execute()){
        // set response code - 201 created
        http_response_code(201);
    
        echo "Child added to waitlist";
    }
    // if unable to insert into table, tell the user
    else {
        // set response code - 503 service unavailable
        http_response_code(503);
        echo "Unable to add child to waitlist";

        // Print error if something goes wrong.
        printf("Error: %s \n",$stmt->error);
    }
}

?>