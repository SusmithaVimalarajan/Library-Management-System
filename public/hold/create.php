<?php

require_once "../../config/db.php";
require_once "../../includes/student_auth.php";


date_default_timezone_set("Asia/Colombo");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit();
}



$email       = trim($_POST["email"] ?? "");
$phone       = trim($_POST["phone_number"] ?? "");
$bookId      = filter_input(INPUT_POST, "book_id", FILTER_VALIDATE_INT);

$errors = [];


if ($bookId === false || $bookId === null || $bookId < 1) {
    $errors[] = "Invalid book.";
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}


$phoneDigits = preg_replace("/\D+/", "", $phone);

if (
    $phone === "" ||
    strlen($phoneDigits) < 7 ||
    strlen($phoneDigits) > 15 ||
    !preg_match('/^[0-9+\-\s().]{7,20}$/', $phone)
) {
    $errors[] = "Please enter a valid phone number.";
}

if (!empty($errors)) {

    $_SESSION["hold_error"] = implode(" ", $errors);

    header("Location: ../index.php");
    exit();
}

$conn->begin_transaction();

try {

   
    // CR001: Take the hold owner from the authenticated student session.
    $studentStmt = $conn->prepare(
        "SELECT id, name
         FROM students
         WHERE id = ?
         LIMIT 1
         FOR UPDATE"
    );

    $studentId = (int)$_SESSION["student_id"];
    $studentStmt->bind_param("i", $studentId);
    $studentStmt->execute();

    $student = $studentStmt->get_result()->fetch_assoc();

    if (!$student) {
        throw new Exception("Student account not found.");
    }

    
    $bookStmt = $conn->prepare(
        "SELECT id, book_id, title, quantity, available_quantity
         FROM books
         WHERE id = ?
         LIMIT 1
         FOR UPDATE"
    );

    $bookStmt->bind_param("i", $bookId);
    $bookStmt->execute();

    $book = $bookStmt->get_result()->fetch_assoc();

    if (!$book) {
        throw new Exception("Book not found.");
    }

    
    if ((int)$book["available_quantity"] < 1) {
        throw new Exception("Sorry, this book is currently unavailable.");
    }

   
    $duplicateStmt = $conn->prepare(
        "SELECT id
         FROM book_holds
         WHERE student_id = ?
           AND book_id = ?
           AND status = 'ACTIVE'
         LIMIT 1"
    );

    $duplicateStmt->bind_param(
        "ii",
        $student["id"],
        $book["id"]
    );

    $duplicateStmt->execute();

    if ($duplicateStmt->get_result()->num_rows > 0) {
        throw new Exception(
            "You already have an active hold for this book."
        );
    }

   
    $holdToken = bin2hex(random_bytes(32));

    $insert = $conn->prepare(
        "INSERT INTO book_holds
        (
            hold_token,
            student_id,
            book_id,
            email,
            phone_number,
            hold_time,
            expiry_time,
            status,
            notification_sent
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            NOW(),
            DATE_ADD(NOW(), INTERVAL 10 MINUTE),
            'ACTIVE',
            0
        )"
    );

    $insert->bind_param(
        "siiss",
        $holdToken,
        $student["id"],
        $book["id"],
        $email,
        $phone
    );

    $insert->execute();

   
    $updateBook = $conn->prepare(
        "UPDATE books
         SET available_quantity = available_quantity - 1
         WHERE id = ?
           AND available_quantity > 0"
    );

    $updateBook->bind_param("i", $book["id"]);
    $updateBook->execute();

    if ($updateBook->affected_rows !== 1) {
        throw new Exception(
            "Sorry, this book is no longer available."
        );
    }

    $conn->commit();

   
    header(
        "Location: confirmation.php?token=" .
        urlencode($holdToken)
    );

    exit();

} catch (Throwable $e) {

    $conn->rollback();

    $_SESSION["hold_error"] = $e->getMessage();

    header("Location: ../index.php");
    exit();
}