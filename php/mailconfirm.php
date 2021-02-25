<?php
define("mysqlip", "127.0.0.1");
define("mysqluser","user");
define("mysqlpass", "password");

if(isset($_GET['key']) && isset($_GET['user'])){
	//unhash maybe just hash the username?
	//$account=unhashedkey
	$username = $_GET['user'];
	$chash = $_GET['key'];
	$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
  if($mysqli->connect_errno) {
	die("There was a problem connecting to server. Contact Admin.");
  }
  $query = "SELECT account * WHERE username = $username";
  $result = $mysqli->query($query);
  if($result) {
	$num=$result->num_rows;
	if($num == 0) { die("failed to confirm email."); $mysqli->close(); }
	//get email from query and hash it + compare
	$obj = $result->fetch_object();
	$semail = $obj->email;
	$ehash = hash('sha256', $semail, false);;
	if($ehash != $chash)  { die("failed to confirm email."); $mysqli->close(); } //emails key do not mach
  }
  
  $query = "UPDATE account SET emailconfirmed = 1 WHERE username = '$username';";
  $result = $mysqli->query($query);
  if($result) {
	  echo "Account email has been confirmed";
  }
  else {
	  echo "There was an error confirming the email for this account.";
  }
  
  $mysqli->close();
}
else {
	echo "Invalid key";
}
?>