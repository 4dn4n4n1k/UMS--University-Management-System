<?php

session_start();

if(!isset($_SESSION["userId"]) || !isset($_SESSION["role"])){
    header("Location: ../../controllers/Sign_in/sign_in.php");
    exit();
}

if($_SESSION["role"] !== "faculty"){
    if($_SESSION["role"] === "admin"){
        header("Location: ../admin/admin.php");
        exit();
    }

    if($_SESSION["role"] === "student"){
        header("Location: ../student/studentDashboard.php");
        exit();
    }

    header("Location: ../../controllers/Sign_in/sign_in.php");
    exit();
}

require_once "../../models/database.php";

$conn = dbConnect();

$courseCount = 0;
$assignmentCount = 0;
$resultCount = 0;

if($conn){
    $courseQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM courses");
    $assignmentQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM assignments");
    $resultQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM results");

    if($courseQuery){
        $courseCount = mysqli_fetch_assoc($courseQuery)["total"];
    }

    if($assignmentQuery){
        $assignmentCount = mysqli_fetch_assoc($assignmentQuery)["total"];
    }

    if($resultQuery){
        $resultCount = mysqli_fetch_assoc($resultQuery)["total"];
    }

    mysqli_close($conn);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard | UMS</title>
    <link rel="stylesheet" href="../css/faculty.css">
</head>

<body>

<div class="layout">

    <aside class="sidebar">

        <h2>Faculty</h2>

        <nav>
            <a href="faculty.php" class="active">
                Dashboard
            </a>

            <a href="courses.php">
                Course Management
            </a>

            <a href="assignments.php">
                Assignments
            </a>

            <a href="results.php">
                Student Results
            </a>

            <a href="profile.php">
                Profile
            </a>

            <a href="changePassword.php">
                Change Password
            </a>

            <button type="button" id="logoutBtn" class="logout">
                Logout
            </button>
        </nav>

    </aside>

    <main class="main">

        <h1>Faculty Dashboard</h1>

        <p>Welcome to the University Management System.</p>

        <section class="cards">

            <div class="card">
                <h3>Total Courses</h3>
                <p><?php echo $courseCount; ?></p>
            </div>

            <div class="card">
                <h3>Total Assignments</h3>
                <p><?php echo $assignmentCount; ?></p>
            </div>

            <div class="card">
                <h3>Total Results</h3>
                <p><?php echo $resultCount; ?></p>
            </div>

        </section>

        <section class="panel">

            <h2>Faculty Management</h2>

            <div class="quick-links">

                <a href="courses.php">
                    <span>Course Management</span>
                    <small>Manage course information</small>
                </a>

                <a href="assignments.php">
                    <span>Assignments</span>
                    <small>Create and manage assignments</small>
                </a>

                <a href="results.php">
                    <span>Student Results</span>
                    <small>Manage student results</small>
                </a>

            </div>

        </section>

    </main>

</div>

<script>
document.getElementById("logoutBtn").addEventListener("click", function() {
    window.location.href = "../../models/logout.php";
});
</script>

</body>
</html>