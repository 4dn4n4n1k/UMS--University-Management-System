<!DOCTYPE html>

<html>
    <head>
        <title>Sign in | UMS - University Management System</title>
        <link rel="stylesheet" href="sign_in.css">
    </head>

    <body>
        <div class="login">
            <section class="left">
                <h1 id="left-h1">University Management <br> System</h1>
                <p id="left-para">Authoriyy - Faculty - Student</p>
            </section>

            <section class="right">
                
                <div class="login-card">
                    <h2 id="login-title">Sign In</h2>
                    <p id="login-para">Enter your credentials to continue</p>
                       
                    <form action="../models/login.php" method="post" id="input">

                        <p id="feedback" class="<?php echo isset($_GET["signup"]) ? 'success' : 'error'; ?>" >
                            <?php
                                if(isset($_GET["error"])){
                                    echo "Wrong email or password";
                                }
                                elseif(isset($_GET["signup"]) && $_GET["signup"] == "success"){
                                    echo "Signup successful. Please login.";
                                }
                            ?>
                        </p>

                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required placeholder="Enter your email">
                        <br>

                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required placeholder="Enter the password">
                        <br>
                        
                        <button type ="submit" id="submitBtn">Login</button> 
                    </form>

                    <p id="signup_para">Don't have any account? <span id="signupBtn"><a href="/Project/Sign_up/sign_up.php">Create One</a></span></p>

                </div>
            </section>
        </div>
    </body>
</html>