<?php
include "useraccount.php";

if($_POST) {
	if($_POST['password'] == '' || $_POST['email'] == '')  die("please fill in out fields");
	if($_POST['password'] != $_POST['passrep']) die("Passwords do not match confirmation password");
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT); //hash password
	$email = $_POST['email'];

	//check if email is in valid format
	if(filter_var($email, FILTER_VALIDATE_EMAIL) === false) die("Account Creation Failed. Please enter a valid E-Mail."); 
	$useraccount = new useraccount($email, $password, true); // create new account
}
?>