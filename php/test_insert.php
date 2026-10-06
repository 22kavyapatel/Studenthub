<?php

require_once "db.php";

$name = "Test Student";
$email = "test@gmail.com";
$mobile = "9999999999";
$course = "B.Tech IT";
$year = "2nd Year";

$sql = "INSERT INTO students
        (name, email, mobile, course, year)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $name,
    $email,
    $mobile,
    $course,
    $year
]);

echo "Student inserted successfully!";

?>