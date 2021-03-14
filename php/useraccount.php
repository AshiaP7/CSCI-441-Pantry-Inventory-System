<?php
include "sendmail.php";

define("mysqlip", "127.0.0.1");
define("mysqluser","user");
define("mysqlpass", "password");

class useraccount {
	private $email;
	public function __construct($email = null, $pw = null) {
		//if user and pw  not null get from session vars
		//new signin
	}
	public function createAccount($email, $password) {
		$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
		if($mysqli->connect_errno) {
		  die("There was a problem connecting to server. Contact Admin.");
		}
		//------------check if user exist-------//
		$query = "SELECT * FROM account WHERE email = '$email'";
		$result=$mysqli->query($query);
		$num=$result->num_rows;
		if($num > 0) {
			$mysqli->close();
			echo 'Account name already exist';
			return;
		}
		
		//------add account to db-------------//
		$query = "INSERT INTO account (email, password, timestamp, emailconfirmed)  VALUES ('$email', '$password', '2021-01-01 01:00:00', '0')"; //need to update time stamp.
		$result=$mysqli->query($query);
		if($result == FALSE) { 
			echo "There was a problem. please contact admin for help. ";
			$mysqli->close();
			return;
		}
		$mysqli->close();
		
		$hashemail = hash('sha256', $email, false);
		$mailcontent = "Please confirm your email address by clicking the link: <a href='http://hbprophecy.com/school/php/mailconfirm.php?user=$username&key=$hashemail'>http://hbprophecy.com/school/php/mailconfirm.php?user=$username&key=$hashemail</a>";
		SendEmail($email, $mailcontent);
	}
	public function updateaccount() {

	}
}
?>