
<?php
require_once __DIR__ . '/php/db_mysqli.php';

$name = 'StudentHub Admin';
$email = 'admin@studenthub.com';
$password = 'Admin@12345';
$role = 'admin';

// Check whether the email already exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();

$result = $check->get_result();

if ($result->num_rows > 0) {
    exit("This admin email already exists.");
}

// Hash the password before storing it
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert admin account using your actual column names
$stmt = $conn->prepare(
    "INSERT INTO users (role, name, email, password)
     VALUES (?, ?, ?, ?)"
);

$stmt->bind_param("ssss", $role, $name, $email, $hashedPassword);

if ($stmt->execute()) {
    echo "Admin account created successfully!<br>";
    echo "Email: admin@studenthub.com<br>";
    echo "Password: Admin@12345";
} else {
    echo "Could not create admin account.";
}
?>
