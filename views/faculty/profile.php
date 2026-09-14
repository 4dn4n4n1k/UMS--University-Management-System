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

$profile = null;
$error = "";

if(!$conn){
    $error = "Database connection failed.";
}
else{

    $userId = intval($_SESSION["userId"]);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            users.id AS user_id,
            users.username,
            users.email,
            users.role,
            faculty.id AS faculty_id,
            faculty.name,
            faculty.gender,
            faculty.profile_image
         FROM users
         INNER JOIN faculty
            ON users.username = faculty.username
         WHERE users.id = ?"
    );

    if($stmt){

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $userId
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) > 0){
            $profile = mysqli_fetch_assoc($result);
        }
        else{
            $error = "Faculty profile could not be found.";
        }

        mysqli_stmt_close($stmt);

    }
    else{
        $error = "Unable to load faculty profile.";
    }

    mysqli_close($conn);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | UMS Faculty</title>
    <link rel="stylesheet" href="../css/faculty.css">
</head>

<body>

<div class="layout">

    <aside class="sidebar">

        <h2>Faculty</h2>

        <nav>

            <a href="faculty.php">
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

            <a href="profile.php" class="active">
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

        <h1>My Profile</h1>

        <p>View your faculty account information.</p>

        <?php if($error !== ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php elseif($profile !== null): ?>

            <section class="profile-panel">

                <div class="profile-header">

                    <?php if(!empty($profile["profile_image"])): ?>

                        <img
                            src="../../<?php echo htmlspecialchars($profile["profile_image"]); ?>"
                            alt="Profile Image"
                            class="profile-image"
                        >

                    <?php else: ?>

                        <div class="profile-placeholder">
                            <?php echo strtoupper(substr($profile["name"], 0, 1)); ?>
                        </div>

                    <?php endif; ?>

                    <div>
                        <h2><?php echo htmlspecialchars($profile["name"]); ?></h2>
                        <p>Faculty</p>
                    </div>

                </div>

                <div class="profile-details">

                    <div class="profile-row">
                        <span>User ID</span>
                        <strong><?php echo $profile["user_id"]; ?></strong>
                    </div>

                    <div class="profile-row">
                        <span>Faculty ID</span>
                        <strong><?php echo $profile["faculty_id"]; ?></strong>
                    </div>

                    <div class="profile-row">
                        <span>Username</span>
                        <strong><?php echo htmlspecialchars($profile["username"]); ?></strong>
                    </div>

                    <div class="profile-row">
                        <span>Name</span>
                        <strong><?php echo htmlspecialchars($profile["name"]); ?></strong>
                    </div>

                    <div class="profile-row">
                        <span>Email</span>
                        <strong><?php echo htmlspecialchars($profile["email"]); ?></strong>
                    </div>

                    <div class="profile-row">
                        <span>Gender</span>
                        <strong><?php echo htmlspecialchars($profile["gender"]); ?></strong>
                    </div>

                    <div class="profile-row">
                        <span>Role</span>
                        <strong><?php echo htmlspecialchars($profile["role"]); ?></strong>
                    </div>

                </div>

            </section>

        <?php endif; ?>

    </main>

</div>

<script>
document.getElementById("logoutBtn").addEventListener("click", function() {
    window.location.href = "../../models/logout.php";
});
</script>

</body>
</html>