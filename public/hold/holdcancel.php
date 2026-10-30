<?php
require_once "../../config/db.php";
require_once "../../includes/student_auth.php";


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit();
}



$token = trim($_POST["hold_token"] ?? "");

if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    http_response_code(400);
    exit("Invalid hold token.");
}

$conn->begin_transaction();

try {
    $holdStmt = $conn->prepare(
        "SELECT id, book_id, status, expiry_time
         FROM book_holds
         WHERE hold_token = ?
           AND student_id = ?
         LIMIT 1
         FOR UPDATE"
    );
    $studentId = (int)$_SESSION["student_id"];
    $holdStmt->bind_param("si", $token, $studentId);
    $holdStmt->execute();
    $hold = $holdStmt->get_result()->fetch_assoc();

    if (!$hold) {
        throw new Exception("Hold not found.");
    }

    if ($hold["status"] === "CANCELLED") {
        throw new Exception("This hold has been cancelled.");
    }

    if ($hold["status"] === "BORROWED") {
        throw new Exception("This hold has already been converted to a borrowing.");
    }

    if ($hold["status"] === "EXPIRED") {
        throw new Exception("This hold has expired.");
    }

    if (strtotime($hold["expiry_time"]) <= time()) {
        $newStatus = "EXPIRED";
        $message = "This hold has expired.";
    } else {
        $newStatus = "CANCELLED";
        $message = "This hold has been cancelled.";
    }

    $bookStmt = $conn->prepare(
        "SELECT quantity, available_quantity
         FROM books
         WHERE id = ?
         FOR UPDATE"
    );
    $bookStmt->bind_param("i", $hold["book_id"]);
    $bookStmt->execute();
    $book = $bookStmt->get_result()->fetch_assoc();

    if (!$book) {
        throw new Exception("Book not found.");
    }

   
    $newAvailable = min(
        (int)$book["quantity"],
        (int)$book["available_quantity"] + 1
    );

    $updateBook = $conn->prepare(
        "UPDATE books SET available_quantity = ? WHERE id = ?"
    );
    $updateBook->bind_param("ii", $newAvailable, $hold["book_id"]);
    $updateBook->execute();

    $updateHold = $conn->prepare(
        "UPDATE book_holds
         SET status = ?
         WHERE id = ? AND status = 'ACTIVE'"
    );
    $updateHold->bind_param("si", $newStatus, $hold["id"]);
    $updateHold->execute();

    if ($updateHold->affected_rows !== 1) {
        throw new Exception("This hold is no longer active.");
    }

    $conn->commit();

    $_SESSION["hold_message"] = $message;

    header("Location: confirmation.php?token=" . urlencode($token));
    exit();

} catch (Throwable $e) {
    $conn->rollback();

    $_SESSION["hold_error"] = $e->getMessage();

    header("Location: confirmation.php?token=" . urlencode($token));
    exit();
}
?>