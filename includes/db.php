<?php
require_once 'security.php';
$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP password
$dbname = 'dajot_poultry';

$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create DB if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    $conn->select_db($dbname);
} else {
    die("Error creating database: " . $conn->error);
}
?>
