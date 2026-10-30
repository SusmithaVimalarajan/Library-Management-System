<?php

require_once "../../config/db.php";
require_once "../../includes/auth.php";
require_once "../../config/settings.php";

$student_id = $_GET["student_id"] ?? "";




$students = $conn->query("
    SELECT id, index_number, name
    FROM students
    ORDER BY name ASC");




$rows = null;

if ($student_id !== "") {

    // CR002: Include persisted fine amounts in the student borrowing report.
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

            b.book_id,
            b.title,
            b.author,
            b.category

        FROM borrowings br

        INNER JOIN students s
            ON br.student_id = s.id

        INNER JOIN books b
            ON br.book_id = b.id

        WHERE br.student_id = ?

        ORDER BY br.issue_date DESC
    ");

    $stmt->bind_param("i", $student_id);

    $stmt->execute();

    $rows = $stmt->get_result();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Borrowing Report</title>

    <link rel="stylesheet" href="../../assets/css/style.css">

</head>


<body>

<?php include "../../includes/navbar.php"; ?>


<div class="container">

<div class="report-links">

        <a class="btn" href="monthly.php">
            Monthly Report
        </a>

        <a class="btn" href="student.php">
            Student Report
        </a>

        <a class="btn" href="book.php">
            Book Report
        </a>

    </div>


    <div class="page-head">

        <h1>Student Borrowing Report</h1>

    </div>



    <form class="search-form" method="GET">

        <select name="student_id" required>

            <option value="">
            --Student Details--
            </option>

            <?php while ($student = $students->fetch_assoc()): ?>

                <option
                    value="<?= (int)$student["id"] ?>"
                    <?= ($student_id == $student["id"]) ? "selected" : "" ?>>

                    <?= htmlspecialchars($student["index_number"]) ?>

                    -

                    <?= htmlspecialchars($student["name"]) ?>

                </option>

            <?php endwhile; ?>

        </select>


        <button type="submit" class="btn primary">
            View Borrowing Details
        </button>

    </form>


    <?php if ($rows !== null): ?>

        <br>


        <div class="table-wrap">

            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Book ID</th>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Category</th>
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
                                <?= htmlspecialchars($row["index_number"]   ) ?>                                  
                            </td>

                            <td>
                                <?= htmlspecialchars($row["student_name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row["book_id"]
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row["title"]
                                ) ?>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $row["author"]
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row["category"]
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row["issue_date"]
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row["due_date"]
                                ) ?>
                            </td>

                            <td>

                                <?php if (!empty($row["return_date"])): ?>

                                    <?= htmlspecialchars(
                                        $row["return_date"]
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
                        <td colspan="11"
                            style="text-align:center;">
                            No borrowing records found
                            for this student.
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