<?
/*
script will only allow get from suggested domain and then request JSON from spoonacular and relay back to client.
*/
header("Content-Type: application/json");
error_reporting(0); //need no post of warnings or errors as it could changes the json output
$apikey = "fbd4007d4eae44aebd9d387fc1a9292c"; //your api key here

$allowed = array('hbprophecy.com', '192.168.0.2', '127.0.0.1'); //allowed domains
$domainname = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
if(!in_array($domainname, $allowed)){
	echo json_encode(array("results" => 'false'));
	exit();
}
if($_GET){
	if(isset($_GET['search'])) {
		$json = file_get_contents("https://api.spoonacular.com/recipes/search?apiKey=" . $apikey . "&query=" . $_GET['search']);
	}
	else if (isset($_GET['stepurl'])) {
		$json = file_get_contents("https://api.spoonacular.com/recipes/extract?apiKey=" . $apikey . "&url=" . $_GET['stepurl']);
	}
	else {
		exit();
	}
	echo $json;
}
?>