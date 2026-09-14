<?php

session_start();

if (!isset($_SESSION["userId"]) || !isset($_SESSION["role"])) {
    header("Location: ../../controllers/Sign_in/sign_in.php");
    exit();
}

if ($_SESSION["role"] !== "admin") {
    if ($_SESSION["role"] === "student") {
        header("Location: ../student/studentDashboard.php");
        exit();
    }

    if ($_SESSION["role"] === "faculty") {
        header("Location: ../faculty/faculty.php");
        exit();
    }

    header("Location: ../../controllers/Sign_in/sign_in.php");
    exit();
}

require_once "../../models/database.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $currentPassword = $_POST["currentPassword"] ?? "";
    $newPassword = $_POST["newPassword"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";

    if (
        $currentPassword === "" ||
        $newPassword === "" ||
        $confirmPassword === ""
    ) {

        $message = "All fields are required.";
        $messageType = "error";

    } elseif ($newPassword !== $confirmPassword) {

        $message = "New passwords do not match.";
        $messageType = "error";

    } elseif (strlen($newPassword) < 8) {

        $message = "New password must be at least 8 characters.";
        $messageType = "error";

    } else {

        $conn = dbConnect();

        if (!$conn) {

            $message = "Database connection failed.";
            $messageType = "error";

        } else {

            $userId = $_SESSION["userId"];

            $sql = "SELECT password
                    FROM users
                    WHERE id = ?
                    AND role = 'admin'";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {

                $message = "Something went wrong.";
                $messageType = "error";

            } else {

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $userId
                );

                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) === 0) {

                    $message = "Admin account not found.";
                    $messageType = "error";

                } else {

                    $user = mysqli_fetch_assoc($result);

                    if (!password_verify(
                        $currentPassword,
                        $user["password"]
                    )) {

                        $message = "Current password is incorrect.";
                        $messageType = "error";

                    } else {

                        $hashedPassword = password_hash(
                            $newPassword,
                            PASSWORD_DEFAULT
                        );

                        $updateSql = "UPDATE users
                                      SET password = ?
                                      WHERE id = ?
                                      AND role = 'admin'";

                        $updateStmt = mysqli_prepare(
                            $conn,
                            $updateSql
                        );

                        if (!$updateStmt) {

                            $message = "Unable to update password.";
                            $messageType = "error";

                        } else {

                            mysqli_stmt_bind_param(
                                $updateStmt,
                                "si",
                                $hashedPassword,
                                $userId
                            );

                            if (mysqli_stmt_execute($updateStmt)) {

                                $message = "Password changed successfully.";
                                $messageType = "success";

                            } else {

                                $message = "Unable to update password.";
                                $messageType = "error";
                            }

                            mysqli_stmt_close($updateStmt);
                        }
                    }
                }

                mysqli_stmt_close($stmt);
            }

            mysqli_close($conn);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Change Password | UMS</title>

    <link rel="stylesheet" href="../css/admin.css">

</head>

<body>

    <div class="layout">

        <aside class="sidebar">

            <h2>Admin</h2>

            <nav>

                <a href="admin.php">
                    Dashboard
                </a>

                <a href="addRemoveAdmin.php">
                    Add / Remove Admin
                </a>

                <a href="addRemoveFaculty.php">
                    Add / Remove Faculty
                </a>

                <a href="profile.php">
                    Profile
                </a>

                <a href="changePassword.php" class="active">
                    Change Password
                </a>

                <button
                    type="button"
                    id="logoutBtn"
                >
                    Logout
                </button>

            </nav>

        </aside>


        <main class="main">

            <section class="password-panel">

                <h1>Change Password</h1>

                <?php if ($message !== ""): ?>

                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>

                <?php endif; ?>


                <form
                    method="POST"
                    action="changePassword.php"
                >

                    <div class="form-group">

                        <label for="currentPassword">
                            Current Password
                        </label>

                        <input
                            type="password"
                            id="currentPassword"
                            name="currentPassword"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="newPassword">
                            New Password
                        </label>

                        <input
                            type="password"
                            id="newPassword"
                            name="newPassword"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="confirmPassword">
                            Confirm New Password
                        </label>

                        <input
                            type="password"
                            id="confirmPassword"
                            name="confirmPassword"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        class="primary-btn"
                    >
                        Change Password
                    </button>

                </form>

            </section>

        </main>

    </div>


    <script>

        const logoutBtn = document.getElementById("logoutBtn");

        if (logoutBtn) {

            logoutBtn.addEventListener("click", function () {

                window.location.href = "../../models/logout.php";

            });

        }

    </script>

</body>

</html>