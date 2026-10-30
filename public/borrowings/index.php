<?php
require_once "../../config/db.php";
require_once "../../includes/auth.php";

$searchValue = $_GET["q"] ?? "";
$statusValue = $_GET["status"] ?? "";
$search = is_string($searchValue) ? trim($searchValue) : "";
$statusFilter = is_string($statusValue) ? trim($statusValue) : "";
$error = "";

// CR003: Accept only the borrowing statuses supported by the request.
if (
    !is_string($statusValue) ||
    !in_array($statusFilter, ["", "Issued", "Returned"], true)
) {
    $error = "Choose a valid borrowing status.";
    $statusFilter = "";
}

$searchPattern = "%" . $search . "%";
$stmt = $conn->prepare(
    "SELECT br.*, s.index_number, s.name AS student_name, b.book_id, b.title
     FROM borrowings br
     JOIN students s ON br.student_id = s.id
     JOIN books b ON br.book_id = b.id
     WHERE (? = '' OR s.name LIKE ? OR b.title LIKE ? OR b.book_id LIKE ?)
       AND (? = '' OR br.status = ?)
     ORDER BY br.id DESC"
);

if (!$stmt) {
    exit("Unable to load borrowing records.");
}

$stmt->bind_param(
    "ssssss",
    $search,
    $searchPattern,
    $searchPattern,
    $searchPattern,
    $statusFilter,
    $statusFilter
);
$stmt->execute();
$borrowings = $stmt->get_result();
$today = date("Y-m-d");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowings</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<?php include "../../includes/navbar.php"; ?>

<main class="container">
    <div class="page-head">
        <h1>Borrowings</h1>
        <a class="btn primary" href="issue.php">+ Issue Book</a>
    </div>

    <!-- CR003: Search and status filters can be submitted together. -->
    <form class="search-form" method="GET">
        <input type="search" name="q" value="<?= htmlspecialchars($search) ?>"
               placeholder="Search student name or book">
        <select name="status" aria-label="Filter by borrowing status">
            <option value="" <?= $statusFilter === "" ? "selected" : "" ?>>All statuses</option>
            <option value="Issued" <?= $statusFilter === "Issued" ? "selected" : "" ?>>Issued</option>
            <option value="Returned" <?= $statusFilter === "Returned" ? "selected" : "" ?>>Returned</option>
        </select>
        <button class="btn primary" type="submit">Search</button>
        <?php if ($search !== "" || $statusFilter !== ""): ?>
            <a class="btn" href="index.php">Clear</a>
        <?php endif; ?>
    </form>

    <?php if ($error !== ""): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Book</th>
                    <th>Issue</th>
                    <th>Due</th>
                    <th>Return</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($borrowings->num_rows > 0): ?>
                <?php while ($r = $borrowings->fetch_assoc()): ?>
                    <?php
                    // CR004: Only an unreturned issued book past its due date is overdue.
                    $isOverdue = $r["status"] === "Issued" && $r["due_date"] < $today;
                    ?>
                    <tr class="<?= $isOverdue ? "overdue-row" : "" ?>">
                        <td><?= htmlspecialchars($r["index_number"] . " - " . $r["student_name"]) ?></td>
                        <td><?= htmlspecialchars($r["book_id"] . " - " . $r["title"]) ?></td>
                        <td><?= htmlspecialchars($r["issue_date"]) ?></td>
                        <td><?= htmlspecialchars($r["due_date"]) ?></td>
                        <td><?= htmlspecialchars($r["return_date"] ?? "-") ?></td>
                        <td>
                            <span class="badge"><?= htmlspecialchars($r["status"]) ?></span>
                            <?php if ($isOverdue): ?>
                                <span class="badge overdue-badge">Overdue</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r["status"] === "Issued"): ?>
                                <a class="btn small re" href="return.php?id=<?= (int)$r["id"] ?>">Return</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty-state">No borrowing records match your search and status filter.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
