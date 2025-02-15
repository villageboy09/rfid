<?php

// Database connection settings
$servername = ""; // Replace with your server name
$username = ""; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = ""; // Replace with your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
