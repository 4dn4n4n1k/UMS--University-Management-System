<?php

require_once "database.php";

function getStudentIdFromUserId($userId)
{
    $conn = dbConnect();

    if (!$conn) {
        return false;
    }

    $sql = "SELECT students.id AS studentId
            FROM users
            INNER JOIN students
                ON users.username = students.username
            WHERE users.id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return false;
    }

    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $row["studentId"];
}


function getAllCourses()
{
    $conn = dbConnect();

    if (!$conn) {
        return false;
    }

    $sql = "SELECT
                id AS courseId,
                course_name AS courseName,
                credit
            FROM courses
            ORDER BY id";

    $result = mysqli_query($conn, $sql);

    mysqli_close($conn);

    return $result;
}


function checkEnrollment($userId, $courseId)
{
    $studentId = getStudentIdFromUserId($userId);

    if ($studentId === false) {
        return false;
    }

    $conn = dbConnect();

    if (!$conn) {
        return false;
    }

    $sql = "SELECT id
            FROM enrollments
            WHERE student_id = ?
            AND course_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ii", $studentId, $courseId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $enrolled = mysqli_num_rows($result) > 0;

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $enrolled;
}


function addEnrollment($userId, $courseId)
{
    $studentId = getStudentIdFromUserId($userId);

    if ($studentId === false) {
        return false;
    }

    $conn = dbConnect();

    if (!$conn) {
        return false;
    }

    $sql = "INSERT INTO enrollments
            (student_id, course_id)
            VALUES (?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ii", $studentId, $courseId);

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $success;
}


function dropEnrollment($userId, $courseId)
{
    $studentId = getStudentIdFromUserId($userId);

    if ($studentId === false) {
        return false;
    }

    $conn = dbConnect();

    if (!$conn) {
        return false;
    }

    $sql = "DELETE FROM enrollments
            WHERE student_id = ?
            AND course_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ii", $studentId, $courseId);

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $success;
}


function getEnrolledCourses($userId)
{
    $studentId = getStudentIdFromUserId($userId);

    if ($studentId === false) {
        return false;
    }

    $conn = dbConnect();

    if (!$conn) {
        return false;
    }

    $sql = "SELECT
                courses.id AS courseId,
                courses.course_name AS courseName,
                courses.credit AS credit
            FROM enrollments
            INNER JOIN courses
                ON enrollments.course_id = courses.id
            WHERE enrollments.student_id = ?
            ORDER BY courses.id";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $studentId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);


    return $result;
}



function countEnrolled($userId)
{
    $studentId = getStudentIdFromUserId($userId);

    if ($studentId === false) {
        return 0;
    }

    $conn = dbConnect();

    if (!$conn) {
        return 0;
    }

    $sql = "SELECT COUNT(*) AS total
            FROM enrollments
            WHERE student_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return 0;
    }

    mysqli_stmt_bind_param($stmt, "i", $studentId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return (int)$row["total"];
}

?>