<?php
    require_once "database.php";

    function login($username, $password){
        $conn = dbConnect();

        if(!$conn){
            return null;
        }

        $sql = "SELECT * FROM users WHERE username = ? AND PASSWORD = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) > 0){
            while($row = mysqli_fetch_assoc($result)){
                return $row;
            }
            else {
                echo "Wrong Credentials";
            }
        }



    }
?>