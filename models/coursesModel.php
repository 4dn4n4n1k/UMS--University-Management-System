<?php
require_once "dbConnect.php";

function getAllCourses()
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT * FROM courses";
        $result=mysqli_query($conn, $sql);
        return $result;
    }
    else
    {
        echo "connection failed";
    }
}

function getCoursesByFaculty($facultyId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT * FROM courses WHERE facultyId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $facultyId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        return $result;
    }
    else
    {
        echo "connection failed";
    }
}

function checkEnrollment($studentId, $courseId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT * FROM enrollments WHERE studentId=? AND courseId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $studentId, $courseId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result)>0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    else
    {
        echo "connection failed";
    }
}

function addEnrollment($studentId, $courseId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="INSERT INTO enrollments (studentId, courseId) VALUES (?,?)";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $studentId, $courseId);

        if(mysqli_stmt_execute($stmt))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    else
    {
        echo "connection failed";
    }
}

function dropEnrollment($studentId, $courseId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="DELETE FROM enrollments WHERE studentId=? AND courseId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $studentId, $courseId);

        if(mysqli_stmt_execute($stmt))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    else
    {
        echo "connection failed";
    }
}

function getEnrolledCourses($studentId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT courses.courseId, courses.courseName, courses.credit, users.name AS facultyName
              FROM enrollments
              JOIN courses ON enrollments.courseId=courses.courseId
              LEFT JOIN users ON courses.facultyId=users.userId
              WHERE enrollments.studentId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $studentId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        return $result;
    }
    else
    {
        echo "connection failed";
    }
}

function countEnrolled($studentId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT COUNT(*) AS total FROM enrollments WHERE studentId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $studentId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            return $row["total"];
        }
    }
    else
    {
        echo "connection failed";
    }
}

function getStudentsOfFaculty($facultyId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT users.userId, users.name, users.email, courses.courseName
              FROM enrollments
              JOIN courses ON enrollments.courseId=courses.courseId
              JOIN users ON enrollments.studentId=users.userId
              WHERE courses.facultyId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $facultyId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        return $result;
    }
    else
    {
        echo "connection failed";
    }
}

function countCourses()
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT COUNT(*) AS total FROM courses";
        $result=mysqli_query($conn, $sql);

        while($row=mysqli_fetch_assoc($result))
        {
            return $row["total"];
        }
    }
    else
    {
        echo "connection failed";
    }
}

function countByFaculty($facultyId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT COUNT(*) AS total FROM courses WHERE facultyId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $facultyId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            return $row["total"];
        }
    }
    else
    {
        echo "connection failed";
    }
}


?>