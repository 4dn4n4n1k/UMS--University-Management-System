<?php
    require_once "database.php";

    function login($email, $password){
        $conn = dbConnect();

        if(!$conn){
            return null;
        }

        $sql = "SELECT * FROM users WHERE email = ? AND password = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "ss", $email, $password);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) > 0){
            
            $row = mysqli_fetch_assoc($result);

            mysqli_close($conn);
            return $row;
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
            header("Location: ../welcome.php");
            exit();
        }
        else{
            header("Location: ../Sign_in/sign_in.php?error=1");
            exit();
        }
    }
?>