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
$editCourse = null;

if(!$conn){
    $error = "Database connection failed.";
}
else{

    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_course"])){

        $courseId = intval($_POST["course_id"]);
        $courseName = trim($_POST["course_name"]);
        $credit = intval($_POST["credit"]);
        $departmentId = intval($_POST["department_id"]);

        if($courseId <= 0 || $courseName === "" || $credit <= 0 || $departmentId <= 0){
            $error = "Please provide valid course information.";
        }
        else{

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE courses
                 SET course_name = ?, credit = ?, department_id = ?
                 WHERE id = ?"
            );

            if($stmt){
                mysqli_stmt_bind_param(
                    $stmt,
                    "siii",
                    $courseName,
                    $credit,
                    $departmentId,
                    $courseId
                );

                if(mysqli_stmt_execute($stmt)){
                    $message = "Course information updated successfully.";
                }
                else{
                    $error = "Unable to update course information.";
                }

                mysqli_stmt_close($stmt);
            }
            else{
                $error = "Unable to prepare the update.";
            }
        }
    }

    if(isset($_GET["edit"])){
        $courseId = intval($_GET["edit"]);

        if($courseId > 0){

            $stmt = mysqli_prepare(
                $conn,
                "SELECT id, course_name, credit, department_id
                 FROM courses
                 WHERE id = ?"
            );

            if($stmt){
                mysqli_stmt_bind_param($stmt, "i", $courseId);
                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);

                if(mysqli_num_rows($result) > 0){
                    $editCourse = mysqli_fetch_assoc($result);
                }

                mysqli_stmt_close($stmt);
            }
        }
    }

    $departments = mysqli_query(
        $conn,
        "SELECT id, code, name
         FROM departments
         ORDER BY name"
    );

    $courses = mysqli_query(
        $conn,
        "SELECT
            courses.id,
            courses.course_name,
            courses.credit,
            departments.code AS department_code,
            departments.name AS department_name
         FROM courses
         INNER JOIN departments
            ON courses.department_id = departments.id
         ORDER BY courses.id"
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management | UMS</title>
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

            <a href="courses.php" class="active">
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

        <h1>Course Management</h1>

        <p>View and manage course information.</p>

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

        <?php if($editCourse !== null): ?>

            <section class="panel">

                <h2>Edit Course</h2>

                <form method="POST" action="courses.php">

                    <input
                        type="hidden"
                        name="course_id"
                        value="<?php echo $editCourse["id"]; ?>"
                    >

                    <label for="course_name">
                        Course Name
                    </label>

                    <input
                        type="text"
                        id="course_name"
                        name="course_name"
                        value="<?php echo htmlspecialchars($editCourse["course_name"]); ?>"
                        required
                    >

                    <label for="credit">
                        Credit
                    </label>

                    <input
                        type="number"
                        id="credit"
                        name="credit"
                        min="1"
                        value="<?php echo $editCourse["credit"]; ?>"
                        required
                    >

                    <label for="department_id">
                        Department
                    </label>

                    <select
                        id="department_id"
                        name="department_id"
                        required
                    >

                        <?php if($departments): ?>

                            <?php while($department = mysqli_fetch_assoc($departments)): ?>

                                <option
                                    value="<?php echo $department["id"]; ?>"
                                    <?php echo ($department["id"] == $editCourse["department_id"]) ? "selected" : ""; ?>
                                >
                                    <?php echo htmlspecialchars($department["code"] . " - " . $department["name"]); ?>
                                </option>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </select>

                    <div class="form-actions">

                        <button type="submit" name="update_course">
                            Update Course
                        </button>

                        <a href="courses.php" class="cancel-btn">
                            Cancel
                        </a>

                    </div>

                </form>

            </section>

        <?php endif; ?>

        <section class="panel">

            <h2>Available Courses</h2>

            <?php if($courses && mysqli_num_rows($courses) > 0): ?>

                <div class="table-container">

                    <table>

                        <thead>

                            <tr>
                                <th>ID</th>
                                <th>Course Name</th>
                                <th>Credit</th>
                                <th>Department</th>
                                <th>Action</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php while($course = mysqli_fetch_assoc($courses)): ?>

                                <tr>

                                    <td>
                                        <?php echo $course["id"]; ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($course["course_name"]); ?>
                                    </td>

                                    <td>
                                        <?php echo $course["credit"]; ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($course["department_code"]); ?>
                                    </td>

                                    <td>
                                        <a
                                            href="courses.php?edit=<?php echo $course["id"]; ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-message">
                    No courses are available.
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