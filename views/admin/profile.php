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

$userId = $_SESSION["userId"];
$conn = dbConnect();

$admin = false;

if ($conn) {

    $sql = "SELECT
                users.id AS userId,
                users.username AS username,
                users.email AS email,
                users.role AS role,
                admins.name AS name
            FROM users
            INNER JOIN admins
                ON users.username = admins.username
            WHERE users.id = ?
            AND users.role = 'admin'";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $admin = mysqli_fetch_assoc($result);
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}

if (!$admin) {
    header("Location: admin.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profile | UMS</title>

    <link rel="stylesheet" href="../css/profile.css">

</head>

<body>

    <div class="layout">

        <aside class="sidebar">

            <h2>Admin</h2>

            <nav>

                <a href="admin.php">
                    Dashboard
                </a>

                <a href="addRemoveAdmin.php">
                    Add / Remove Admin
                </a>

                <a href="addRemoveFaculty.php">
                    Add / Remove Faculty
                </a>

                <a href="profile.php" class="active">
                    Profile
                </a>

                <a href="changePassword.php">
                    Change Password
                </a>

                <button
                    type="button"
                    id="logoutBtn"
                >
                    Logout
                </button>

            </nav>

        </aside>


        <main class="main">

            <section class="profile-panel">

                <h1>My Profile</h1>

                <div class="profile-body">

                    <table>

                        <tr>

                            <th>User ID</th>

                            <td>
                                <?php echo htmlspecialchars($admin["userId"]); ?>
                            </td>

                        </tr>

                        <tr>

                            <th>Username</th>

                            <td>
                                <?php echo htmlspecialchars($admin["username"]); ?>
                            </td>

                        </tr>

                        <tr>

                            <th>Name</th>

                            <td>
                                <?php echo htmlspecialchars($admin["name"]); ?>
                            </td>

                        </tr>

                        <tr>

                            <th>Email</th>

                            <td>
                                <?php echo htmlspecialchars($admin["email"]); ?>
                            </td>

                        </tr>

                        <tr>

                            <th>Role</th>

                            <td>
                                <?php echo htmlspecialchars($admin["role"]); ?>
                            </td>

                        </tr>

                    </table>

                </div>

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