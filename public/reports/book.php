<?php

require_once "../../config/db.php";
require_once "../../includes/auth.php";
require_once "../../config/settings.php";

$book_id = $_GET["book_id"] ?? "";



$books = $conn->query("
    SELECT id, book_id, title, author
    FROM books
    ORDER BY title ASC
");



$rows = null;

if ($book_id !== "") {

    // CR002: Include persisted fine amounts in the book borrowing report.
    $stmt = $conn->prepare("
        SELECT
            br.id,
            br.issue_date,
            br.due_date,
            br.return_date,
            br.status,
            br.fine_amount,

            s.index_number,
            s.name AS student_name,
            s.grade,
            s.contact_number,

            b.book_id,
            b.title,
            b.author,
            b.category

        FROM borrowings br

        INNER JOIN students s
            ON br.student_id = s.id

        INNER JOIN books b
            ON br.book_id = b.id

        WHERE br.book_id = ?

        ORDER BY br.issue_date DESC ");

    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $rows = $stmt->get_result();
}

?>



<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Borrowing Report</title>

    <link rel="stylesheet" href="../../assets/css/style.css">

</head>


<body>

<?php include "../../includes/navbar.php"; ?>


<div class="container">

   <div class="report-links">

        <a class="btn" href="monthly.php">Monthly Report</a>
        <a class="btn" href="student.php">Student Report </a>
        <a class="btn" href="book.php">Book Report</a>

    </div>

    <div class="page-head">
        <h1>Book Borrowing Report</h1>
    </div>


    <form class="search-form" method="GET">

        <select name="book_id" required>


            <option value="">--Books Details--</option>


            <?php while ($book = $books->fetch_assoc()): ?>

                <option
                    value="<?= (int)$book["id"] ?>"
                    <?= ($book_id == $book["id"]) ? "selected" : "" ?>
                >

                    <?= htmlspecialchars(
                        $book["book_id"]
                    ) ?>

                    -

                    <?= htmlspecialchars(
                        $book["title"]
                    ) ?>

                </option>

            <?php endwhile; ?>

        </select>


        <button
            type="submit"
            class="btn primary">

            View Borrowing Details
        </button>

    </form>


    <?php if ($rows !== null): ?>

        <br>


        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Book ID</th>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Grade</th>
                        <th>Contact</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Fine (<?= FINE_CURRENCY ?>)</th>
                    </tr>
                </thead>
                <tbody>

                <?php if ($rows->num_rows > 0): ?>

                    <?php while ($row = $rows->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($row["book_id"]) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row["title"])
                                    ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row["author"])
                                ?>
                            </td>
                            <td>
                                <?= htmlspecialchars( $row["index_number"])                    
                                ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row["student_name"])                                   
                                ?>
                            </td>
                            <td>
                                <?= htmlspecialchars( $row["grade"])                                  
                                ?>
                            </td>
                            <td>
                                <?= htmlspecialchars( $row["contact_number"])                                   
                                ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row["issue_date"])                                    
                                ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row["due_date"])                                    
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($row["return_date"])): ?>
                                    <?= htmlspecialchars($row["return_date"]
                                    ) ?>

                                <?php else: ?>

                                    -

                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    $row["status"]
                                ) ?>
                            </td>
                            <td><?= number_format((float)$row["fine_amount"], 2) ?></td>
                        </tr>

                    <?php endwhile; ?>


                <?php else: ?>

                    <tr>
                        <td colspan="12"
                            style="text-align:center;">

                            No students have borrowed
                            this book.

                        </td>

                    </tr>

                <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>

</body>
</html>