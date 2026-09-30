<?php

// ==========================================
// StudentHub - Contact Form Processor
// Practical 7
// ==========================================


// Only POST allowed
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    showMessage(
        "Invalid Request",
        "Please submit the contact form.",
        false
    );

    exit;
}


// ==========================================
// HELPER FUNCTIONS
// ==========================================

function cleanInput($data) {

    return trim(strip_tags($data));

}


function showMessage($title, $message, $success = true) {

    $color = $success ? "#16a34a" : "#dc2626";
    $icon = $success ? "✓" : "✕";

    echo "

    <!DOCTYPE html>

    <html lang='en'>

    <head>

        <meta charset='UTF-8'>

        <meta name='viewport'
              content='width=device-width, initial-scale=1.0'>

        <title>StudentHub Contact</title>

        <style>

            * {
                box-sizing: border-box;
            }

            body {

                margin: 0;

                font-family: Arial, sans-serif;

                background: #f5f7fb;

                display: flex;

                justify-content: center;

                align-items: center;

                min-height: 100vh;

            }

            .response-box {

                width: 90%;

                max-width: 550px;

                background: white;

                padding: 40px;

                border-radius: 16px;

                text-align: center;

                box-shadow:
                    0 10px 30px
                    rgba(0,0,0,0.10);

            }

            .icon {

                width: 65px;

                height: 65px;

                margin: 0 auto 20px;

                border-radius: 50%;

                background: {$color};

                color: white;

                display: flex;

                align-items: center;

                justify-content: center;

                font-size: 32px;

            }

            h1 {

                color: #222;

                margin-bottom: 15px;

            }

            p {

                color: #555;

                line-height: 1.6;

            }

            .back-btn {

                display: inline-block;

                margin-top: 20px;

                padding: 12px 24px;

                background: #111827;

                color: white;

                text-decoration: none;

                border-radius: 8px;

            }

        </style>

    </head>

    <body>

        <div class='response-box'>

            <div class='icon'>
                {$icon}
            </div>

            <h1>{$title}</h1>

            <p>{$message}</p>

            <a href='../pages/contact.html'
               class='back-btn'>
                Back to Contact
            </a>

        </div>

    </body>

    </html>

    ";
}


// ==========================================
// GET DATA
// ==========================================

$name = cleanInput(
    $_POST["name"] ?? ""
);

$email = cleanInput(
    $_POST["email"] ?? ""
);

$subject = cleanInput(
    $_POST["subject"] ?? ""
);

$message = cleanInput(
    $_POST["message"] ?? ""
);


// ==========================================
// SERVER-SIDE VALIDATION
// ==========================================

$errors = [];


// Name
if ($name === "") {

    $errors[] = "Name is required.";

} elseif (!preg_match("/^[A-Za-z ]{2,50}$/", $name)) {

    $errors[] = "Please enter a valid name.";

}


// Email
if ($email === "") {

    $errors[] = "Email is required.";

} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $errors[] = "Please enter a valid email address.";

}


// Subject
if ($subject === "") {

    $errors[] = "Subject is required.";

} elseif (strlen($subject) < 3) {

    $errors[] = "Subject must contain at least 3 characters.";

}


// Message
if ($message === "") {

    $errors[] = "Message is required.";

} elseif (strlen($message) < 10) {

    $errors[] = "Message must contain at least 10 characters.";

}


// ==========================================
// DISPLAY ERRORS
// ==========================================

if (!empty($errors)) {

    $errorMessage =
        implode("<br>", $errors);

    showMessage(
        "Message Not Sent",
        $errorMessage,
        false
    );

    exit;
}


// ==========================================
// CSV FILE
// ==========================================

$file =
    __DIR__ . "/../data/contacts.csv";


// Create data directory if necessary
$directory = dirname($file);

if (!is_dir($directory)) {

    mkdir(
        $directory,
        0755,
        true
    );

}


// ==========================================
// OPEN FILE
// ==========================================

$handle = fopen(
    $file,
    "a"
);


if ($handle === false) {

    showMessage(
        "Storage Error",
        "Unable to save your message.",
        false
    );

    exit;

}


// ==========================================
// SAFE FILE WRITING
// ==========================================

if (flock($handle, LOCK_EX)) {


    // Add CSV header
    if (filesize($file) === 0) {

        fputcsv(
            $handle,
            [
                "Date",
                "Name",
                "Email",
                "Subject",
                "Message"
            ]
        );

    }


    // Add contact record
    fputcsv(
        $handle,
        [
            date("Y-m-d H:i:s"),
            $name,
            $email,
            $subject,
            $message
        ]
    );


    // Unlock
    flock(
        $handle,
        LOCK_UN
    );

}


// Close file
fclose($handle);


// ==========================================
// SUCCESS
// ==========================================

showMessage(
    "Message Sent Successfully",
    "Thank you for contacting StudentHub. Your message has been stored successfully.",
    true
);

?>