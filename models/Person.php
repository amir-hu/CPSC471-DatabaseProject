<?php

class Person {

    // Connection instance
    private $connection;

    // Database table name
    private $table = 'Person';

    // Table attributes
    public $sin;
    public $firstName;
    public $lastName;
    public $gender;
    public $addrUnitNum;
    public $addrStreet;
    public $addrCity;
    public $addrPostalCode;
    public $startDay;
    public $startMonth;
    public $startYear;

    // Constructor with database connection
    public function __construct($dbConnection) {
        $this->connection = $dbConnection;
    }

    // CRUD Methods:

    public function create(){

    }

    public function read(){

    }

    public function update(){

    }

    public function delete(){

    }

}

?>