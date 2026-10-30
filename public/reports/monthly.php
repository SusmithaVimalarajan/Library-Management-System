
<?php

require_once "../../config/db.php";
require_once "../../includes/auth.php";
require_once "../../config/settings.php";

$month = $_GET["month"] ?? date("Y-m");

// CR002: Include persisted fine amounts in the monthly borrowing report.
$stmt = $conn->prepare("
    SELECT 
        br.*,
        s.index_number,
        s.name AS student_name,
        b.book_id,
        b.title
    FROM borrowings br
    JOIN students s ON br.student_id = s.id
    JOIN books b ON br.book_id = b.id
    WHERE DATE_FORMAT(br.issue_date, '%Y-%m') = ?
    ORDER BY br.issue_date DESC
");

$stmt->bind_param("s", $month);
$stmt->execute();

$rows = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report</title>
    <link rel="stylesheet" href="../../assets/css/style.css">

</head>



<body>

<?php include "../../includes/navbar.php"; ?>


<div class="container">

    <div class="report-links">

        <a class="btn" href="monthly.php">Monthly Report</a>

        <a class="btn" href="student.php">Student Report</a>

        <a class="btn" href="book.php">Book Report </a>

    </div>

    <div class="page-head">

        <h1>Monthly Borrowing Report</h1>

    
    </div>


    

    

    <form class="search-form" method="GET">

        <input
            type="month"
            name="month"
            value="<?= htmlspecialchars($month) ?>">

        <button type="submit" class="btn primary">View Report </button>
    </form>


    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Book ID</th>
                    <th>Book Name</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Fine (<?= FINE_CURRENCY ?>)</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($rows->num_rows > 0): ?>

                <?php while ($r = $rows->fetch_assoc()): ?>

                    <tr>
                        <td>
                            <?= htmlspecialchars($r["index_number"] ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($r["student_name"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars( $r["book_id"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars( $r["title"] ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars( $r["issue_date"] ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars( $r["due_date"] ) ?>
                        </td>

                        <td>
                            <?php if (!empty($r["return_date"])): ?>
                                <?= htmlspecialchars($r["return_date"] ) ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars(
                                $r["status"]
                            ) ?>
                        </td>
                        <td><?= number_format((float)$r["fine_amount"], 2) ?></td>
                    </tr>
                <?php endwhile; ?>

          <?php else: ?>


                <tr>
                    <td
                        colspan="9"
                        style="text-align:center;">

                        No borrowing records found
                        for this month.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>
    </div>

    
</div>


</body>

</html>
