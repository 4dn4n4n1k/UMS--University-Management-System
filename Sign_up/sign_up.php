<!DOCTYPE html>

<html>
    <head>
        <title>Sign up | UMS - University Management System</title>
        <link rel="stylesheet" href="sign_up.css">
    </head>

    <body>
        <div class="signup">
            <section class="left">
                <h1 id="left-h1">University Management <br> System</h1>
                <p id="left-para">Authoriyy - Faculty - Student</p>
            </section>

            <section class="right">
                
                <div class="signup-card">
                    <h2 id="signup-title">Sign Up</h2>
                    <p id="signup-para">Enter your credentials to continue</p>
                       
                    <form action="data_control.php" method="post" id="input" enctype="multipart/form-data">
                        <label for="fName">Full Name:</label>
                        <input type="text" name="fName" id="fName" placeholder="Enter your full name">
                        <span style="color: red;">
                            <?php
                                if(isset($_GET["nameError"])){
                                    echo $_GET["nameError"];
                                }
                            ?>
                        </span><br>

                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" placeholder="Enter your email">
                        <span style="color: red;">
                            <?php
                                if(isset($_GET["emailError"])){
                                    echo $_GET["emailError"];
                                }
                            ?>
                        </span><br>

                        <label for="gender">Gender:</label>
                        <select id="gender" name="gender">
                            <option value="">----- Select your gender -----</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                        <span style="color: red;">
                            <?php
                                if(isset($_GET["genderError"])){
                                    echo $_GET["genderError"];
                                }
                            ?>
                        </span><br>


                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" placeholder="Enter your password">
                        <span style="color: red;">
                            <?php
                                if(isset($_GET["passwordError"])){
                                    echo $_GET["passwordError"];
                                }
                            ?>
                        </span><br>

                        <label for="cPassword">Confirm Password:</label>
                        <input type="password" name="cPassword" id="cPassword" placeholder="Confirm password">
                        <span style="color: red;">
                            <?php
                                if(isset($_GET["passwordError"])){
                                    echo $_GET["passwordError"];
                                }
                            ?>
                        </span><br>
                        
                        <label for="dob">Date of Birth:</label>
                        <input type="date" name="dob" id="dob">
                        <span style="color: red;">
                            <?php
                                if(isset($_GET["dobError"])){
                                    echo $_GET["dobError"];
                                }
                            ?>
                        </span><br>

                        <label for="role">Role:</label>
                        <select name="role" id="role">
                            <option value="">----- Select your role -----</option>
                            <option value="student">Student</option>
                            <option value="faculty">Faculty</option>
                            <option value="admin">Admin</option>
                        </select>
                       <span style="color: red;">
                        <?php
                            if(isset($_GET["roleError"])){
                                echo $_GET["roleError"];
                            }
                        ?>
                       </span><br>

                        <button id="signUpBtn">Sign up</button>
                    </form>

                    <p id="signin_para">Already have an account? <span id="signinBtn"><a href="/Project/Sign_in/sign_in.php">Sign in</a></span></p>

                </div>
            </section>
        </div>
    </body>
</html>