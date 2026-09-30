
<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo "Invalid request.";

    exit;
}


/* =========================
   GET FORM DATA
========================= */

$role = $_POST["role"] ?? "";

$name = $_POST["name"] ?? "";

$email = $_POST["email"] ?? "";

$mobile = $_POST["mobile"] ?? "";

$gender = $_POST["gender"] ?? "";

$password = $_POST["password"] ?? "";

$course = $_POST["course"] ?? "";

$year = $_POST["year"] ?? "";

$department = $_POST["department"] ?? "";

$qualification = $_POST["qualification"] ?? "";


/* =========================
   CSV FILE
========================= */

$file = "users.csv";


/* =========================
   CREATE CSV + HEADER
========================= */

if (!file_exists($file)) {

    $handle = fopen($file, "w");

    fputcsv($handle, [
        "Role",
        "Name",
        "Email",
        "Mobile",
        "Gender",
        "Course",
        "Year",
        "Department",
        "Qualification",
        "Password"
    ]);

    fclose($handle);
}


/* =========================
   ADD USER
========================= */

$handle = fopen($file, "a");


fputcsv($handle, [
    $role,
    $name,
    $email,
    $mobile,
    $gender,
    $course,
    $year,
    $department,
    $qualification,
    $password
]);


fclose($handle);


/* =========================
   SUCCESS PAGE
========================= */

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Registration Successful</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            text-align: center;
            padding-top: 100px;
        }

        .box {
            background: white;
            width: 400px;
            margin: auto;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 0 15px #ccc;
        }

        h1 {
            color: green;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #333;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

    </style>

</head>


<body>


    <div class="box">

        <h1>
            Registration Successful!
        </h1>

        <p>
            Your details have been saved successfully.
        </p>


        <a href="../pages/register.html">
            Register Another User
        </a>

    </div>


</body>

</html>

