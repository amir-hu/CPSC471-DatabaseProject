<?php

// Required HTTP headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/Database.php'; // Bring in database

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // Set response code - 405 Method not allowed
    http_response_code(405);
    echo 'Request method ' . $_SERVER["REQUEST_METHOD"] . ' not allowed';
    exit();
}

// Check if any paramters were passed and return that else return an empty string.
$childName = isset($_GET['ChildName']) ? $_GET['ChildName'] : '';
$familyName = isset($_GET['FamilyName']) ? $_GET['FamilyName'] : '';
$adminEmpId = isset($_GET['SubmittedById']) ? $_GET['SubmittedById'] : '';

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL AddToWaitlist(:childName, :familyName, :adminEmpId)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
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

    // Set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to add to waitlist. Data is incomplete.';

// Check data type
}else if (ctype_digit($childName) || ctype_digit($familyName) || !(is_numeric($adminEmpId)) ) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to add to waitlist. Data type is not correct.';

 // Make sure that the input length matches model
}else if (strlen($childName) > 30 || strlen($familyName) > 30 || strlen($adminEmpId) > 11) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to add to waitlist. Data does not match the defined model.';

}else {

    // Execute stored procedure
    try {
        $stmt->execute();
        // Set response code - 201 created
        http_response_code(201);

        echo "Child added to waitlist.";
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        echo "Unable to add child to waitlist. " . $exception->getMessage();
    }
}
?>