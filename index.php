<?php
// Start the session
session_start();
require 'config.php';

// If the user is already logged in, redirect to the dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['unique_pin'])) {
    header("Location: data.php");
    exit;
}

// Check if the PIN has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pin'])) {
    $pin = $_POST['pin'];

    // Prepare and execute the query to check the PIN
    $stmt = $conn->prepare("SELECT * FROM farmers WHERE unique_pin = ?");
    $stmt->bind_param("s", $pin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch user data
        $user = $result->fetch_assoc();

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['unique_pin'] = $pin;

        // Redirect to the dashboard
        header("Location: data.php");
        exit;
    } else {
        // Invalid PIN, set error message
        $error_message = "Invalid PIN. Please try again.";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Farmer</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #45a049;
            --background-color: #f0f4f7;
            --text-color: #333;
            --error-color: #f44336;
        }

        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            height: 100%;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }

        .card {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .logo {
            width: 120px;
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 30px;
        }

        .pin-input {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            border: 2px solid #ddd;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .pin-input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .welcome-button {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 30px;
            font-size: 18px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            width: 100%;
        }

        .welcome-button:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .error-message {
            color: var(--error-color);
            margin-top: 20px;
            font-size: 14px;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }

        @media (max-width: 480px) {
            .card {
                padding: 30px;
            }

            .logo {
                width: 100px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <img src="logo.jpg" alt="Company Logo" class="logo">
            <h1>Welcome, Farmer</h1>

            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form action="index.php" method="POST">
                <input type="text" name="pin" placeholder="Enter your 4-digit PIN" maxlength="4" class="pin-input" required>
                <button type="submit" class="welcome-button">Log In</button>
            </form>

            <div class="footer">
                &copy; 2023 Your Company. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>
