<?php

    session_start();
    require_once "database.php";

    function login($email, $password){
        $conn = dbConnect();

        if(!$conn){
            return null;
        }

        $sql = "SELECT * FROM users WHERE email = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) > 0){
            
            $row = mysqli_fetch_assoc($result);

            if(password_verify($password, $row["password"])){
                mysqli_close($conn);
                return $row;
            }
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        return false;
    }

    if($_SERVER["REQUEST_METHOD"] == "POST"){

        $email = $_POST["email"];
        $password = $_POST["password"];
        $user = login($email, $password);

        if($user !== false){

            $_SESSION["role"] = $user["role"];

            if($user["role"] == "admin"){
                header("Location: ../views/admin/admin.php");
                exit();
            }
            elseif($user["role"] == "student"){
                header("Location: ../views/student/student.php");
                exit();
            }
            elseif($user["role"] == "faculty"){
                header("Location: ../views/faculty/faculty.php");
                exit();
            }
        }
        else{
            header("Location: ../Sign_in/sign_in.php?error=1");
            exit();
        }
    }
?>