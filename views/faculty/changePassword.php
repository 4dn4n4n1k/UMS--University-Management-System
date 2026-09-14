<?php
session_start();

if(!isset($_SESSION["userId"]) || !isset($_SESSION["role"])){
    header("Location: ../Sign_in/sign_in.php");
    exit();
}

if($_SESSION["role"] == "admin"){
    header("Location: ../admin/admin.php");
    exit();
}

if($_SESSION["role"] == "student"){
    header("Location: ../student/studentDashboard.php");
    exit();
}

require_once "../../models/database.php";

$message = "";
$error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $currentPassword = $_POST["currentPassword"] ?? "";
    $newPassword = $_POST["newPassword"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";

    if($currentPassword === "" || $newPassword === "" || $confirmPassword === ""){
        $error = "All fields are required.";
    }
    elseif(strlen($newPassword) < 6){
        $error = "New password must be at least 6 characters.";
    }
    elseif($newPassword !== $confirmPassword){
        $error = "New passwords do not match.";
    }
    else{

        $conn = dbConnect();

        if(!$conn){
            $error = "Database connection failed.";
        }
        else{

            $sql = "SELECT password FROM users WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);

            if($stmt){

                mysqli_stmt_bind_param($stmt, "i", $_SESSION["userId"]);
                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($result);

                mysqli_stmt_close($stmt);

                if(!$user || !password_verify($currentPassword, $user["password"])){
                    $error = "Current password is incorrect.";
                }
                else{

                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                    $updateSql = "UPDATE users SET password = ? WHERE id = ?";
                    $updateStmt = mysqli_prepare($conn, $updateSql);

                    if($updateStmt){

                        mysqli_stmt_bind_param(
                            $updateStmt,
                            "si",
                            $hashedPassword,
                            $_SESSION["userId"]
                        );

                        if(mysqli_stmt_execute($updateStmt)){
                            $message = "Password changed successfully.";
                        }
                        else{
                            $error = "Failed to change password.";
                        }

                        mysqli_stmt_close($updateStmt);
                    }
                    else{
                        $error = "Failed to prepare password update.";
                    }
                }
            }
            else{
                $error = "Failed to prepare password query.";
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
    <title>Change Password | Faculty</title>
    <link rel="stylesheet" href="../css/faculty.css">
</head>

<body>

<div class="layout">

    <aside class="sidebar">

        <div class="sidebar-header">
            <h2>UMS</h2>
            <p>Faculty Panel</p>
        </div>

        <nav>
            <a href="faculty.php">Dashboard</a>
            <a href="courses.php">Course Management</a>
            <a href="assignments.php">Assignments</a>
            <a href="results.php">Student Results</a>
            <a href="profile.php">Profile</a>
            <a href="changePassword.php" class="active">Change Password</a>
            <a href="../../models/logout.php" class="logout">Logout</a>
        </nav>

    </aside>

    <main class="main">

        <div class="page-header">
            <div>
                <h1>Change Password</h1>
                <p>Update your faculty account password</p>
            </div>
        </div>

        <section class="panel password-panel">

            <?php if($message != ""): ?>
                <div class="success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if($error != ""): ?>
                <div class="error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input
                        type="password"
                        id="currentPassword"
                        name="currentPassword"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input
                        type="password"
                        id="newPassword"
                        name="newPassword"
                        minlength="6"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input
                        type="password"
                        id="confirmPassword"
                        name="confirmPassword"
                        minlength="6"
                        required
                    >
                </div>

                <div class="form-actions">
                    <button type="submit" class="primary-btn">
                        Change Password
                    </button>
                </div>

            </form>

        </section>

    </main>

</div>

</body>
</html>