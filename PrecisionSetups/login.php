<!--
Matthew Neal
CSCI N-342
Completed 10-16-16
login.php
-->
<!---->
<?php session_start();
    //if this is a page that requires login always perform this session verification
    require_once "../inc/sessionVerify.php";
    require_once "../inc/util.php";

    require_once "../inc/dbconnect.php";
    $_SESSION['timeout'] = time();
    if (isset($_SESSION['email'])) {
        $sql = "select * from REGISTRATION where username = '" . $_SESSION['email'] . "'";
    }
    else {
        Header("Location:login.php");
    }
?>

<!DOCTYPE html>
<html lang="en">
<?php
include "./head.php"
?>
<body>
<div id="main-wrapper">
    <?php
    include "./header.php"
    ?>

    <?php

    $max = 3;
    $attempts = "";
    $msg = "";
    $userPWord = "";
    $pWord = "";
    $userEmail = "";
    $eMail = "";
    $counter = 0;
    $disabled = "";

    if (isset($_POST['enter'])) {

        //ensure no white space
        $eMail = trim($_POST['email']);
        $pWord = trim($_POST['password']);
        $email = mysqli_real_escape_string($con, $eMail);
        $pWord = mysqli_real_escape_string($con, $pWord);

        if ($counter < $max && (spamcheck($eMail)))
        {
            $_SESSION['email'] = $email;

            $sql = "SELECT COUNT(*) AS c FROM Customer_FP WHERE Email = '".$email."' AND Password = '".$pWord."' AND authVer = '1'";
            $result = mysqli_query($con, $sql) or die(mysqli_error($con)); //send the query to the database or quit if cannot connect
            $field = mysqli_fetch_object($result); //the query results are objects, in this case, one object
            $count = $field->c;

            $sqlElif = "SELECT COUNT(*) AS c FROM Customer_FP WHERE authVer = '1' AND Email = '".$email."'";
            $resultElif = mysqli_query($con, $sqlElif) or die(mysqli_error($con)); //send the query to the database or quit if cannot connect
            $fieldElif = mysqli_fetch_object($resultElif); //the query results are objects, in this case, one object
            $countElif = $fieldElif->c;

            $sqlAdmin = "SELECT COUNT(*) AS c FROM Customer_FP WHERE authVer = '1' AND Email = '".$email."' AND Password = '".$pWord."' AND isAdmin = '1'";
            $resultAdmin = mysqli_query($con, $sqlAdmin) or die(mysqli_error($con)); //send the query to the database or quit if cannot connect
            $fieldAdmin = mysqli_fetch_object($resultAdmin); //the query results are objects, in this case, one object
            $countAdmin = $fieldAdmin->c;

            if ($countAdmin > 0) {
                Header("Location:adminLanding.php");
            }//end if

            else if ($count > 0) {
                Header("Location:userLanding.php");
            }//end elif

            elseif ($countElif < 1) {
                $msg = "Account not authorized. Please check your email for authorization link before logging in.";
            }//end if

            else {
                $msg = "The information entered does not match with the records in our database.";
                $_SESSION['counter'] = $counter++;
            }//end else
        }//end if

        else {
            $disabled = "disabled";
            $msg = "Max Attempts Used. Please Try Again Later.";
        }
    }

    ?>

    <!-- Wrapper -->
    <div id="wrapper" name="login">

        <form action="login.php" method="post">

           <?php print $msg; ?>

            <label for="email">Email:</label>
            <input type="email" id="email" placeholder="btables@iupui.edu" name="email" required <?php echo $disabled; ?>>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required <?php echo $disabled; ?>>

            <button name="enter" class="btn" type="submit">Log In</button>

            <p style="text-align: center;">Not a user yet?
                <a style="color: lightblue" href="http://corsair.cs.iupui.edu:20181/PrecisionSetups/registration.php">Register.</a></p>
        </form>

    </div>


    <?php
    include "./footer.php"
    ?>
</div>
<?php
include "./scripts.php"
?>
</body>
</html>