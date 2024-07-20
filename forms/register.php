<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="../assets/img/favicon.png" rel="icon">
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
</head>
<body>
    
<!-- register -->
<div class="container">
        <div class="box form-box">

        <?php
             include "../assets/php/connexion.php";
             $con = connectdb();

             if(isset($_POST['submit'])){
                $email = $_POST['email'];
                $username = $_POST['username'];
                $password = $_POST['password'];

                //recuperer les emails avec neutralisation des Char spec comme { / , " , '}
                $email = $con->real_escape_string($_POST['email']);

                //email syntaxe verfication
                $test = filter_var($email,FILTER_VALIDATE_EMAIL);
                if(!$test){
                     ?>

                     <div class="message">
                     <p>ERROR : Email Invalide !</p>
                     </div><br>
                     <a href="javascript:self.history.back()"><button class="btn">Go Back</button>

                     <?php
                }else
                {
                     $test = "SELECT * FROM users
                             WHERE email='$email' AND password='$password'";
                     $result = $con->query($test);

                     if($result->num_rows > 0){
                        ?>
                         <div class="message">
                         <p>This email is used , Try another One Please !</p>
                         </div><br>
                         <a href="javascript:self.history.back()"><button class="btn">Go Back</button>
                <?php
                     }else{
                         $sql = "INSERT INTO users (username,password,email,usertype)
                                 VALUES  ('$username','$password','$email','user')";
                         $con->query($sql);

                         ?>
                          <div class="message">
                         <p>Registration successfully !</p>
                         </div><br>
                         <a href="login.php"><button class="btn">Login Now</button>
                         <?php
                     }
                }
             }else{
        ?>
        <div class="header">Sign Up</div>
        <form action="" method="post">
                <div class="field input">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" autocomplete="off" required>
                </div>
                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" autocomplete="off" required>
                </div>

                <div class="field">
                    
                    <input type="submit" class="btn" name="submit" value="Register" required>
                </div>
                <div class="links">
                    Already a member? <a href="login.php">Log In</a>
                </div>
            </form>
        </div>
        <?php } ?>
</div>
</body>
</html>