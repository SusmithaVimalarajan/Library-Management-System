<?php
require_once "../config/db.php";
require_once "../includes/student_auth.php";

$studentId = (int)$_SESSION["student_id"];

// CR001: Scope both personal record lists to the authenticated student ID.
$borrowingsStmt = $conn->prepare(
    "SELECT br.issue_date, br.due_date, br.return_date, br.status, br.fine_amount,
            b.book_id, b.title
     FROM borrowings br
     JOIN books b ON br.book_id = b.id
     WHERE br.student_id = ?
     ORDER BY br.id DESC"
);
$borrowingsStmt->bind_param("i", $studentId);
$borrowingsStmt->execute();
$borrowings = $borrowingsStmt->get_result();

$holdsStmt = $conn->prepare(
    "SELECT h.hold_time, h.expiry_time, h.status,
            b.book_id, b.title
     FROM book_holds h
     JOIN books b ON h.book_id = b.id
     WHERE h.student_id = ?
     ORDER BY h.id DESC"
);
$holdsStmt->bind_param("i", $studentId);
$holdsStmt->execute();
$holds = $holdsStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Library Account</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="logo">Student Account</div>
    <div class="nav-links">
        <a href="student-dashboard.php">My Records</a>
        <a href="index.php">Book Catalogue</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main class="container">
    <h1>Welcome, <?= htmlspecialchars($_SESSION["student_name"]) ?></h1>

    <h2>My Borrowings</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Fine</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($borrowings->num_rows > 0): ?>
                <?php while ($borrowing = $borrowings->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($borrowing["book_id"] . " - " . $borrowing["title"]) ?></td>
                        <td><?= htmlspecialchars($borrowing["issue_date"]) ?></td>
                        <td><?= htmlspecialchars($borrowing["due_date"]) ?></td>
                        <td><?= htmlspecialchars($borrowing["return_date"] ?? "-") ?></td>
                        <td><?= htmlspecialchars($borrowing["status"]) ?></td>
                        <td>LKR <?= number_format((float)$borrowing["fine_amount"], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="empty-state">You have no borrowing records.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h2>My Holds</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Hold Date</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($holds->num_rows > 0): ?>
                <?php while ($hold = $holds->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($hold["book_id"] . " - " . $hold["title"]) ?></td>
                        <td><?= htmlspecialchars($hold["hold_time"]) ?></td>
                        <td><?= htmlspecialchars($hold["expiry_time"]) ?></td>
                        <td><?= htmlspecialchars($hold["status"]) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="empty-state">You have no hold records.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
