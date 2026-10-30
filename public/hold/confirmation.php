<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/db.php";
require_once "../../includes/student_auth.php";






$token = trim($_GET["token"] ?? "");

if (!preg_match('/^[a-f0-9]{64}$/', $token)) {

    http_response_code(400);
    exit("Invalid hold link.");

}




$stmt = $conn->prepare(
    "SELECT h.*,
            s.name AS student_name,
            s.index_number,
            b.book_id AS display_book_id,
            b.title
     FROM book_holds h
     JOIN students s ON h.student_id = s.id
     JOIN books b ON h.book_id = b.id
     WHERE h.hold_token = ?
     LIMIT 1"
);

$stmt->bind_param("s", $token);
$stmt->execute();

$hold = $stmt->get_result()->fetch_assoc();


if (!$hold) {

    http_response_code(404);
    exit("Hold not found.");

}

// CR001: A hold confirmation is visible only to the student who owns the hold.
if ((int)$hold["student_id"] !== (int)$_SESSION["student_id"]) {
    http_response_code(403);
    exit("You are not authorized to view this hold.");
}



$timeStmt = $conn->prepare(
    "SELECT TIMESTAMPDIFF(
                SECOND,
                NOW(),
                expiry_time
            ) AS remaining_seconds
     FROM book_holds
     WHERE hold_token = ?
     LIMIT 1"
);

$timeStmt->bind_param("s", $token);
$timeStmt->execute();

$timeResult = $timeStmt->get_result()->fetch_assoc();

$remainingSeconds = max(
    0,
    (int)($timeResult["remaining_seconds"] ?? 0)
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Hold Confirmation</title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>


<body>



<nav class="navbar">

    <div class="logo">
        Library Management System
    </div>

    <div class="nav-links">
        <a href="../student-dashboard.php">My Records</a>
        <a href="../index.php">Book Catalogue</a>
        <a href="../logout.php">Logout</a>
    </div>

</nav>




<div class="container">

    <div class="form-card hold-confirmation">


        

        <?php if (!empty($_SESSION["hold_message"])): ?>

            <div class="alert success">

                <?= htmlspecialchars(
                    $_SESSION["hold_message"]
                ) ?>

            </div>

            <?php unset($_SESSION["hold_message"]); ?>

        <?php endif; ?>


        

        <?php if (!empty($_SESSION["hold_error"])): ?>

            <div class="alert error">

                <?= htmlspecialchars(
                    $_SESSION["hold_error"]
                ) ?>

            </div>

            <?php unset($_SESSION["hold_error"]); ?>

        <?php endif; ?>


        <h1>
            Book Hold
        </h1>


        <?php if ($hold["status"] === "ACTIVE"): ?>


            

            <div class="alert success">

                Book held successfully.
                Your hold is valid for 10 minutes.

            </div>


            

            <p>

                <strong>
                    Student:
                </strong>

                <?= htmlspecialchars(
                    $hold["student_name"]
                ) ?>

            </p>


            

            <p>

                <strong>
                    Book ID:
                </strong>

                <?= htmlspecialchars(
                    $hold["display_book_id"]
                ) ?>

            </p>


            

            <p>

                <strong>
                    Book:
                </strong>

                <?= htmlspecialchars(
                    $hold["title"]
                ) ?>

            </p>


            

            <p>

                <strong>
                    Hold Time:
                </strong>

                <?= htmlspecialchars(
                    date(
                        "d M Y, h:i A",
                        strtotime($hold["hold_time"])
                    )
                ) ?>

            </p>


            

            <p>

                <strong>
                    Expiry Time:
                </strong>

                <?= htmlspecialchars(
                    date(
                        "d M Y, h:i A",
                        strtotime($hold["expiry_time"])
                    )
                ) ?>

            </p>


            

            <p class="countdown-text">

                Time remaining:

                <strong id="countdown">
                    Calculating...
                </strong>

            </p>


            

            <div class="confirmation-actions">


                

                <form method="POST" action="../index.php" style="display:inline;" >


                    <input type="hidden" name="hold_token" value="<?= htmlspecialchars($token) ?>">

                    <button type="submit" class="btn" > Confirm Activity </button>

                </form>


                

                <a href="../index.php" class="btn secondary" >
                    Back to Books
                </a>


                

                <form method="POST" action="holdcancel.php" style="display:inline;">

                    

                    <input type="hidden" name="hold_token" value="<?= htmlspecialchars($token) ?>">

                    <button type="submit" class="btn danger" onclick="return confirm('Cancel this active hold?')">Cancel Hold  </button>

                </form>


            </div>


            

            <script>

            let remainingSeconds =
                <?= $remainingSeconds ?>;


            const countdown =
                document.getElementById("countdown");


            function updateCountdown() {

                if (remainingSeconds <= 0) {

                    countdown.textContent = "Expired";

                    return;

                }


                const minutes =
                    Math.floor(
                        remainingSeconds / 60
                    );


                const seconds =
                    remainingSeconds % 60;


                countdown.textContent =
                    minutes +
                    " minutes " +
                    String(seconds).padStart(2, "0") +
                    " seconds";


                remainingSeconds--;

            }


            updateCountdown();


            setInterval(
                updateCountdown,
                1000
            );

            </script>


        <?php elseif ($hold["status"] === "BORROWED"): ?>


            <div class="alert success">

                This hold has already been
                converted to a borrowing.

            </div>


        <?php elseif ($hold["status"] === "CANCELLED"): ?>


            <div class="alert error">

                This hold has been cancelled.

            </div>


        <?php else: ?>


            <div class="alert error">

                This hold has expired.

            </div>


        <?php endif; ?>


    </div>

</div>


</body>

</html>