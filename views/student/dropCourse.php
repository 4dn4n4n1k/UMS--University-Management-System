<?php
session_start();
if(isset($_SESSION["userId"]) && isset($_SESSION["role"]))
{
    if($_SESSION["role"]=="student")
    {

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

$myCourses=getEnrolledCourses($_SESSION["userId"]);
?>


<!doctype html>
<html>

<head>
    <title>Drop Course</title>
    <link rel="stylesheet" href="../css/student.css">
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
            <div class="panel">
                <h2>Drop Course</h2>
                <span>
                    <?php
                        if(isset($_GET["msg"]))
                            {
                                echo $_GET["msg"];
                            }
                    ?>
                </span>
                <table>
                    <tr>
                        <th>Course Id</th>
                        <th>Course Name</th>
                        <th>Credit</th>
                        <th>Action</th>
                    </tr>
                    <?php
                        while($row=mysqli_fetch_assoc($myCourses))
                        {
                            echo "<tr>";
                            echo "<td>".$row["courseId"]."</td>";
                            echo "<td>".$row["courseName"]."</td>";
                            echo "<td>".$row["credit"]."</td>";
                            echo "<td><a href='../../controllers/dropCourseControls.php?courseId=".$row["courseId"]."'><button class='btnRemove'>Drop</button></a></td>";
                            echo "</tr>";
                        }
                    ?>
                </table>
            </div>
        </div>

    </div>
</body>

</html>