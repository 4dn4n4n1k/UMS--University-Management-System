<?php

session_start();

require_once "../models/database.php";

if (!isset($_SESSION["userId"]) || !isset($_SESSION["role"])) {
    header("Location: Sign_in/sign_in.php");
    exit();
}


if ($_SESSION["role"] !== "student") {
    header("Location: ../controllers/Sign_in/sign_in.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../views/student/changePassword.php");
    exit();
}


$currentPassword = $_POST["currentPassword"] ?? "";
$newPassword = $_POST["newPassword"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";


if (
    empty($currentPassword) ||
    empty($newPassword) ||
    empty($confirmPassword)
) {
    header("Location: ../views/student/changePassword.php?msg=All fields are required");
    exit();
}


if ($newPassword !== $confirmPassword) {
    header("Location: ../views/student/changePassword.php?msg=New passwords do not match");
    exit();
}


if (strlen($newPassword) < 8) {
    header("Location: ../views/student/changePassword.php?msg=Password must be at least 8 characters");
    exit();
}


$userId = $_SESSION["userId"];

$conn = dbConnect();


if (!$conn) {
    header("Location: ../views/student/changePassword.php?msg=Database connection failed");
    exit();
}


$sql = "SELECT password
        FROM users
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {
    mysqli_close($conn);

    header("Location: ../views/student/changePassword.php?msg=Something went wrong");
    exit();
}


mysqli_stmt_bind_param($stmt, "i", $userId);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


if (mysqli_num_rows($result) === 0) {

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../views/student/changePassword.php?msg=User not found");
    exit();
}


$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!password_verify($currentPassword, $user["password"])) {

    mysqli_close($conn);

    header("Location: ../views/student/changePassword.php?msg=Current password is incorrect");
    exit();
}

$newPasswordHash = password_hash(
    $newPassword,
    PASSWORD_DEFAULT
);


$sql = "UPDATE users
        SET password = ?
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    mysqli_close($conn);

    header("Location: ../views/student/changePassword.php?msg=Could not update password");
    exit();
}


mysqli_stmt_bind_param(
    $stmt,
    "si",
    $newPasswordHash,
    $userId
);


if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../views/student/changePassword.php?msg=Password changed successfully");
    exit();

}


mysqli_stmt_close($stmt);
mysqli_close($conn);

header("Location: ../views/student/changePassword.php?msg=Could not update password");
exit();

?>