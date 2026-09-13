<?php
session_start();
if(isset($_SESSION["userId"]) && isset($_SESSION["role"]))
{
    if($_SESSION["role"]=="student")
    {

    }
    else if($_SESSION["role"]=="admin")
    {
        header("Location: ../admin/adminDashboard.php");
    }
    else if($_SESSION["role"]=="faculty")
    {
        header("Location: ../faculty/facultyDashboard.php");
    }
    else
    {
        header("Location: ../login.php");
    }
}

else
{
    header("Location: ../login.php");
}

require_once "../../models/coursesModel.php";

$studentId=$_SESSION["userId"];
$totalEnrolled=countEnrolled($studentId);
$myCourses=getEnrolledCourses($studentId);
?>


<!doctype html>
<html>

<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="js/studentDashboardJs.js" defer></script>
</head>

<body>
    <div class="layout">

        <div class="sidebar">
            <h3 class="roleTitle roleStudent">Student</h3>
            <a href="studentDashboard.php">Dashboard</a>
            <a href="myCourses.php">My Courses</a>
            <a href="addCourse.php">Add Course</a>
            <a href="dropCourse.php">Drop Course</a>
            <a href="results.php">Results</a>
            <a href="profile.php">Profile</a>
            <a href="changePassword.php">Change Password</a>
            <button id="logoutBtn" class="logoutBtn">Logout</button>
        </div>

        <div class="main">
            <h1>Welcome Student, <?php echo $_SESSION["userId"]; ?></h1>

            <div class="cards">
                <div class="card">
                    <div class="label">Enrolled Courses</div>
                    <div class="value"><?php echo $totalEnrolled; ?></div>
                </div>
            </div>

            <div class="panel">
                <h2>My Enrolled Courses</h2>
                <table>
                    <tr>
                        <th>Course Name</th>
                        <th>Credit</th>
                        <th>Faculty</th>
                    </tr>
                    <?php
                        while($row=mysqli_fetch_assoc($myCourses))
                        {
                            echo "<tr>";
                            echo "<td>".$row["courseName"]."</td>";
                            echo "<td>".$row["credit"]."</td>";
                            echo "<td>".$row["facultyName"]."</td>";
                            echo "</tr>";
                        }
                    ?>
                </table>
            </div>
        </div>

    </div>
</body>

</html>