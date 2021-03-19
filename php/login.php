<?
include "useraccount.php";
error_reporting(E_ERROR | E_PARSE);

class jsonobj {
	private $result;
	private $msg;
	public function __construct($result, $msg) {
		$this->result = $result;
		$this->msg = $msg;
	}
	public function jsonSerialize() {
		return [
			'result' => $this->result,
			'msg' => $this->msg
		];
	}
}

if($_POST) {
if(!isset($_POST['email'])) die("invalid email");
if(!isset( $_POST['password'])) die("Invalid password input");
$email = $_POST['email'];
$password = $_POST['password'];

$useraccount = new useraccount($email, $password);

if($useraccount->validation == false) {
	header("Content-Type: application/json");
	$obj = new jsonobj(false, "sign on failed for this reason."); //maybe have class throw error messages to here.
	//json here for failed login
	echo json_encode($obj->jsonSerialize());
	exit();
}
header("Content-Type: application/json");
$obj = new jsonobj(true, "sign on success");
//json here for success login + message
echo json_encode($obj->jsonSerialize());
}
else {
	if(isset($_GET['logoff'])) {
		header("Content-Type: application/json");
		$useraccount = new useraccount();
		$value = false;
		if($useraccount->validation == true) {
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