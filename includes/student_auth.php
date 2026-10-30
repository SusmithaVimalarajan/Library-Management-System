<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CR001: Require a student identity for all student self-service pages.
if (
    !isset($_SESSION["student_id"]) ||
    ($_SESSION["role"] ?? "") !== "student"
) {
    $scriptPath = (string)($_SERVER["SCRIPT_NAME"] ?? "");
    $loginPath = strpos($scriptPath, "/hold/") !== false
        ? "../student-login.php"
        : "student-login.php";

    header("Location: " . $loginPath);
    exit();
}
