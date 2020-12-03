# Daycare Management System

CPSC 471 project for the Data Base Management Systems course at the University of Calgary

## Credits
* Ahmed Hasan
* Erin Danielle Paslawski
* Amir Hussain

## Download and Install
* A web server
* MySQL database
* PHP

## Quick Set Up
1. Download and place the project file inside the web folder (www) of the web server
1. Navigate to http://localhost/phpmyadmin/ and login
1. Change password to 'root'
1. Navigate to http://localhost/CPSC471-DatabaseProject/config/Install.php to setup the database and tables

## Changing The Database Configuration
* [Install.php](config/Install.php) runs a SQL script to create the database, tables and the stored procedures
* The name of the database created with the script is called ``childDaycare``
* The configuration assumes that the username and password to phpMyAdmin is ``root``
* This configuration can be changed to your own preference by following these steps:
1. Open [Database.php](config/Database.php) to change ``servername``, ``username``, or ``password``
1. To change the database name, open [database.sql](config/database.sql) and change ``childDaycare`` in lines 1, 3 and 5
1. Once those changes are made, navigate to http://localhost/CPSC471-DatabaseProject/config/Install.php
