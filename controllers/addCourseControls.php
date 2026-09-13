<?php
require_once "../models/coursesModel.php";
session_start();

$studentId=$_SESSION["userId"];
$courseId=$_GET["courseId"];

if(checkEnrollment($studentId, $courseId))
{
    header("Location: ../views/student/addCourse.php?msg="."You already added this course");
}
else
{
    if(addEnrollment($studentId, $courseId))
    {
        header("Location: ../views/student/addCourse.php?msg="."Course added");
    }
    else
    {
        header("Location: ../views/student/addCourse.php?msg="."Could not add course");
    }
}


?>