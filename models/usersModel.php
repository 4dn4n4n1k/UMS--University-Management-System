<?php

require_once "database.php";


function getUserById($userId)
{
    $conn = dbConnect();

    if (!$conn) {
        return false;
    }

    $sql = "SELECT
                users.id AS userId,
                users.username AS username,
                users.email AS email,
                users.role AS role,

                students.name AS name,
                students.date_of_birth AS date_of_birth,
                students.gender AS gender,
                students.profile_image AS profile_image

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


    $user = mysqli_fetch_assoc($result);


    mysqli_stmt_close($stmt);
    mysqli_close($conn);


    return $user;
}

?>