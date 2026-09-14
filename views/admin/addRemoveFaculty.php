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

    if (isset($_POST["addFaculty"])) {

        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        $gender = trim($_POST["gender"] ?? "");

        if (
            $name === "" ||
            $email === "" ||
            $username === "" ||
            $password === ""
        ) {
            $message = "All required fields must be filled.";
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

            if (!$checkStmt) {
                $message = "Something went wrong.";
                $messageType = "error";
            } else {

                mysqli_stmt_bind_param(
                    $checkStmt,
                    "ss",
                    $username,
                    $email
                );

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

                        $hashedPassword = password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );

                        $userSql = "INSERT INTO users
                                    (username, email, password, role)
                                    VALUES (?, ?, ?, 'faculty')";

                        $userStmt = mysqli_prepare($conn, $userSql);

                        if (!$userStmt) {
                            throw new Exception(
                                "Unable to create faculty account."
                            );
                        }

                        mysqli_stmt_bind_param(
                            $userStmt,
                            "sss",
                            $username,
                            $email,
                            $hashedPassword
                        );

                        if (!mysqli_stmt_execute($userStmt)) {
                            throw new Exception(
                                "Unable to create faculty account."
                            );
                        }

                        mysqli_stmt_close($userStmt);

                        $facultySql = "INSERT INTO faculty
                                       (username, name, gender)
                                       VALUES (?, ?, ?)";

                        $facultyStmt = mysqli_prepare(
                            $conn,
                            $facultySql
                        );

                        if (!$facultyStmt) {
                            throw new Exception(
                                "Unable to create faculty profile."
                            );
                        }

                        mysqli_stmt_bind_param(
                            $facultyStmt,
                            "sss",
                            $username,
                            $name,
                            $gender
                        );

                        if (!mysqli_stmt_execute($facultyStmt)) {
                            throw new Exception(
                                "Unable to create faculty profile."
                            );
                        }

                        mysqli_stmt_close($facultyStmt);

                        mysqli_commit($conn);

                        $message = "Faculty member added successfully.";
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

    if (isset($_POST["removeFaculty"])) {

        $facultyId = $_POST["facultyId"] ?? "";

        if (!ctype_digit($facultyId)) {

            $message = "Invalid faculty member.";
            $messageType = "error";

        } else {

            $facultyId = (int)$facultyId;

            $findSql = "SELECT username
                        FROM faculty
                        WHERE id = ?";

            $findStmt = mysqli_prepare($conn, $findSql);

            if (!$findStmt) {

                $message = "Something went wrong.";
                $messageType = "error";

            } else {

                mysqli_stmt_bind_param(
                    $findStmt,
                    "i",
                    $facultyId
                );

                mysqli_stmt_execute($findStmt);

                $findResult = mysqli_stmt_get_result($findStmt);
                $faculty = mysqli_fetch_assoc($findResult);

                mysqli_stmt_close($findStmt);

                if (!$faculty) {

                    $message = "Faculty member not found.";
                    $messageType = "error";

                } else {

                    $username = $faculty["username"];

                    mysqli_begin_transaction($conn);

                    try {

                        $deleteFacultySql = "DELETE FROM faculty
                                             WHERE id = ?";

                        $deleteFacultyStmt = mysqli_prepare(
                            $conn,
                            $deleteFacultySql
                        );

                        if (!$deleteFacultyStmt) {
                            throw new Exception(
                                "Unable to remove faculty member."
                            );
                        }

                        mysqli_stmt_bind_param(
                            $deleteFacultyStmt,
                            "i",
                            $facultyId
                        );

                        if (!mysqli_stmt_execute($deleteFacultyStmt)) {
                            throw new Exception(
                                "Unable to remove faculty member."
                            );
                        }

                        mysqli_stmt_close($deleteFacultyStmt);

                        $deleteUserSql = "DELETE FROM users
                                          WHERE username = ?";

                        $deleteUserStmt = mysqli_prepare(
                            $conn,
                            $deleteUserSql
                        );

                        if (!$deleteUserStmt) {
                            throw new Exception(
                                "Unable to remove faculty account."
                            );
                        }

                        mysqli_stmt_bind_param(
                            $deleteUserStmt,
                            "s",
                            $username
                        );

                        if (!mysqli_stmt_execute($deleteUserStmt)) {
                            throw new Exception(
                                "Unable to remove faculty account."
                            );
                        }

                        mysqli_stmt_close($deleteUserStmt);

                        mysqli_commit($conn);

                        $message = "Faculty member removed successfully.";
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
}

$facultyMembers = [];

$facultySql = "SELECT
                   faculty.id,
                   faculty.name,
                   faculty.username,
                   faculty.gender,
                   users.email
               FROM faculty
               INNER JOIN users
                   ON faculty.username = users.username
               WHERE users.role = 'faculty'
               ORDER BY faculty.id";

$facultyResult = mysqli_query($conn, $facultySql);

if ($facultyResult) {

    while ($row = mysqli_fetch_assoc($facultyResult)) {
        $facultyMembers[] = $row;
    }
}

mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add / Remove Faculty | UMS</title>

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

                <a href="addRemoveAdmin.php">
                    Add / Remove Admin
                </a>

                <a href="addRemoveFaculty.php" class="active">
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

                <h1>Add / Remove Faculty</h1>

                <p class="subtitle">
                    Manage faculty members.
                </p>


                <?php if ($message !== ""): ?>

                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>

                <?php endif; ?>


                <h2>Add New Faculty</h2>


                <form
                    method="POST"
                    action="addRemoveFaculty.php"
                >

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


                        <div class="form-group">

                            <label for="gender">
                                Gender
                            </label>

                            <select
                                id="gender"
                                name="gender"
                            >

                                <option value="">
                                    Select gender
                                </option>

                                <option value="Male">
                                    Male
                                </option>

                                <option value="Female">
                                    Female
                                </option>

                                <option value="Other">
                                    Other
                                </option>

                            </select>

                        </div>

                    </div>


                    <button
                        type="submit"
                        name="addFaculty"
                        class="primary-btn"
                    >
                        Add Faculty
                    </button>

                </form>


                <h2 class="existing-title">
                    Existing Faculty
                </h2>


                <div class="table-container">

                    <table>

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>Full Name</th>

                                <th>Username</th>

                                <th>Email Address</th>

                                <th>Gender</th>

                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if (count($facultyMembers) > 0): ?>

                                <?php foreach ($facultyMembers as $faculty): ?>

                                    <tr>

                                        <td>
                                            <?php echo htmlspecialchars($faculty["id"]); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($faculty["name"]); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($faculty["username"]); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($faculty["email"]); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($faculty["gender"] ?: "N/A"); ?>
                                        </td>

                                        <td>

                                            <form
                                                method="POST"
                                                action="addRemoveFaculty.php"
                                                onsubmit="return confirm('Remove this faculty member?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="facultyId"
                                                    value="<?php echo htmlspecialchars($faculty["id"]); ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    name="removeFaculty"
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

                                    <td colspan="6">
                                        No faculty members found.
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