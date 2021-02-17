<?
if($_POST) {
if(!isset($_POST['username'])) exit();
if(!isset( $_POST['password'])) exit();
$username = $_POST['username'];
$password $_POST['password'];

//hash password
//comp to database
//mysqli connect via ip user & pw to select table account and search for username
$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "website");
  if($mysqli->connect_errno) {
	die("There was a problem connecting to server. Contact Admin.");
  }
  $query = "SELECT * FROM account WHERE name = '$username'";
  	$result = $mysqli->query($query);
	$num=$result->num_rows;
	$obj = $result->fetch_object();
	$accountname=$obj->name;
	$AccPass=$obj->password;
	if(($accountname == $username) && (password_verify($password, $AccPass) == true)) {
		//login success update query for time stamps here
		
		//----save veriables of current session with server.
		$_SESSION['account'] = $username;
		$_SESSION['password'] = $password;
		$_SESSION['start'] = time(); // saving a time stamp for timeouts
	}
	else {
		echo "Login Failed";
		exit();
	}
	$mysqli->close();
}
?>