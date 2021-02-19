<?php
include "sendmail.php"
if($_POST) {
	$username = $_POST['username'];
	if($_POST['password'] != $_POST['passrep']) {
		echo "Passwords do not match";
		exit();
	}
	$email = $_POST['email'];
	
	//check if username already in db
	//check email valid
	//update the db
	//send email
	$hashuser = hash(sha256, $username, false);
	$mailcontent = "Please confirm your email address by clicking the link: <a href='http://hbprophecy.com/school/php/mailconfirm.php?key=123'>http://hbprophecy.com/school/php/mailconfirm.php?user=$uesrname&key=$hashuser</a>"
	SendEmail($email, $mailcontent);
}
?>