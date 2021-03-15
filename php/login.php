<?
include "useraccount.php";
if($_POST) {
if(!isset($_POST['email'])) die("Invalid email");
if(!isset( $_POST['password'])) die("Invalid password input");
$email = $_POST['email'];
$password = $_POST['password'];

$useraccount = new useraccount($email, $password);

if($useraccount == false) {
die("Sign in failed");
}
echo "Sign in Success";
}
?>