<!--
Matthew Neal
CSCI N-342
Completed 9-16-16
registration.php
-->

<?php session_start(); //this must be the very first line on the php page, to register this page to use session variables
?>

<!DOCTYPE HTML>

<html>

<?php
require_once "./util.php";
require_once "../mail/mail.class.php";
require_once "./dbconnect.php";
include "./head.php";
?>

<body>

<?php
$con = NULL;
$msg = "";
$fName = "";
$firstNameRequired = "*";
$lName = "";
$lastNameRequired = "*";
$pWord = "";
$passwordRequired = "*";
$passwordConfirmation = "";
$eMail = "";
$emailRequired = "*";
$emailConfirmation = "";
$homePhone = "";
$cellPhone = "";
$termsReq = "*";



if (isset($_POST['enter'])) {
    //ensure no white space
    $fName = trim($_POST['firstName']);
    $lName = trim($_POST['lastName']);
    $eMail = trim($_POST['email']);
    $emailConfirmation = trim($_POST['emailConfirm']);
    $pWord = trim($_POST['password']);
    $passwordConfirmation = trim($_POST['passwordConfirm']);
    $homePhone = trim($_POST['homePhone']);
    $cellPhone = trim($_POST['cellPhone']);
    $pWordCheck = false;

    if ($fName == "") {
        $firstNameRequired = '<span style = "color: red">*</span>';
    };

    if ($lName == "") {
        $lastNameRequired = '<span style = "color: red">*</span>';
    };


    if (!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
        $emailRequired = '<span style="color:red">*</span>';
    }
        if (!pWordValidate($pWord)) {
            $msg = 'Password is not in the required format of 10 or more characters containing at least one letter and on number.';
            $pWord = "";
            $passwordConfirmation = "";
        }
        else {
            if ($pWord != $passwordConfirmation) {
                $msg = "Please enter matching passwords.";
            }
            else $pWordCheck = true;
            {
                if ($eMail != $emailConfirmation) {
                    $msg = "Please enter matching emails.";
                }
                elseif ($pWord != $passwordConfirmation) {
                    $msg = "Please enter matching passwords.";
                    $pWord = "";
                    $passwordConfirmation = "";
                }
                elseif (($firstNameRequired != "*") or ($lastNameRequired != "*") or ($emailRequired != "*") or ($termsReq != "*")) {
                    $msg = "Please enter valid data.";
                }
                else {
                    /*************************************************************
                     * Enter data into the database here
                     *************************************************************/
                    //first escape all the strings so that backslashes are added before the following characters: \x00, \n, \r, \, ', " and \x1a.
                    //This is used to prevent sql injections.
                    $eMail = mysqli_real_escape_string(null, $eMail);
                    $pWord = mysqli_real_escape_string(null, $pWord);
                    $fName = mysqli_real_escape_string(null, $fName);
                    $lName = mysqli_real_escape_string(null, $lName);

                    $_SESSION['email'] = $eMail;

                    //first check if the username already exists in the database
                    $sql = "select count(*) as c from Customer_FP where Email = '" . $eMail. "'";

                    $result = mysqli_query(null, $sql) or die("Error in the consult.." . mysqli_error($con)); //send the query to the database or quit if cannot connect
                    $count = 0;
                    $field = mysqli_fetch_object($result); //the query results are objects, in this case, one object
                    $count = $field->c;
                    if ($count != 0)
                    {	Header ("Location:login.php?l=r");}
                    else //the username doesn't exist yet
                    {	$sql = "insert into Customer_FP values(null, '".$fName."', '".$lName."', '".$eMail."', '".$pWord."', '".$homePhone."', '".$cellPhone."')";
                        $result= mysqli_query($con, $sql) or die(mysqli_error($con)); //a non-select statement query will return a result indicating if the query is successful						//Commonly used functions are: Sys::getDB()->Execute, Sys::getDB()->GetOne(), Sys::getDB()->GetRows(),  Sys::getDB()->GetRow(), see details in adodb.inc.php
                        //send the email to the email registered for activating the account
                        //written by Andy Harris for his PHP/MySql book, modified for this lab to match
                        //my variables and requirements
                        $code = randomCodeGenerator(50);
                        $subject = "Email Activation";
                        $body = 'Thank you for registering at Precision Setups! We hope our website gives you the greatest experience.'.'<a href="http://corsair.cs.iupui.edu:20181/lab4/confirmation.php?code=' . $code . '">Your code is ' . $code . '</a>';
                        $mailer = new Mail();
                        if (($mailer->sendMail($eMail, $fName, $subject, $body)) == true) {
                            $msg = "<b>Thank you for registering. A welcome message has been sent to the address you have just registered.</b>";
                        }
                        else {
                            $msg = "Email not sent. ";
                        }
                        if ($result) $msg = "<b>Your information is entered into the database. </b>";
                        //Insert auth code into database
                        $sql = "insert into USER values(null, null, '".$code."')";
                    }

                    header("Location:login.php");
                }
            }
        }
}

?>

<!-- Wrapper -->
<div id="wrapper">

    <form action="registration.php" method="post">

        <?php print $msg; ?>

        <label for="firstName">First Name: <?php print $firstNameRequired; ?></label>
        <input type="text" id="firstName" placeholder="Bobby" name="firstName" value="<?php print $fName; ?>" required>

        <label for="lastName">Last Name: <?php print $lastNameRequired; ?></label>
        <input type="text" id="lastName" placeholder="Tables" name="lastName" value="<?php print $lName; ?>" required>

        <label for="email">Email: <?php print $emailRequired; ?></label>
        <input type="email" id="email" placeholder="btables@iupui.edu" name="email" value="<?php print $eMail; ?>" required>

        <label for="emailConfirm">Confirm Email:</label>
        <input type="email" id="emailConfirm" name="emailConfirm" placeholder="Please Confirm Email" value="<?php print $emailConfirmation; ?>" required>

        <label for="password">Password: Must contain 10-18 characters, with at least 1 letter and 1 number. <?php print $passwordRequired; ?></label>
        <input type="password" id="password" name="password" value="<?php print $pWord; ?>" required>

        <label for="password">Confirm Password:</label>
        <input type="password" id="passwordConfirm" placeholder="Please Confirm Password" name="passwordConfirm" value="<?php print $passwordConfirmation; ?>" required>

        <label for="homePhone">Home Phone: (Format: 555-555-5555)</label>
        <input type="text" id="homePhone" placeholder="Please Enter Home Phone #" name="homePhone" value="<?php print $homePhone; ?>" required>

        <label for="cellPhone">Cell Phone: (Format: 555-555-5555)</label>
        <input type="text" id="cellPhone" placeholder="Please Enter Cell Phone #" name="cellPhone" value="<?php print $cellPhone; ?>" required>

        <label>Terms and Conditions: <?php print $termsReq; ?></label>
        <input type="checkbox" id="terms" value="terms" name="terms" required><label class="light" for="terms">I Agree to the Terms and Conditions:</label>

        <button name="enter" class="btn" type="submit">Sign Up</button>
    </form>

    <!-- Footer -->
    <?php
    include "./footer.php"
    ?>

</div>

<!-- Scripts -->
<script src="../assets/js/jquery.min.js"></script>
<!--<script src="assets/js/main.js"></script> -->

</body>
</html>