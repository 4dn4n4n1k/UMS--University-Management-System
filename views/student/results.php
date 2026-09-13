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


require_once "../../models/resultsModel.php";


$userId = $_SESSION["userId"];

$results = getResultsByStudent($userId);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/results.css">

    <title>Results | UMS</title>

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


    <h1>Results</h1>


    <table border="1">

        <thead>

            <tr>

                <th>Course Name</th>

                <th>Credit</th>

                <th>Marks</th>

                <th>Grade</th>

            </tr>

        </thead>


        <tbody>

            <?php

            if ($results && mysqli_num_rows($results) > 0) {

                while ($row = mysqli_fetch_assoc($results)) {

                    echo "<tr>";

                    echo "<td>"
                        . htmlspecialchars($row["courseName"])
                        . "</td>";

                    echo "<td>"
                        . htmlspecialchars($row["credit"])
                        . "</td>";

                    echo "<td>"
                        . htmlspecialchars($row["marks"])
                        . "</td>";

                    echo "<td>"
                        . htmlspecialchars($row["grade"])
                        . "</td>";

                    echo "</tr>";
                }

            } else {

                echo "<tr>";

                echo "<td colspan='4'>No results available.</td>";

                echo "</tr>";
            }

            ?>

        </tbody>

    </table>


    <script src="js/studentDashboardJs.js"></script>

</body>

</html>