<?php
include "sendmail.php";

define("mysqlip", "127.0.0.1");
define("mysqluser","user");
define("mysqlpass", "password");

if($_POST) {
	$username = $_POST['username'];
	if($_POST['password'] == '' || $_POST['username'] == '' || $_POST['email'] == '' || $_POST['question'] == '' || $_POST['answer'] == '')  die("please fill in out fields");
	if($_POST['password'] != $_POST['passrep']) die("Passwords do not match confirmation password");
	$password = $_POST['password'];
	$email = $_POST['email'];
	$question = $_POST['question'];
	$answer = $_POST['answer'];
	
	if(strlen($username) < 5) die("Account must be at least 5 characters long.");
	if(strlen($username) > 15) die("Account must be less then 15 characters long.");

	//check if email is in valid format
	if(filter_var($email, FILTER_VALIDATE_EMAIL) === false) die("Account Creation Failed. Please enter a valid E-Mail."); 
	
	
	$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
	if($mysqli->connect_errno) {
	  die("There was a problem connecting to server. Contact Admin.");
	}
	//------------check if user exist-------//
	$query = "SELECT * FROM account WHERE username = '$username'";
	$result=$mysqli->query($query);
	$num=$result->num_rows;
	if($num > 0) {
		$mysqli->close();
		die('Account name already exist');
	}
	
	//------add account to db-------------//
	$query = "INSERT INTO account (username, email, password, timestamp, emailconfirmed)  VALUES ('$username', '$email', '$password', '2021-01-01 01:00:00', '0')";
	$result=$mysqli->query($query);
	if($result == FALSE) { 
		echo "There was a problem. contact admin for help. ";
		$mysqli->close();
		die();
	}
	$mysqli->close();
	
	$hashemail = hash('sha256', $email, false);
	$mailcontent = "Please confirm your email address by clicking the link: <a href='http://hbprophecy.com/school/php/mailconfirm.php?user=$username&key=$hashemail'>http://hbprophecy.com/school/php/mailconfirm.php?user=$username&key=$hashemail</a>";
	SendEmail($email, $mailcontent);
}
?>