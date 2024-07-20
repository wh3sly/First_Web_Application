<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>
     window.history.forward();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="../assets/img/favicon.png" rel="icon">
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
</head>
<body>
    
<!-- login -->
<div class="container">
        <div class="box form-box">
        
        <?php
             include "../assets/php/connexion.php";
             $con = connectdb();

             if(isset($_POST['submit'])){

                //recuperer les emails avec neutralisation des Char spec comme { / , " , '}
                $email = $con->real_escape_string($_POST['email']);
                $password = $_POST['password'];
                //email syntaxe verfication
                $test = filter_var($email,FILTER_VALIDATE_EMAIL);
                if(!$test){
                     ?>

                     <div class="message">
                     <p>ERROR : Email Invalide !</p>
                     </div><br>
                     <a href="javascript:self.history.back()"><button class="btn">Go Back</button>

                     <?php
                }
                else
                {
                     $sql = "SELECT * FROM users
                            WHERE email='$email' AND password='$password'";
                     $result = $con->query($sql);
                     $row = $result->fetch_assoc();
                     if(is_array($row) && !empty($row)){
                        $_SESSION['id']=$row['id'];
                        $_SESSION['valid']=$row['email'];
                        $_SESSION['username']=$row['username'];
                        $_SESSION['password']=$row['password'];
                        $_SESSION['usertype']=$row['usertype'];
                     }
                     else
                     {
                         ?>
                         <div class="message">
                         <p>Wrong Email or Password !</p>
                         </div><br>
                         <a href="./login.php"><button class="btn">Go Back</button>
                         <?php
                     }

                     //envoyer user ou admin a son destination
                     if(isset($_SESSION['valid'])){
                        if($_SESSION['usertype'] == 'user'){
                            header('location: ../content/user.php');
                        }
                        if($_SESSION['usertype'] == 'admin'){
                            header('location: ../content/admin.php');
                        }
                     }

                }

             }else{
                ?>
        <div class="header">Log In</div>
            <form action="" method="post">
                <div class="field input">
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" autocomplete="off" required>
                </div>

                <div class="field">
                    
                    <input type="submit" class="btn" name="submit" value="Login" required>
                </div>
                <div class="links">
                    Don't have account? <a href="register.php">Sign Up Now</a>
                </div>
            </form>
        </div>

        <?php } ?>
      </div>
    </section>
</body>
</html>