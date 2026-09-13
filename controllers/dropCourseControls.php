<?php
require_once "../models/coursesModel.php";
session_start();

$studentId=$_SESSION["userId"];
$courseId=$_GET["courseId"];

if(dropEnrollment($studentId, $courseId))
{
    header("Location: ../views/student/dropCourse.php?msg="."Course dropped");
}
else
{
    header("Location: ../views/student/dropCourse.php?msg="."Could not drop course");
}


?>