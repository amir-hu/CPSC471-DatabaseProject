<?php

// This is for debugging and testing code. Shows all errors and warnings
// So we don't have to go through the logs.
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

class Database {

    private $servername = 'localhost';
    private $username = 'root';
    private $password = 'root';
    private $database;
    private $connection;

    // REFRENCE: https://www.php.net/manual/en/features.http-auth.php#73386
    public function authenticate($clearance) {

        // Accounts that would work with the endpoints. Not all have the same clearance level.
        $validAccounts = array ("A" => array("Hakuna","low"), "Metroid" => array("Prime","med"), "Erin" => array("Matata","high"));
        $valid_users = array_keys($validAccounts);

        $user = $_SERVER['PHP_AUTH_USER']; // Username that you pass in.
        $pass = $_SERVER['PHP_AUTH_PW']; // Password that you pass in.

        $validated = (in_array($user, $valid_users)) && ($pass == $validAccounts[$user][0]);

        if (!$validated) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            // Set response code - 401 Unauthorized
            http_response_code(401);
            $message = array ("Messsage" => "Username or password is incorrect.");
            echo json_encode($message);
            die ();
        }

        $this->checkClearance( ($validAccounts[$user][1]), $clearance );
    }

    public function checkClearance($userClearanceLevel, $clearanceLevelRequired){

        if ( ($clearanceLevelRequired == "med" && $userClearanceLevel == "low") || ($clearanceLevelRequired == "high" && $userClearanceLevel != "high") ) {
            // Set response code - 401 Unauthorized
            http_response_code(401);
            $message = array ("Messsage" => "Authorization not high enough.");
            echo json_encode($message);
            die();
        }
    }

    public function connect() {

        // Navigate to the current directory and open sql script. (C:\AppServ\www\CPSC471-DatabaseProject\config)
        // Get the database name from the sql script.
        // Reads the first line which will contain the database name.
        $f = fopen(__DIR__ . "/database.sql", "r");
        $string = trim(fgets($f));
        fclose($f);

        $pieces = explode(' ', $string);
        $this->database = array_pop($pieces);
        $this->database = substr($this->database, 0, -1); // Takes everything but the last char. Remove ; from the string.

        $this->connection = null;

        try {

            // Create connection
            $this->connection = new PDO('mysql:host=' . $this->servername . ';dbname=' . $this->database, $this->username, $this->password);
            // Set error mode. Get exception when we create queries in case something goes wrong
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            //$message = array('Message' => 'Successfully connected to the database.');
            //echo json_encode($message);

        }
        catch(PDOException $exception) {

            $message = array('Message' => 'Connection Error: ' . $exception->getMessage());
            echo json_encode($message);
        }

        return $this->connection;
    }
}

// testing
//$a = new Database();
//$a->connect();

?>

