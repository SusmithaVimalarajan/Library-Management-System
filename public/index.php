<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "../config/db.php";

// CR001: Do not expose the catalogue to users without a student or librarian session.
if (
    !(($_SESSION["role"] ?? "") === "student" && isset($_SESSION["student_id"])) &&
    !isset($_SESSION["user_id"])
) {
    header("Location: login.php");
    exit();
}

$q = trim($_GET["q"] ?? "");

if ($q !== "") {
    $like = "%" . $q . "%";

    $stmt = $conn->prepare(
        "SELECT * FROM books
         WHERE book_id LIKE ? OR title LIKE ? OR author LIKE ?
            OR isbn LIKE ? OR category LIKE ?
         ORDER BY title"
    );

    $stmt->bind_param("sssss", $like, $like, $like, $like, $like);
    $stmt->execute();
    $books = $stmt->get_result();
} else {
    $books = $conn->query(
        "SELECT * FROM books ORDER BY title"
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Books</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php if (isset($_SESSION["user_id"])): ?>
    <?php include "../includes/navbar.php"; ?>
<?php else: ?>
<nav class="navbar">
    <div class="logo">Library Management System</div>
    <div class="nav-links">
        <a href="student-dashboard.php">My Records</a>
        <a href="index.php">Book Catalogue</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>
<?php endif; ?>

<div class="container">
    <h1>Library Books</h1>

    <?php if (!empty($_SESSION["hold_error"])): ?>
        <div class="alert error">
            <?= htmlspecialchars($_SESSION["hold_error"]) ?>
        </div>
        <?php unset($_SESSION["hold_error"]); ?>
    <?php endif; ?>

    <form class="search-form" method="GET">
        <input type="text"
               name="q"
               value="<?= htmlspecialchars($q) ?>"
               placeholder="Search by ID, title, author, ISBN or category">
        <button type="submit" class="btn primary">Search</button>
        <?php if ($q !== ""): ?>
            <a class="btn" href="index.php">Clear</a>
        <?php endif; ?>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Book ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Available Copies</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($books->num_rows > 0): ?>
                <?php while ($book = $books->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($book["book_id"]) ?></td>
                        <td><?= htmlspecialchars($book["title"]) ?></td>
                        <td><?= htmlspecialchars($book["author"]) ?></td>
                        <td><?= htmlspecialchars($book["category"] ?: "-") ?></td>
                        <td><?= (int)$book["available_quantity"] ?></td>
                        <td>
                            <?php if (
                                ($_SESSION["role"] ?? "") === "student" &&
                                isset($_SESSION["student_id"]) &&
                                (int)$book["available_quantity"] > 0
                            ): ?>
                                <button
                                    type="button"
                                    class="btn primary small hold-open"
                                    data-book-id="<?= (int)$book["id"] ?>"
                                    data-book-label="<?= htmlspecialchars($book["book_id"] . " - " . $book["title"], ENT_QUOTES) ?>">
                                    Hold Book
                                </button>
                            <?php elseif ((int)$book["available_quantity"] < 1): ?>
                                <span class="badge unavailable-badge">Currently Unavailable</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="empty-state">
                        No books found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div id="holdModal" class="modal" aria-hidden="true">
    <div class="modal-card">
        <button type="button" class="modal-close" id="holdClose" aria-label="Close">&times;</button>

        <h2>Hold Book</h2>
        <p class="text-muted" id="selectedBookLabel"></p>

        <div id="holdError" class="alert error" style="display:none;"></div>

        <form method="POST" action="hold/create.php" id="holdForm">
            
            <input type="hidden" name="book_id" id="holdBookId">

            <p>Placing a hold as <strong><?= htmlspecialchars($_SESSION["student_name"]) ?></strong></p>

            <label for="email">Email Address </label>
            <input type="email" id="email" name="email"
                   placeholder="student@example.com" required>

            <label for="phone_number">Phone Number </label>
            <input type="text" id="phone_number" name="phone_number"
                   placeholder="0771234567" maxlength="13" required>

            <div class="actions">
                <button type="submit" class="btn primary">Confirm Hold</button>
                <button type="button" class="btn" id="holdCancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById("holdModal");
const closeButton = document.getElementById("holdClose");
const cancelButton = document.getElementById("holdCancel");
const bookIdInput = document.getElementById("holdBookId");
const selectedBookLabel = document.getElementById("selectedBookLabel");
const holdForm = document.getElementById("holdForm");

function closeHoldModal() {
    modal.classList.remove("show");
    modal.setAttribute("aria-hidden", "true");
    holdForm.reset();
}

document.querySelectorAll(".hold-open").forEach(button => {
    button.addEventListener("click", () => {
        bookIdInput.value = button.dataset.bookId;
        selectedBookLabel.textContent = button.dataset.bookLabel;
        modal.classList.add("show");
        modal.setAttribute("aria-hidden", "false");
        document.getElementById("email").focus();
    });
});

closeButton.addEventListener("click", closeHoldModal);
cancelButton.addEventListener("click", closeHoldModal);

modal.addEventListener("click", event => {
    if (event.target === modal) {
        closeHoldModal();
    }
});
</script>

</body>
</html>
