<?php

    require_once "../models/database.php";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        
        $fName = trim($_POST["fName"]);
        $email = trim($_POST["email"]);
        $gender = $_POST["gender"]??"";
        $password = $_POST["password"];
        $cPassword = $_POST["cPassword"];
        $dob = $_POST["dob"];
        $role = $_POST["role"];
        $username = trim($_POST["username"]);
        $avatar = $_FILES["avatar"];
        $allowedTypes = ["application/pdf", "image/jpeg"];
        $fileSize = $_FILES["avatar"]["size"];
        $fileType = $_FILES["avatar"]["type"];

        $hasError = false;
        $nameError = "";
        $emailError = "";
        $genderError = "";
        $dobError = "";
        $passwordError = "";
        $roleError = "";
        $avatarError = "";
        $avatarError = "";
        $userError = "";

        if($fName == ""){
            $nameError = "Name should be provided";
            $hasError = true;
        }
        elseif(!preg_match('/^[a-zA-Z\' -]+$/', $fName)){
            $nameError = "Name cannot have number or special characters";
            $hasError = true;
        }

        if(empty($email)){
            $emailError = "Email should be provided";
            $hasError = true;
        }
        elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $emailError = "Invalid email format";
            $hasError = true;
        }

        if(empty($gender)){
            $genderError = "Gender must be provided";
            $hasError = true;
        }
        if(empty($username)){
            $userError = "Username must be entered";
            $hasError = true;
        }
        elseif(!preg_match('/^[a-zA-Z0-9_]+$/', $username)){
            $userError = "Username can't contain special characters";
            $hasError = true;
        }

        if(empty($password)){
            $passwordError = "Password must be entered";
            $hasError = true;
        }
        elseif($password != $cPassword){
            $passwordError = "Password doesn't match";
            $hasError = true;
        }
        elseif(strlen($password) < 8){
            $passwordError = "Password must be 8 characters long";
            $hasError = true;
        }

        if(empty($dob)){
            $dobError = "Date of birth should be provided";
            $hasError = true;
        }

        if(empty($role)){
            $roleError = "Role must be selected";
            $hasError = true;
        }

        if($avatar["error"] == UPLOAD_ERR_NO_FILE){
            $avatarError = "Profile picture must be provided";
            $hasError = true;
        }
        elseif($avatar["error"] != UPLOAD_ERR_OK){
            $avatarError = "Error uploading profile picture";
            $hasError = true;
        }
        elseif($fileSize>(2*1024*1024)){
            $avatarError = "File should be under 2 MB";
            $hasError = true;
        }
        elseif(!in_array($fileType, $allowedTypes)){
            $avatarError = "File must be jpeg or pdf";
            $hasError = true;
        }

        if($hasError){
            $url = $url="Location: sign_up.php?". "nameError=".urlencode($nameError)."&emailError=".$emailError."&genderError=".$genderError."&passwordError=".$passwordError."&dobError=".$dobError."&roleError=".$roleError."&avatarError=".$avatarError."&userError=".$userError."&name=".urlencode($fName)."&email=".urlencode($email)."&gender=".urlencode($gender)."&username=".urlencode($username)."&role=".urlencode($role)."&dob=".urlencode($dob)."&avatar=".$avatar;

            header($url);
        }
        else{
            $conn = dbConnect();

            if(!$conn){
                die("Database connection failed.");
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);
            
            mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $passwordHash, $role);

            if(mysqli_stmt_execute($stmt)){
                if($role == "student"){
                    $sqlRole = "INSERT into students (username, name, date_of_birth, gender, profile_image) VALUES (?, ?, ?, ?, ?)";

                    $stmtRole = mysqli_prepare($conn, $sqlRole);

                    mysqli_stmt_bind_param($stmtRole, "sssss", $username, $fName, $dob, $gender, $avatar["name"]);

                    mysqli_stmt_execute($stmtRole);
                    mysqli_stmt_close($stmtRole);
                }

                elseif($role == "faculty"){
                    $sqlRole = "INSERT INTO faculty (username, name, gender, profile_image) VALUES (?, ?, ?, ?)";
                    $stmtRole = mysqli_prepare($conn, $sqlRole);

                    mysqli_stmt_bind_param($stmtRole, "ssss", $username, $fName, $gender, $avatar["name"]);

                    mysqli_stmt_execute($stmtRole);
                    mysqli_stmt_close($stmtRole);     
                }

                elseif($role == "admin"){
                    $sqlRole = "INSERT INTO admins (username, name) VALUES (?, ?)";
                    $stmtRole = mysqli_prepare($conn, $sqlRole);
                    
                    mysqli_stmt_bind_param($stmtRole, "ss", $username, $fName);

                    mysqli_stmt_execute($stmtRole);
                    mysqli_stmt_close($stmtRole); 
                }

                header("Location: ../Sign_in/sign_in.php?signup=success");
                exit();
            }
            else{
                echo "Registration Failed: ". mysqli_error($conn); 
            }

            mysqli_stmt_close($stmt);
            mysqli_close($conn);

        }
    }


?>