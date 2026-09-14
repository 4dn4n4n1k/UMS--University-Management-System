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

$message = "";
$error = "";
$editAssignment = null;

if(!$conn){
    $error = "Database connection failed.";
}
else{

    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_assignment"])){

        $courseId = intval($_POST["course_id"]);
        $title = trim($_POST["title"]);
        $marks = intval($_POST["marks"]);

        if($courseId <= 0 || $title === "" || $marks <= 0){
            $error = "Please provide valid assignment information.";
        }
        else{

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO assignments (course_id, title, marks)
                 VALUES (?, ?, ?)"
            );

            if($stmt){

                mysqli_stmt_bind_param(
                    $stmt,
                    "isi",
                    $courseId,
                    $title,
                    $marks
                );

                if(mysqli_stmt_execute($stmt)){
                    $message = "Assignment created successfully.";
                }
                else{
                    $error = "Unable to create assignment.";
                }

                mysqli_stmt_close($stmt);
            }
            else{
                $error = "Unable to prepare the assignment.";
            }
        }
    }

    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_assignment"])){

        $assignmentId = intval($_POST["assignment_id"]);
        $courseId = intval($_POST["course_id"]);
        $title = trim($_POST["title"]);
        $marks = intval($_POST["marks"]);

        if($assignmentId <= 0 || $courseId <= 0 || $title === "" || $marks <= 0){
            $error = "Please provide valid assignment information.";
        }
        else{

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE assignments
                 SET course_id = ?, title = ?, marks = ?
                 WHERE id = ?"
            );

            if($stmt){

                mysqli_stmt_bind_param(
                    $stmt,
                    "isii",
                    $courseId,
                    $title,
                    $marks,
                    $assignmentId
                );

                if(mysqli_stmt_execute($stmt)){
                    $message = "Assignment updated successfully.";
                }
                else{
                    $error = "Unable to update assignment.";
                }

                mysqli_stmt_close($stmt);
            }
            else{
                $error = "Unable to prepare the update.";
            }
        }
    }

    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_assignment"])){

        $assignmentId = intval($_POST["assignment_id"]);

        if($assignmentId <= 0){
            $error = "Invalid assignment.";
        }
        else{

            $stmt = mysqli_prepare(
                $conn,
                "DELETE FROM assignments WHERE id = ?"
            );

            if($stmt){

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $assignmentId
                );

                if(mysqli_stmt_execute($stmt)){
                    $message = "Assignment deleted successfully.";
                }
                else{
                    $error = "Unable to delete assignment.";
                }

                mysqli_stmt_close($stmt);
            }
            else{
                $error = "Unable to prepare the delete operation.";
            }
        }
    }

    if(isset($_GET["edit"])){

        $assignmentId = intval($_GET["edit"]);

        if($assignmentId > 0){

            $stmt = mysqli_prepare(
                $conn,
                "SELECT id, course_id, title, marks
                 FROM assignments
                 WHERE id = ?"
            );

            if($stmt){

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $assignmentId
                );

                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);

                if(mysqli_num_rows($result) > 0){
                    $editAssignment = mysqli_fetch_assoc($result);
                }

                mysqli_stmt_close($stmt);
            }
        }
    }

    $courses = mysqli_query(
        $conn,
        "SELECT
            courses.id,
            courses.course_name,
            departments.code AS department_code
         FROM courses
         INNER JOIN departments
            ON courses.department_id = departments.id
         ORDER BY courses.id"
    );

    $assignments = mysqli_query(
        $conn,
        "SELECT
            assignments.id,
            assignments.course_id,
            assignments.title,
            assignments.marks,
            courses.course_name,
            departments.code AS department_code
         FROM assignments
         INNER JOIN courses
            ON assignments.course_id = courses.id
         INNER JOIN departments
            ON courses.department_id = departments.id
         ORDER BY assignments.id DESC"
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments | UMS</title>
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

            <a href="assignments.php" class="active">
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

        <h1>Assignment Management</h1>

        <p>Create and manage course assignments.</p>

        <?php if($message !== ""): ?>

            <div class="success">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>

        <?php if($error !== ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <section class="panel">

            <?php if($editAssignment === null): ?>

                <h2>Create New Assignment</h2>

                <form method="POST" action="assignments.php">

                    <label for="course_id">
                        Course
                    </label>

                    <select
                        id="course_id"
                        name="course_id"
                        required
                    >

                        <option value="">
                            Select Course
                        </option>

                        <?php if($courses): ?>

                            <?php while($course = mysqli_fetch_assoc($courses)): ?>

                                <option value="<?php echo $course["id"]; ?>">
                                    <?php echo htmlspecialchars($course["department_code"] . " - " . $course["course_name"]); ?>
                                </option>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </select>

                    <label for="title">
                        Assignment Title
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        maxlength="255"
                        required
                    >

                    <label for="marks">
                        Marks
                    </label>

                    <input
                        type="number"
                        id="marks"
                        name="marks"
                        min="1"
                        required
                    >

                    <button type="submit" name="add_assignment">
                        Create Assignment
                    </button>

                </form>

            <?php else: ?>

                <h2>Edit Assignment</h2>

                <form method="POST" action="assignments.php">

                    <input
                        type="hidden"
                        name="assignment_id"
                        value="<?php echo $editAssignment["id"]; ?>"
                    >

                    <label for="course_id">
                        Course
                    </label>

                    <select
                        id="course_id"
                        name="course_id"
                        required
                    >

                        <?php if($courses): ?>

                            <?php while($course = mysqli_fetch_assoc($courses)): ?>

                                <option
                                    value="<?php echo $course["id"]; ?>"
                                    <?php echo ($course["id"] == $editAssignment["course_id"]) ? "selected" : ""; ?>
                                >
                                    <?php echo htmlspecialchars($course["department_code"] . " - " . $course["course_name"]); ?>
                                </option>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </select>

                    <label for="title">
                        Assignment Title
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        maxlength="255"
                        value="<?php echo htmlspecialchars($editAssignment["title"]); ?>"
                        required
                    >

                    <label for="marks">
                        Marks
                    </label>

                    <input
                        type="number"
                        id="marks"
                        name="marks"
                        min="1"
                        value="<?php echo $editAssignment["marks"]; ?>"
                        required
                    >

                    <div class="form-actions">

                        <button type="submit" name="update_assignment">
                            Update Assignment
                        </button>

                        <a href="assignments.php" class="cancel-btn">
                            Cancel
                        </a>

                    </div>

                </form>

            <?php endif; ?>

        </section>

        <section class="panel">

            <h2>Existing Assignments</h2>

            <?php if($assignments && mysqli_num_rows($assignments) > 0): ?>

                <div class="table-container">

                    <table>

                        <thead>

                            <tr>
                                <th>ID</th>
                                <th>Course</th>
                                <th>Department</th>
                                <th>Assignment</th>
                                <th>Marks</th>
                                <th>Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php while($assignment = mysqli_fetch_assoc($assignments)): ?>

                                <tr>

                                    <td>
                                        <?php echo $assignment["id"]; ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($assignment["course_name"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($assignment["department_code"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($assignment["title"]); ?>
                                    </td>

                                    <td>
                                        <?php echo $assignment["marks"]; ?>
                                    </td>

                                    <td>

                                        <a
                                            href="assignments.php?edit=<?php echo $assignment["id"]; ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="assignments.php"
                                            class="delete-form"
                                            onsubmit="return confirm('Are you sure you want to delete this assignment?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="assignment_id"
                                                value="<?php echo $assignment["id"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="delete_assignment"
                                                class="delete-btn"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-message">
                    No assignments have been created yet.
                </div>

            <?php endif; ?>

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

<?php

if(isset($conn) && $conn){
    mysqli_close($conn);
}

?>