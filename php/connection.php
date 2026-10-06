<?php

$conn = new mysqli("localhost", "root", "", "studenthub");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

?>