<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "Invalid request.";
    exit;
}

include "connection.php";


/* =========================
   GET FORM DATA
========================= */

$role = trim($_POST["role"] ?? "");
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$gender = trim($_POST["gender"] ?? "");

$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";

$course = trim($_POST["course"] ?? "");
$year = trim($_POST["year"] ?? "");

$department = trim($_POST["department"] ?? "");
$qualification = trim($_POST["qualification"] ?? "");

$terms = $_POST["terms"] ?? "";


/* =========================
   BACKEND VALIDATION
========================= */

if (
    $role === "" ||
    $name === "" ||
    $email === "" ||
    $mobile === "" ||
    $gender === "" ||
    $password === "" ||
    $confirmPassword === "" ||
    $terms === ""
) {
    die("Please fill all required fields.");
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email address.");
}


if (!preg_match("/^[0-9]{10}$/", $mobile)) {
    die("Mobile number must contain exactly 10 digits.");
}


if (strlen($password) < 8) {
    die("Password must contain at least 8 characters.");
}


if ($password !== $confirmPassword) {
    die("Passwords do not match.");
}


/* =========================
   DUPLICATE CHECK
========================= */

$sql = "SELECT id FROM users WHERE email = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $email);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows > 0) {

    echo "Email already registered.";

    exit;
}

$stmt->close();


/* =========================
   PASSWORD HASHING
========================= */

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/* =========================
   INSERT USER
========================= */

$sql = "INSERT INTO users
        (role, name, email, mobile, gender, course, year,
         department, qualification, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";


$stmt = $conn->prepare($sql);


$stmt->bind_param(
    "ssssssssss",
    $role,
    $name,
    $email,
    $mobile,
    $gender,
    $course,
    $year,
    $department,
    $qualification,
    $hashedPassword
);


if ($stmt->execute()) {

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

            <h1>Registration Successful!</h1>

            <p>Your account has been created successfully.</p>

            <a href="../pages/register.html">
                Register Another User
            </a>

        </div>

    </body>

    </html>

    <?php

} else {

    echo "Registration failed. Please try again.";

}


$stmt->close();

$conn->close();

?>