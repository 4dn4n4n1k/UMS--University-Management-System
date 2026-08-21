<?php
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        
        $fName = trim($_POST["fName"]);
        $email = trim($_POST["email"]);
        $gender = $_POST["gender"]??"";
        $password = $_POST["password"];
        $cPassword = $_POST["cPassword"];
        $dob = $_POST["dob"];
        $role = $_POST["role"];

        $hasError = false;
        $nameError = "";
        $emailError = "";
        $genderError = "";
        $dobError = "";
        $passwordError = "";
        $roleError = "";

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
        }

        if(empty($dob)){
            $dobError = "Date of birth should be provided";
            $hasError = true;
        }

        if(empty($role)){
            $roleError = "Role must be selected";
            $hasError = true;
        }

        if($hasError){
            $url = $url="Location: sign_up.php?nameError=".urlencode($nameError)."&emailError=".$emailError."&genderError=".$genderError."&passwordError=".$passwordError."&dobError=".$dobError."&roleError=".$roleError;

            header($url);
        }
        else{

        }
    }


?>