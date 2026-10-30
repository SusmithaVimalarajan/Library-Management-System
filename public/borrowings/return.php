<?php
require_once "../../config/db.php";
require_once "../../includes/auth.php";
require_once "../../config/settings.php";

$id = (int)($_GET["id"] ?? 0);

$todayResult = $conn->query("SELECT CURDATE() AS today");
if (!$todayResult) {
    exit("Unable to determine the return date.");
}
$returnDate = $todayResult->fetch_assoc()["today"];

$s = $conn->prepare(
    "SELECT br.*,b.title,s.name 
     FROM borrowings br 
     JOIN books b ON br.book_id=b.id 
     JOIN students s ON br.student_id=s.id 
     WHERE br.id=? AND br.status='Issued'"
);

$s->bind_param("i", $id);
$s->execute();

$row = $s->get_result()->fetch_assoc();
$error = "";

if (!$row) {
    die("Borrowing record not found.");
}

// CR002: Calculate the preview using the database's current calendar date.
$dueDate = new DateTimeImmutable($row["due_date"]);
$returnedOn = new DateTimeImmutable($returnDate);
$overdueDays = max(0, (int)$dueDate->diff($returnedOn)->format("%r%a"));
$fineAmount = round($overdueDays * FINE_PER_OVERDUE_DAY, 2);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $conn->begin_transaction();

    try {

        // CR002: Persist the calculated fine together with the return date.
        $updateBorrowing = $conn->prepare(
            "UPDATE borrowings 
             SET return_date=?, fine_amount=?, status='Returned'
             WHERE id=? AND status='Issued'"
        );

        if (!$updateBorrowing) {
            throw new Exception("Unable to prepare the borrowing update.");
        }

        $updateBorrowing->bind_param("sdi", $returnDate, $fineAmount, $id);
        $updateBorrowing->execute();

        if ($updateBorrowing->affected_rows !== 1) {
            throw new Exception("The borrowing is no longer available for return.");
        }

        $updateBook = $conn->prepare(
            "UPDATE books 
             SET available_quantity=available_quantity+1 
             WHERE id=?"
        );

        if (!$updateBook) {
            throw new Exception("Unable to prepare the book availability update.");
        }

        $updateBook->bind_param("i", $row["book_id"]);
        $updateBook->execute();

        $conn->commit();

        header("Location: index.php");
        exit();

    } catch (Throwable $e) {

        $conn->rollback();
        $error = "Return failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Return Book</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

<?php include "../../includes/navbar.php"; ?>

<div class="container">

    <div class="form-card">

        <h1>Confirm Return</h1>

        <?php if ($error !== ""): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <p>
            Return 
            <strong>
                <?= htmlspecialchars($row["title"]) ?>
            </strong>
            borrowed by 
            <strong>
                <?= htmlspecialchars($row["name"]) ?>
            </strong>?
        </p>

        <p><strong>Overdue days:</strong> <?= $overdueDays ?></p>
        <p><strong>Fine:</strong> <?= FINE_CURRENCY ?> <?= number_format($fineAmount, 2) ?></p>
        <p class="text-muted">
            Rate: <?= FINE_CURRENCY ?> <?= number_format(FINE_PER_OVERDUE_DAY, 2) ?> per overdue day.
        </p>

        <form method="POST">

            <button class="btn primary" type="submit">
                Confirm Return
            </button>

            <a class="btn" href="index.php">
                Cancel
            </a>

        </form>

        

    </div>

</div>

</body>
</html> 