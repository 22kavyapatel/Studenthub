
<?php

$host = "localhost";
$username = "root";
$password = "";
$dbname = "studenthub";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log($conn->connect_error);
    http_response_code(500);
    exit("Database connection failed.");
}

if (!$conn->set_charset("utf8mb4")) {
    error_log($conn->error);
    http_response_code(500);
    exit("Database configuration failed.");
}

?>
