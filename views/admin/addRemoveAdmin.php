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

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["addAdmin"])) {

        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($name === "" || $email === "" || $username === "" || $password === "") {
            $message = "All fields are required.";
            $messageType = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address.";
            $messageType = "error";
        } elseif (strlen($password) < 8) {
            $message = "Password must be at least 8 characters.";
            $messageType = "error";
        } else {

            $checkSql = "SELECT id FROM users WHERE username = ? OR email = ?";
            $checkStmt = mysqli_prepare($conn, $checkSql);

            mysqli_stmt_bind_param($checkStmt, "ss", $username, $email);
            mysqli_stmt_execute($checkStmt);

            $checkResult = mysqli_stmt_get_result($checkStmt);

            if (mysqli_num_rows($checkResult) > 0) {

                $message = "Username or email already exists.";
                $messageType = "error";

                mysqli_stmt_close($checkStmt);

            } else {

                mysqli_stmt_close($checkStmt);

                mysqli_begin_transaction($conn);

                try {

                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    $userSql = "INSERT INTO users (username, email, password, role)
                                VALUES (?, ?, ?, 'admin')";

                    $userStmt = mysqli_prepare($conn, $userSql);

                    if (!$userStmt) {
                        throw new Exception("Unable to create user account.");
                    }

                    mysqli_stmt_bind_param(
                        $userStmt,
                        "sss",
                        $username,
                        $email,
                        $hashedPassword
                    );

                    if (!mysqli_stmt_execute($userStmt)) {
                        throw new Exception("Unable to create user account.");
                    }

                    mysqli_stmt_close($userStmt);

                    $adminSql = "INSERT INTO admins (username, name)
                                 VALUES (?, ?)";

                    $adminStmt = mysqli_prepare($conn, $adminSql);

                    if (!$adminStmt) {
                        throw new Exception("Unable to create admin profile.");
                    }

                    mysqli_stmt_bind_param(
                        $adminStmt,
                        "ss",
                        $username,
                        $name
                    );

                    if (!mysqli_stmt_execute($adminStmt)) {
                        throw new Exception("Unable to create admin profile.");
                    }

                    mysqli_stmt_close($adminStmt);

                    mysqli_commit($conn);

                    $message = "Admin added successfully.";
                    $messageType = "success";

                } catch (Exception $e) {

                    mysqli_rollback($conn);

                    $message = $e->getMessage();
                    $messageType = "error";
                }
            }
        }
    }

    if (isset($_POST["removeAdmin"])) {

        $adminId = $_POST["adminId"] ?? "";

        if (!ctype_digit($adminId)) {

            $message = "Invalid administrator.";
            $messageType = "error";

        } else {

            $adminId = (int)$adminId;

            $findSql = "SELECT username FROM admins WHERE id = ?";
            $findStmt = mysqli_prepare($conn, $findSql);

            mysqli_stmt_bind_param($findStmt, "i", $adminId);
            mysqli_stmt_execute($findStmt);

            $findResult = mysqli_stmt_get_result($findStmt);
            $admin = mysqli_fetch_assoc($findResult);

            mysqli_stmt_close($findStmt);

            if (!$admin) {

                $message = "Administrator not found.";
                $messageType = "error";

            } else {

                $username = $admin["username"];

                mysqli_begin_transaction($conn);

                try {

                    $deleteAdminSql = "DELETE FROM admins WHERE id = ?";
                    $deleteAdminStmt = mysqli_prepare($conn, $deleteAdminSql);

                    if (!$deleteAdminStmt) {
                        throw new Exception("Unable to remove administrator.");
                    }

                    mysqli_stmt_bind_param(
                        $deleteAdminStmt,
                        "i",
                        $adminId
                    );

                    if (!mysqli_stmt_execute($deleteAdminStmt)) {
                        throw new Exception("Unable to remove administrator.");
                    }

                    mysqli_stmt_close($deleteAdminStmt);

                    $deleteUserSql = "DELETE FROM users WHERE username = ?";
                    $deleteUserStmt = mysqli_prepare($conn, $deleteUserSql);

                    if (!$deleteUserStmt) {
                        throw new Exception("Unable to remove administrator account.");
                    }

                    mysqli_stmt_bind_param(
                        $deleteUserStmt,
                        "s",
                        $username
                    );

                    if (!mysqli_stmt_execute($deleteUserStmt)) {
                        throw new Exception("Unable to remove administrator account.");
                    }

                    mysqli_stmt_close($deleteUserStmt);

                    mysqli_commit($conn);

                    $message = "Admin removed successfully.";
                    $messageType = "success";

                } catch (Exception $e) {

                    mysqli_rollback($conn);

                    $message = $e->getMessage();
                    $messageType = "error";
                }
            }
        }
    }
}

$admins = [];

$adminSql = "SELECT
                admins.id,
                admins.name,
                admins.username,
                users.email
             FROM admins
             INNER JOIN users
                ON admins.username = users.username
             WHERE users.role = 'admin'
             ORDER BY admins.id";

$adminResult = mysqli_query($conn, $adminSql);

if ($adminResult) {
    while ($row = mysqli_fetch_assoc($adminResult)) {
        $admins[] = $row;
    }
}

mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add / Remove Admin | UMS</title>

    <link rel="stylesheet" href="../css/admin.css">

</head>

<body>

    <div class="layout">

        <aside class="sidebar">

            <h2>Admin</h2>

            <nav>

                <a href="admin.php">
                    Dashboard
                </a>

                <a href="addRemoveAdmin.php" class="active">
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

                <h1>Add / Remove Admin</h1>

                <p class="subtitle">
                    Manage administrator accounts.
                </p>


                <?php if ($message !== ""): ?>

                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>

                <?php endif; ?>


                <h2>Add New Admin</h2>


                <form method="POST" action="addRemoveAdmin.php">

                    <div class="form-grid">

                        <div class="form-group">

                            <label for="name">
                                Full Name
                            </label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                placeholder="Enter full name"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label for="email">
                                Email Address
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter email address"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label for="username">
                                Username
                            </label>

                            <input
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Enter username"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label for="password">
                                Password
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter password"
                                required
                            >

                        </div>

                    </div>


                    <button
                        type="submit"
                        name="addAdmin"
                        class="primary-btn"
                    >
                        Add Admin
                    </button>

                </form>


                <h2 class="existing-title">
                    Existing Admins
                </h2>


                <div class="table-container">

                    <table>

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>Full Name</th>

                                <th>Username</th>

                                <th>Email Address</th>

                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if (count($admins) > 0): ?>

                                <?php foreach ($admins as $admin): ?>

                                    <tr>

                                        <td>
                                            <?php echo htmlspecialchars($admin["id"]); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($admin["name"]); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($admin["username"]); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($admin["email"]); ?>
                                        </td>

                                        <td>

                                            <form
                                                method="POST"
                                                action="addRemoveAdmin.php"
                                                onsubmit="return confirm('Remove this administrator?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="adminId"
                                                    value="<?php echo htmlspecialchars($admin["id"]); ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    name="removeAdmin"
                                                    class="delete-btn"
                                                >
                                                    Remove
                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="5">
                                        No administrators found.
                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

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