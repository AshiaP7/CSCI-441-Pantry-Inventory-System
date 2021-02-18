<?php
if(isset($_GET['key'])){
	//unhash maybe just hash the username?
	//$account=unhashedkey
	$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "website");
  if($mysqli->connect_errno) {
	die("There was a problem connecting to server. Contact Admin.");
  }
  $query = "UPDATE account SET emailconfirmed = 1 WHERE username = $account";
  $result = $mysqli->query($query);
  
  
  $mysqli->close();
}
else {
	echo "Invalid key";
}
?>