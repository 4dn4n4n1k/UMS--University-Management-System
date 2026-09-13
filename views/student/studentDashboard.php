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


/*
 * Load course model.
 */
require_once "../../models/coursesModel.php";


/*
 * The login system stores users.id in $_SESSION["userId"].
 *
 * coursesModel.php will internally find the corresponding
 * students.id when it needs it.
 */
$studentId = $_SESSION["userId"];


/*
 * Get enrolled course information.
 */
$totalEnrolled = countEnrolled($studentId);
$myCourses = getEnrolledCourses($studentId);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard | UMS</title>
    <link rel="stylesheet" href="../css/profile.css">

</head>

<body>

    <h2>Student</h2>


    <!-- Student Navigation -->

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


    <br>


    <!-- Welcome Section -->

    <h1>
        Welcome Student, <?php echo htmlspecialchars($studentId); ?>
    </h1>


    <!-- Enrolled Course Count -->

    <p>Enrolled Courses</p>

    <p>
        <?php echo $totalEnrolled; ?>
    </p>


    <!-- My Enrolled Courses -->

    <h2>My Enrolled Courses</h2>


    <table border="1">

        <thead>

            <tr>

                <th>Course Name</th>

                <th>Credit</th>

            </tr>

        </thead>


        <tbody>

            <?php

            if ($myCourses && mysqli_num_rows($myCourses) > 0) {

                while ($row = mysqli_fetch_assoc($myCourses)) {

                    echo "<tr>";

                    echo "<td>"
                        . htmlspecialchars($row["courseName"])
                        . "</td>";

                    echo "<td>"
                        . htmlspecialchars($row["credit"])
                        . "</td>";

                    echo "</tr>";
                }

            } else {

                echo "<tr>";

                echo "<td colspan='2'>No enrolled courses.</td>";

                echo "</tr>";
            }

            ?>

        </tbody>

    </table>



    <script src="js/studentDashboardJs.js"></script>

</body>

</html>