<?php

// This is for debugging and testing code. Shows all errors and warnings
// So we don't have to go through the logs.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Database {

    private $servername = 'localhost';
    private $username = 'root';
    private $password = 'root';
    private $database;
    private $connection;




    //REFRENCE: https://www.php.net/manual/en/features.http-auth.php#73386
    public function authenticate($clearance){
        $valid_passwords = array ("A" => array("Hakuna","low"),"Metroid" => array("Prime","med"),"Erin" => array("Matata","high"));
        $valid_users = array_keys($valid_passwords);

        $user = $_SERVER['PHP_AUTH_USER'];
        $pass = $_SERVER['PHP_AUTH_PW'];
        //print_r($valid_passwords[$user]);
        $validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user][0]);
   
        
        

        if (!$validated) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            http_response_code(401);
            $message = array ("messsage" => "Not authorized");
            echo json_encode($message);
            die ();
            }
        $data = new Database();
        $data->checkClearance( ($valid_passwords[$user][1]), $clearance);
    }
    

    
    public function checkClearance($give,$required){

        if($required=="med" && $give=="low" || $required=="high" && $give!="high"){
            $message = array ("messsage" => "authorization not high enough");
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

