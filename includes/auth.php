<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// CR001: Keep student sessions out of librarian-only pages.
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") === "student") {
    header("Location: login.php");
    exit();
}
?>
