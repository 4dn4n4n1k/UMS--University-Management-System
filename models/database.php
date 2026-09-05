<?php
    $server = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $db = "ums";


    function dbConnect(){
        global $server;
        global $dbUsername;
        global $dbPassword;
        global $db;

        $conn = mysqli_connect($server, $dbUsername, $dbPassword, $db);

        if($conn){
        }
        else{
            echo "Connection failed".mysqli_connect_error();
        }

        return $conn;
    }
    dbConnect();
?>