<?php 
    require_once "database.php";

    if(isset($_GET["username"])){

        $username = trim($_GET["username"]);
        $conn = dbConnect();

        if(!$conn){
            echo "Couldn't connect to database";
            exit();
        }

        $sql = "SELECT username FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) > 0){
            echo "taken";
        }
        else{
            echo "available";
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
?>