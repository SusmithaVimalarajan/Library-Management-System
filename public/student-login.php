<?php
session_start();

if (($_SESSION["role"] ?? "") === "student" && isset($_SESSION["student_id"])) {
    header("Location: student-dashboard.php");
    exit();
}

require_once "../config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $indexValue = $_POST["index_number"] ?? "";
    $passwordValue = $_POST["password"] ?? "";
    $indexNumber = is_string($indexValue) ? trim($indexValue) : "";
    $password = is_string($passwordValue) ? $passwordValue : "";

    if ($indexNumber === "" || $password === "") {
        $error = "Enter your student index number and password.";
    } else {
        // CR001: Authenticate students independently from librarian accounts.
        $stmt = $conn->prepare(
            "SELECT id, index_number, name, password_hash
             FROM students
             WHERE index_number = ?
             LIMIT 1"
        );

        if (!$stmt) {
            $error = "Student login is temporarily unavailable.";
        } else {
            $stmt->bind_param("s", $indexNumber);
            $stmt->execute();
            $student = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (
                $student &&
                !empty($student["password_hash"]) &&
                password_verify($password, $student["password_hash"])
            ) {
                session_regenerate_id(true);
                unset($_SESSION["user_id"], $_SESSION["username"]);
                $_SESSION["student_id"] = (int)$student["id"];
                $_SESSION["student_name"] = $student["name"];
                $_SESSION["role"] = "student";

                header("Location: student-dashboard.php");
                exit();
            }

            $error = "Invalid student index number or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | Library Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">
    <main class="login-container">
        <div class="login-header">
            <h1>Library Management System</h1>
            <p>Student Login</p>
        </div>

        <?php if ($error !== ""): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="index_number">Student Index Number</label>
                <input type="text" id="index_number" name="index_number"
                       autocomplete="username"
                       value="<?= htmlspecialchars($_POST["index_number"] ?? "") ?>"
                       required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-field">
                    <input type="password" id="password" name="password"
                           autocomplete="current-password" required>
                    <button class="password-toggle" type="button"
                            data-password-toggle aria-controls="password"
                            aria-pressed="false">Show</button>
                </div>
            </div>
            <button type="submit" class="btn primary full">Login</button>
        </form>

        <p class="text-muted">
            <strong>Need a student account?</strong>
            Please contact the library staff to create one for you. Once your
            account is ready, sign in with your student index number and password.
        </p>

        <a class="btn full" href="login.php">Return to Admin Login</a>
    </main>
    <script src="../assets/js/password-toggle.js"></script>
    </body>
    </html>
