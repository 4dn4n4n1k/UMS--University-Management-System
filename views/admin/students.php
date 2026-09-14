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

    if (isset($_POST["addStudent"])) {

        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        $dateOfBirth = trim($_POST["date_of_birth"] ?? "");
        $gender = trim($_POST["gender"] ?? "");

        if (
            $name === "" ||
            $email === "" ||
            $username === "" ||
            $password === ""
        ) {

            $message = "Name, email, username and password are required.";
            $messageType = "error";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $message = "Please enter a valid email address.";
            $messageType = "error";

        } elseif (strlen($password) < 8) {

            $message = "Password must be at least 8 characters.";
            $messageType = "error";

        } else {

            $checkSql = "SELECT id
                         FROM users
                         WHERE username = ?
                         OR email = ?";

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
                                    VALUES (?, ?, ?, 'student')";

                        $userStmt = mysqli_prepare(
                            $conn,
                            $userSql
                        );

                        if (!$userStmt) {
                            throw new Exception(
                                "Unable to create student account."
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
                                "Unable to create student account."
                            );
                        }

                        mysqli_stmt_close($userStmt);

                        $studentSql = "INSERT INTO students
                                       (username, name, date_of_birth, gender)
                                       VALUES (?, ?, NULLIF(?, ''), NULLIF(?, ''))";

                        $studentStmt = mysqli_prepare(
                            $conn,
                            $studentSql
                        );

                        if (!$studentStmt) {
                            throw new Exception(
                                "Unable to create student profile."
                            );
                        }

                        mysqli_stmt_bind_param(
                            $studentStmt,
                            "ssss",
                            $username,
                            $name,
                            $dateOfBirth,
                            $gender
                        );

                        if (!mysqli_stmt_execute($studentStmt)) {
                            throw new Exception(
                                "Unable to create student profile."
                            );
                        }

                        mysqli_stmt_close($studentStmt);

                        mysqli_commit($conn);

                        $message = "Student added successfully.";
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

    if (isset($_POST["removeStudent"])) {

        $studentId = $_POST["studentId"] ?? "";

        if (!ctype_digit($studentId)) {

            $message = "Invalid student.";
            $messageType = "error";

        } else {

            $studentId = (int)$studentId;

            $findSql = "SELECT username
                        FROM students
                        WHERE id = ?";

            $findStmt = mysqli_prepare(
                $conn,
                $findSql
            );

            if (!$findStmt) {

                $message = "Something went wrong.";
                $messageType = "error";

            } else {

                mysqli_stmt_bind_param(
                    $findStmt,
                    "i",
                    $studentId
                );

                mysqli_stmt_execute($findStmt);

                $findResult = mysqli_stmt_get_result($findStmt);

                $student = mysqli_fetch_assoc($findResult);

                mysqli_stmt_close($findStmt);

                if (!$student) {

                    $message = "Student not found.";
                    $messageType = "error";

                } else {

                    $username = $student["username"];

                    $checkResultsSql = "SELECT id
                                        FROM results
                                        WHERE student_id = ?
                                        LIMIT 1";

                    $checkResultsStmt = mysqli_prepare(
                        $conn,
                        $checkResultsSql
                    );

                    $hasResults = false;

                    if ($checkResultsStmt) {

                        mysqli_stmt_bind_param(
                            $checkResultsStmt,
                            "i",
                            $studentId
                        );

                        mysqli_stmt_execute($checkResultsStmt);

                        $resultsCheck = mysqli_stmt_get_result(
                            $checkResultsStmt
                        );

                        $hasResults = mysqli_num_rows(
                            $resultsCheck
                        ) > 0;

                        mysqli_stmt_close($checkResultsStmt);
                    }

                    $checkEnrollmentsSql = "SELECT id
                                            FROM enrollments
                                            WHERE student_id = ?
                                            LIMIT 1";

                    $checkEnrollmentsStmt = mysqli_prepare(
                        $conn,
                        $checkEnrollmentsSql
                    );

                    $hasEnrollments = false;

                    if ($checkEnrollmentsStmt) {

                        mysqli_stmt_bind_param(
                            $checkEnrollmentsStmt,
                            "i",
                            $studentId
                        );

                        mysqli_stmt_execute(
                            $checkEnrollmentsStmt
                        );

                        $enrollmentCheck = mysqli_stmt_get_result(
                            $checkEnrollmentsStmt
                        );

                        $hasEnrollments = mysqli_num_rows(
                            $enrollmentCheck
                        ) > 0;

                        mysqli_stmt_close(
                            $checkEnrollmentsStmt
                        );
                    }

                    if ($hasResults || $hasEnrollments) {

                        $message = "This student cannot be removed because academic records exist.";
                        $messageType = "error";

                    } else {

                        mysqli_begin_transaction($conn);

                        try {

                            $deleteStudentSql = "DELETE FROM students
                                                 WHERE id = ?";

                            $deleteStudentStmt = mysqli_prepare(
                                $conn,
                                $deleteStudentSql
                            );

                            if (!$deleteStudentStmt) {
                                throw new Exception(
                                    "Unable to remove student."
                                );
                            }

                            mysqli_stmt_bind_param(
                                $deleteStudentStmt,
                                "i",
                                $studentId
                            );

                            if (!mysqli_stmt_execute(
                                $deleteStudentStmt
                            )) {
                                throw new Exception(
                                    "Unable to remove student."
                                );
                            }

                            mysqli_stmt_close(
                                $deleteStudentStmt
                            );

                            $deleteUserSql = "DELETE FROM users
                                              WHERE username = ?";

                            $deleteUserStmt = mysqli_prepare(
                                $conn,
                                $deleteUserSql
                            );

                            if (!$deleteUserStmt) {
                                throw new Exception(
                                    "Unable to remove student account."
                                );
                            }

                            mysqli_stmt_bind_param(
                                $deleteUserStmt,
                                "s",
                                $username
                            );

                            if (!mysqli_stmt_execute(
                                $deleteUserStmt
                            )) {
                                throw new Exception(
                                    "Unable to remove student account."
                                );
                            }

                            mysqli_stmt_close(
                                $deleteUserStmt
                            );

                            mysqli_commit($conn);

                            $message = "Student removed successfully.";
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
}

$students = [];

$studentSql = "SELECT
                   students.id,
                   students.name,
                   students.username,
                   students.date_of_birth,
                   students.gender,
                   users.email
               FROM students
               INNER JOIN users
                   ON students.username = users.username
               WHERE users.role = 'student'
               ORDER BY students.id";

$studentResult = mysqli_query(
    $conn,
    $studentSql
);

if ($studentResult) {

    while ($row = mysqli_fetch_assoc($studentResult)) {
        $students[] = $row;
    }
}

mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Accounts | UMS</title>

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

                <a href="students.php" class="active">
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

                <h1>Student Accounts</h1>

                <p class="subtitle">
                    Manage student accounts.
                </p>


                <?php if ($message !== ""): ?>

                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>

                <?php endif; ?>


                <h2>Add New Student</h2>


                <form
                    method="POST"
                    action="students.php"
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

                            <label for="date_of_birth">
                                Date of Birth
                            </label>

                            <input
                                type="date"
                                id="date_of_birth"
                                name="date_of_birth"
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
                        name="addStudent"
                        class="primary-btn"
                    >
                        Add Student
                    </button>

                </form>


                <h2 class="existing-title">
                    Existing Students
                </h2>


                <div class="table-container">

                    <table>

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>Full Name</th>

                                <th>Username</th>

                                <th>Email Address</th>

                                <th>Date of Birth</th>

                                <th>Gender</th>

                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if (count($students) > 0): ?>

                                <?php foreach ($students as $student): ?>

                                    <tr>

                                        <td>
                                            <?php echo htmlspecialchars($student["id"]); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($student["name"]); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($student["username"]); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($student["email"]); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($student["date_of_birth"] ?: "N/A"); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($student["gender"] ?: "N/A"); ?>
                                        </td>

                                        <td>

                                            <form
                                                method="POST"
                                                action="students.php"
                                                onsubmit="return confirm('Remove this student account?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="studentId"
                                                    value="<?php echo htmlspecialchars($student["id"]); ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    name="removeStudent"
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

                                    <td colspan="7">
                                        No students found.
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