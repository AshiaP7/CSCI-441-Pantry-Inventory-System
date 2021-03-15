<?php
include "sendmail.php";

define("mysqlip", "127.0.0.1");
define("mysqluser","user");
define("mysqlpass", "password");

class useraccount {
	private $email;
	public function __construct($email = null, $pw = null) {
		//if user and pw  not null get from session vars
		if($email != null || $pw != null) {
			if($this->verifyuser($email, $pw) == true) {
				session_start();
				$_SESSION['email'] = $email;
				$_SESSION['start'] = time(); // saving a time stamp for timeouts
				$this->email = $email;
				return true;
			}
			else {
				return false;
			}
		}
		else {
			if(isset($_SESSION['email'])) {
				session_start();
				$this->email = $_SESSION['email'];
				return true;
			}
		}
		//new signin
	}
	private function verifyuser($lemail, $lpassword) { //bool return
		$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
		  if($mysqli->connect_errno) {
			echo "There was a problem connecting to server. Contact Admin.";
			//Throw error here
			return false;
		  }
		  $query = "SELECT * FROM account WHERE email = '$lemail'";
			$result = $mysqli->query($query);
			$obj = $result->fetch_object();
			$email=$obj->email;
			$password=$obj->password;
			if(($email == $lemail) && (password_verify($password, $lpassword) == true)) {
				$mysqli->close();
				return true;
			}
			$mysqli->close();
			return false;
	}
	
	public function signoff() {
		session_start(); //some reason php needs a start session before destroy session even when one has been created.
		session_destroy(); //destroy session
		if(!isset($_SESSION['email'])) {
			
			return true;
		} else return false;
	}
	
	public function createAccount($email, $password) {
		$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
		if($mysqli->connect_errno) {
		  echo "There was a problem connecting to server. Contact Admin.";
		  return;
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
	public function updateaccount($email, $password) {

	}
}
?>