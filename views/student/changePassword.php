<?php

session_start();

if (!isset($_SESSION["userId"]) || !isset($_SESSION["role"])) {
    header("Location: ../../controllers/Sign_in/sign_in.php");
    exit();
}


if ($_SESSION["role"] !== "student") {

    if ($_SESSION["role"] === "admin") {
        header("Location: ../admin/admin.php");
        exit();
    }

    if ($_SESSION["role"] === "faculty") {
        header("Location: ../faculty/faculty.php");
        exit();
    }

    header("Location: ../../controllers/Sign_in/sign_in.php");
    exit();
}

$message = $_GET["msg"] ?? "";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/changePassword.css">

    <title>Change Password | UMS</title>

</head>

<body>

    <h2>Student</h2>


    <nav>

        <a href="studentDashboard.php">Dashboard</a>

        <a href="myCourses.php">My Courses</a>

        <a href="addCourse.php">Add Course</a>

        <a href="dropCourse.php">Drop Course</a>

        <a href="results.php">Results</a>

        <a href="profile.php">Profile</a>

        <a href="changePassword.php">Change Password</a>

        <button type="button" id="logoutBtn">Logout</button>

    </nav>


    <h1>Change Password</h1>


    <?php if ($message !== ""): ?>

        <p>
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>


    <form
        action="../../controllers/changePasswordControls.php"
        method="POST"
    >

        <div>

            <label for="currentPassword">
                Current Password:
            </label>

            <br>

            <input
                type="password"
                id="currentPassword"
                name="currentPassword"
                required
            >

        </div>


        <br>


        <div>

            <label for="newPassword">
                New Password:
            </label>

            <br>

            <input
                type="password"
                id="newPassword"
                name="newPassword"
                minlength="8"
                required
            >

        </div>


        <br>


        <div>

            <label for="confirmPassword">
                Confirm New Password:
            </label>

            <br>

            <input
                type="password"
                id="confirmPassword"
                name="confirmPassword"
                minlength="8"
                required
            >

        </div>


        <br>


        <button type="submit">
            Change Password
        </button>

    </form>


    <script src="js/studentDashboardJs.js"></script>

</body>

</html>