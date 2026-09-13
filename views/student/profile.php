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

require_once "../../models/usersModel.php";


$userId = $_SESSION["userId"];

$user = getUserById($userId);


if ($user === false) {
    die("Unable to load student profile.");
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/profile.css">

    <title>Profile | UMS</title>

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


    <h1>My Profile</h1>


    <table border="1">

        <tr>
            <th>User ID</th>
            <td>
                <?php echo htmlspecialchars($user["userId"]); ?>
            </td>
        </tr>


        <tr>
            <th>Username</th>
            <td>
                <?php echo htmlspecialchars($user["username"]); ?>
            </td>
        </tr>


        <tr>
            <th>Name</th>
            <td>
                <?php echo htmlspecialchars($user["name"]); ?>
            </td>
        </tr>


        <tr>
            <th>Email</th>
            <td>
                <?php echo htmlspecialchars($user["email"]); ?>
            </td>
        </tr>


        <tr>
            <th>Gender</th>
            <td>
                <?php echo htmlspecialchars($user["gender"]); ?>
            </td>
        </tr>


        <tr>
            <th>Date of Birth</th>
            <td>
                <?php echo htmlspecialchars($user["date_of_birth"]); ?>
            </td>
        </tr>


        <tr>
            <th>Role</th>
            <td>
                <?php echo htmlspecialchars($user["role"]); ?>
            </td>
        </tr>

    </table>


    <script src="js/studentDashboardJs.js"></script>

</body>

</html>