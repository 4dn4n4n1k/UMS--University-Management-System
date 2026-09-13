<?php

require_once "database.php";


function getResultsByStudent($userId)
{
    $conn = dbConnect();

    if (!$conn) {
        return false;
    }

    $sql = "SELECT
                courses.course_name AS courseName,
                courses.credit AS credit,
                results.marks AS marks,
                results.grade AS grade
            FROM results
            INNER JOIN students
                ON results.student_id = students.id
            INNER JOIN users
                ON students.username = users.username
            INNER JOIN courses
                ON results.course_id = courses.id
            WHERE users.id = ?
            ORDER BY courses.id";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $userId);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    return $result;
}

?>