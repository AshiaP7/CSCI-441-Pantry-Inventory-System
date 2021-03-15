<?
include "useraccount.php";
if($_POST) {
if(!isset($_POST['email'])) die("invalid email");
if(!isset( $_POST['password'])) die("Invalid password input");
$email = $_POST['email'];
$password = $_POST['password'];

$useraccount = new useraccount($email, $password);

if($useraccount->validation == false) {
die("Sign in failed");
}
echo "Sign in Success";
}
else {
	if(isset($_GET['logoff'])) {
		header("Content-Type: application/json");
		$useraccount = new useraccount();
		$value = false;
		if($useraccount->validation != false) {
			if($useraccount->signoff() == true)  $value = true;
		}
		
		$buildjson = array("result"=>$value);
		echo json_encode($buildjson);
	}
	else {
		header("Content-Type: application/json");
		$useraccount = new useraccount();
		$value = false;
		if($useraccount->validation == false) $value = false; //should send this json
		else $value = true;
		$buildjson = array("result"=>$value);
		echo json_encode($buildjson);
	}
}
?>