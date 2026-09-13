<?php
    session_start();
    session_unset();
    session_destroy();

    header("Location: ../controllers/Sign_in/sign_in.php");
    exit();
?>