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
$editResult = null;

function calculateGrade($marks)
{
    if($marks >= 80){
        return "A+";
    }
    elseif($marks >= 75){
        return "A";
    }
    elseif($marks >= 70){
        return "A-";
    }
    elseif($marks >= 65){
        return "B+";
    }
    elseif($marks >= 60){
        return "B";
    }
    elseif($marks >= 55){
        return "B-";
    }
    elseif($marks >= 50){
        return "C+";
    }
    elseif($marks >= 45){
        return "C";
    }
    elseif($marks >= 40){
        return "D";
    }

    return "F";
}

if(!$conn){
    $error = "Database connection failed.";
}
else{

    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_result"])){

        $studentId = intval($_POST["student_id"]);
        $courseId = intval($_POST["course_id"]);
        $marks = intval($_POST["marks"]);
        $grade = calculateGrade($marks);

        if($studentId <= 0 || $courseId <= 0 || $marks < 0 || $marks > 100){
            $error = "Please provide valid result information.";
        }
        else{

            $checkStmt = mysqli_prepare(
                $conn,
                "SELECT id
                 FROM results
                 WHERE student_id = ? AND course_id = ?"
            );

            if($checkStmt){

                mysqli_stmt_bind_param(
                    $checkStmt,
                    "ii",
                    $studentId,
                    $courseId
                );

                mysqli_stmt_execute($checkStmt);

                $checkResult = mysqli_stmt_get_result($checkStmt);

                if(mysqli_num_rows($checkResult) > 0){

                    $error = "A result already exists for this student and course.";

                }
                else{

                    $stmt = mysqli_prepare(
                        $conn,
                        "INSERT INTO results
                        (student_id, course_id, marks, grade)
                        VALUES (?, ?, ?, ?)"
                    );

                    if($stmt){

                        mysqli_stmt_bind_param(
                            $stmt,
                            "iiis",
                            $studentId,
                            $courseId,
                            $marks,
                            $grade
                        );

                        if(mysqli_stmt_execute($stmt)){
                            $message = "Student result added successfully.";
                        }
                        else{
                            $error = "Unable to add the student result.";
                        }

                        mysqli_stmt_close($stmt);
                    }
                    else{
                        $error = "Unable to prepare the result.";
                    }
                }

                mysqli_stmt_close($checkStmt);

            }
            else{
                $error = "Unable to check the existing result.";
            }
        }
    }

    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_result"])){

        $resultId = intval($_POST["result_id"]);
        $studentId = intval($_POST["student_id"]);
        $courseId = intval($_POST["course_id"]);
        $marks = intval($_POST["marks"]);
        $grade = calculateGrade($marks);

        if($resultId <= 0 || $studentId <= 0 || $courseId <= 0 || $marks < 0 || $marks > 100){
            $error = "Please provide valid result information.";
        }
        else{

            $checkStmt = mysqli_prepare(
                $conn,
                "SELECT id
                 FROM results
                 WHERE student_id = ?
                 AND course_id = ?
                 AND id != ?"
            );

            if($checkStmt){

                mysqli_stmt_bind_param(
                    $checkStmt,
                    "iii",
                    $studentId,
                    $courseId,
                    $resultId
                );

                mysqli_stmt_execute($checkStmt);

                $checkResult = mysqli_stmt_get_result($checkStmt);

                if(mysqli_num_rows($checkResult) > 0){

                    $error = "Another result already exists for this student and course.";

                }
                else{

                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE results
                         SET student_id = ?, course_id = ?, marks = ?, grade = ?
                         WHERE id = ?"
                    );

                    if($stmt){

                        mysqli_stmt_bind_param(
                            $stmt,
                            "iiisi",
                            $studentId,
                            $courseId,
                            $marks,
                            $grade,
                            $resultId
                        );

                        if(mysqli_stmt_execute($stmt)){
                            $message = "Student result updated successfully.";
                        }
                        else{
                            $error = "Unable to update the student result.";
                        }

                        mysqli_stmt_close($stmt);
                    }
                    else{
                        $error = "Unable to prepare the update.";
                    }
                }

                mysqli_stmt_close($checkStmt);

            }
            else{
                $error = "Unable to check the existing result.";
            }
        }
    }

    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_result"])){

        $resultId = intval($_POST["result_id"]);

        if($resultId <= 0){
            $error = "Invalid result.";
        }
        else{

            $stmt = mysqli_prepare(
                $conn,
                "DELETE FROM results WHERE id = ?"
            );

            if($stmt){

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $resultId
                );

                if(mysqli_stmt_execute($stmt)){
                    $message = "Student result deleted successfully.";
                }
                else{
                    $error = "Unable to delete the student result.";
                }

                mysqli_stmt_close($stmt);
            }
            else{
                $error = "Unable to prepare the delete operation.";
            }
        }
    }

    if(isset($_GET["edit"])){

        $resultId = intval($_GET["edit"]);

        if($resultId > 0){

            $stmt = mysqli_prepare(
                $conn,
                "SELECT id, student_id, course_id, marks, grade
                 FROM results
                 WHERE id = ?"
            );

            if($stmt){

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $resultId
                );

                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);

                if(mysqli_num_rows($result) > 0){
                    $editResult = mysqli_fetch_assoc($result);
                }

                mysqli_stmt_close($stmt);
            }
        }
    }

    $students = mysqli_query(
        $conn,
        "SELECT
            id,
            username,
            name
         FROM students
         ORDER BY name"
    );

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

    $results = mysqli_query(
        $conn,
        "SELECT
            results.id,
            results.student_id,
            results.course_id,
            results.marks,
            results.grade,
            students.username,
            students.name AS student_name,
            courses.course_name,
            departments.code AS department_code
         FROM results
         INNER JOIN students
            ON results.student_id = students.id
         INNER JOIN courses
            ON results.course_id = courses.id
         INNER JOIN departments
            ON courses.department_id = departments.id
         ORDER BY results.id DESC"
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Results | UMS</title>
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

            <a href="results.php" class="active">
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

        <h1>Student Results</h1>

        <p>Upload and manage student results.</p>

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

            <?php if($editResult === null): ?>

                <h2>Add Student Result</h2>

                <form method="POST" action="results.php">

                    <label for="student_id">
                        Student
                    </label>

                    <select
                        id="student_id"
                        name="student_id"
                        required
                    >

                        <option value="">
                            Select Student
                        </option>

                        <?php if($students): ?>

                            <?php while($student = mysqli_fetch_assoc($students)): ?>

                                <option value="<?php echo $student["id"]; ?>">
                                    <?php echo htmlspecialchars($student["name"] . " (" . $student["username"] . ")"); ?>
                                </option>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </select>

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

                    <label for="marks">
                        Marks
                    </label>

                    <input
                        type="number"
                        id="marks"
                        name="marks"
                        min="0"
                        max="100"
                        required
                    >

                    <button type="submit" name="add_result">
                        Add Result
                    </button>

                </form>

            <?php else: ?>

                <h2>Edit Student Result</h2>

                <form method="POST" action="results.php">

                    <input
                        type="hidden"
                        name="result_id"
                        value="<?php echo $editResult["id"]; ?>"
                    >

                    <label for="student_id">
                        Student
                    </label>

                    <select
                        id="student_id"
                        name="student_id"
                        required
                    >

                        <?php if($students): ?>

                            <?php while($student = mysqli_fetch_assoc($students)): ?>

                                <option
                                    value="<?php echo $student["id"]; ?>"
                                    <?php echo ($student["id"] == $editResult["student_id"]) ? "selected" : ""; ?>
                                >
                                    <?php echo htmlspecialchars($student["name"] . " (" . $student["username"] . ")"); ?>
                                </option>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </select>

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
                                    <?php echo ($course["id"] == $editResult["course_id"]) ? "selected" : ""; ?>
                                >
                                    <?php echo htmlspecialchars($course["department_code"] . " - " . $course["course_name"]); ?>
                                </option>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </select>

                    <label for="marks">
                        Marks
                    </label>

                    <input
                        type="number"
                        id="marks"
                        name="marks"
                        min="0"
                        max="100"
                        value="<?php echo $editResult["marks"]; ?>"
                        required
                    >

                    <div class="form-actions">

                        <button type="submit" name="update_result">
                            Update Result
                        </button>

                        <a href="results.php" class="cancel-btn">
                            Cancel
                        </a>

                    </div>

                </form>

            <?php endif; ?>

        </section>

        <section class="panel">

            <h2>Existing Results</h2>

            <?php if($results && mysqli_num_rows($results) > 0): ?>

                <div class="table-container">

                    <table>

                        <thead>

                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Department</th>
                                <th>Marks</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php while($resultRow = mysqli_fetch_assoc($results)): ?>

                                <tr>

                                    <td>
                                        <?php echo $resultRow["id"]; ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($resultRow["student_name"] . " (" . $resultRow["username"] . ")"); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($resultRow["course_name"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($resultRow["department_code"]); ?>
                                    </td>

                                    <td>
                                        <?php echo $resultRow["marks"]; ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($resultRow["grade"]); ?>
                                    </td>

                                    <td>

                                        <a
                                            href="results.php?edit=<?php echo $resultRow["id"]; ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="results.php"
                                            class="delete-form"
                                            onsubmit="return confirm('Are you sure you want to delete this result?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="result_id"
                                                value="<?php echo $resultRow["id"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="delete_result"
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
                    No student results have been added yet.
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