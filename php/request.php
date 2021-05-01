<?
/*
script will only allow get from suggested domain and then request JSON from spoonacular and relay back to client.
script handle json
*/
include "Inventory.php";
include "recipe.php";
header("Content-Type: application/json");
//error_reporting(0); //need no post of warnings or errors as it could changes the json output
$apikey = "fbd4007d4eae44aebd9d387fc1a9292c"; //your api key here
$allowed = array('hbprophecy.com', '192.168.0.2', '127.0.0.1'); //allowed domains
$domainname = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);

/*if(!in_array($domainname, $allowed)){
	echo json_encode(array("results" => 'false'));
	exit();
}*/
//--------------------------Get request------------------------------//
if($_SERVER['REQUEST_METHOD'] === 'GET'){
	if(isset($_GET['search'])) {
		$recipelist = new cRecipelist();
		$json = file_get_contents("https://api.spoonacular.com/recipes/search?apiKey=" . $apikey . "&query=" . urlencode($_GET['search']));
		//append to json results array
		$decode = json_decode($json, true);
		$jsonapp = $recipelist->search($_GET['search']);
		//array_unshift($decode['results'], array('id' => 0, 'title' => 'test'));
		$json = json_encode($decode);
	}
	else if (isset($_GET['stepid'])) {
		//$json = file_get_contents("https://api.spoonacular.com/recipes/extract?apiKey=" . $apikey . "&url=" . urlencode($_GET['stepurl']));
		$json = file_get_contents("https://api.spoonacular.com/recipes/" . $_GET['stepid'] . "/information?apiKey=" . $apikey);
	}
	else if (isset($_GET['upc'])) {
		//call function here to add to inventory
		$inventory = new cInventory();
		if($inventory->validation == true) {
			//signed on
			$value = $inventory->AddToInventory($_GET['upc'], $_GET['name'], $_GET['image'], $_GET['accountid']);
			$json = json_encode(array("result"=>$value));
		}
		else {
			//not a signed on user.
			$json = json_encode(array("result"=>false));
		}
	}
	else if (isset($_GET['recipe'])) {
		$recipelist = new cRecipelist();
		$fav = false;
		$dis = false;
		if(isset($_GET['fav']) && $_GET['fav'] == 1) $fav = true;
		if(isset($_GET['dis']) && $_GET['dis'] == 1) $dis = true;
		if($recipelist->validation == true && !isset($_GET['remove'])) {
			$json = json_encode($recipelist->getRecipes($_GET['recipe'], $fav, $dis));
		}
		else if ($recipelist->validation == true && isset($_GET['remove']) && $_GET['remove'] == true) {
			$json = json_encode($recipelist->RemoveRecipe($_GET['recipe']));
		}
		else $json = "{result: 'false'}";
	}
	else if (isset($_GET['favrmv'])) {
		$recipelist = new cRecipelist();
		if($recipelist->validation == true) {
			$json = json_encode($recipelist->RemoveFavDisRecipe($_GET['favrmv']));
		} else $json = "{result: 'false'}";
	}
	else if (isset($_GET['inventory'])) {
		$inventory = new cInventory();
		if($inventory->validation == true) {
			$json = json_encode($inventory->GetInventory());
		}else $json = "{ result: 'false' }";
	}
	else if(isset($_GET['likeid'])) {
		//like
		$recipelist = new cRecipelist();
		$spoonid = 0;
		$id = 0;
		if(isset($_GET['spoonid'])) {
			$spoonid = $_GET['likeid'];
		}
		else {
			$id = $_GET['likeid'];
		}
		if(isset($_GET['remove'])) {
			$json = json_encode($recipelist->LikeDisLikeRecipeRemove($id, $spoonid, true));
		}
		else {
			$json = json_encode($recipelist->LikeDisLikeRecipe($id, $spoonid, true));
		}
	}
	else if(isset($_GET['dislike'])) {
		//dislike
		$recipelist = new cRecipelist();
		$spoonid = 0;
		$id = 0;
		if(isset($_GET['spoonid'])) {
			$spoonid = $_GET['dislike'];
		}
		else {
			$id = $_GET['dislike'];
		}
		if(isset($_GET['remove'])) $json = json_encode($recipelist->LikeDisLikeRecipeRemove($id, $spoonid, false));
		else $json = json_encode($recipelist->LikeDisLikeRecipe($id, $spoonid, false));
	}
	else {
		exit();
	}
	echo $json;
}
//-------------------------post request---------------------------//
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	//-----------------Normal Post request---------------------//
	if(isset($_POST['itemname'])) {
			$inventory = new cInventory();
			if($inventory->validation == true) {
				echo json_encode($inventory->AddToInventory('', $_POST['itemname'], '', $_POST['quantity']));
			}
	}
	else {
		//-------------------------Check Json post request-------------------//
		$data = json_decode(file_get_contents('php://input'), true);
		if($data['posttype'] == "updateinv") {
			$inventory = new cInventory();
			if($inventory->validation == true) {
				$json = json_encode($inventory->updateInventory($data['id'], $data['value'], $data['itemid']));
				echo $json;
			}
			else echo json_encode(array("error"=>"validation failed"));
		}
		else if ($data['posttype'] == "addrecipe") {
			$recipelist = new cRecipelist();
			$json = json_encode($recipelist->AddRecipe($data['recname'], $data['prep-time'], $data['nationality'], $data['dietrestriction'], $data['foodtype'], $data['image'], $data['serving'], $data['ingredient'], $data['step']));
			echo $json;
		}
		else {
			echo json_encode(array("result"=>false));
			echo "no json";
		}
	}
} else echo json_encode(array("error"=>"no post or get "));
?>