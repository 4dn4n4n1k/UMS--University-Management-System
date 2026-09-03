<?php
    $server = "localhost";
    $username = "root";
    $password = "";
    $db = "ums";


    function dbConnect(){
        global $server;
        global $username;
        global $password;
        global $db;

        $conn = mysqli_connect($server, $username, $password, $db);

        if($conn){
            echo "Connection  Successful";
        }
        else{
            echo "Connection failed".mysqli_connect_error();
        }

        return $conn;
    }
    dbConnect();
?>