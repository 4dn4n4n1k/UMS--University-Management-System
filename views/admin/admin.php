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

$conn = dbConnect();

$totalStudents = 0;
$totalFaculty = 0;

if ($conn) {
    $studentResult = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM students"
    );

    if ($studentResult) {
        $studentRow = mysqli_fetch_assoc($studentResult);
        $totalStudents = (int)$studentRow["total"];
    }

    $facultyResult = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM faculty"
    );

    if ($facultyResult) {
        $facultyRow = mysqli_fetch_assoc($facultyResult);
        $totalFaculty = (int)$facultyRow["total"];
    }

    mysqli_close($conn);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | UMS</title>

    <link rel="stylesheet" href="../css/admin.css">

</head>

<body>

    <div class="layout">

        <aside class="sidebar">

            <h2>Admin</h2>

            <nav>

                <a href="admin.php" class="active">
                    Dashboard
                </a>
                <a href="students.php">
                    Student Accounts
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

                <a href="changePassword.php">
                    Change Password
                </a>

                <button
                    type="button"
                    id="logoutBtn"
                    class="logout"
                >
                    Logout
                </button>

            </nav>

        </aside>


        <main class="main">

            <section class="panel">

                <h1>Welcome Back, Admin</h1>


                <div class="cards">

                    <div class="card">

                        <h3>Total Students</h3>

                        <p>
                            <?php echo $totalStudents; ?>
                        </p>

                    </div>


                    <div class="card">

                        <h3>Total Faculty</h3>

                        <p>
                            <?php echo $totalFaculty; ?>
                        </p>

                    </div>

                </div>


                <h2>System Overview</h2>


                <table>

                    <thead>

                        <tr>

                            <th>Category</th>

                            <th>Total</th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr>

                            <td>Students</td>

                            <td>
                                <?php echo $totalStudents; ?>
                            </td>

                        </tr>

                        <tr>

                            <td>Faculty</td>

                            <td>
                                <?php echo $totalFaculty; ?>
                            </td>

                        </tr>

                    </tbody>

                </table>

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