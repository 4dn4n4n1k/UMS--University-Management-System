<!DOCTYPE html>

<html>
    <head>
        <title>UMS - University Management System</title>
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
                       
                    <form action="#" method="post" id="input">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required placeholder="Enter your email">
                            <br>
                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password" required placeholder="Enter the password">
                            <br>
                            <button id="submitBtn">Login</button>
                    </form>

                </div>
            </section>
        </div>
    </body>
</html>