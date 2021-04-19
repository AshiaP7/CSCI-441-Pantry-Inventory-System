<?php
include_once "useraccount.php";
//include_once 'PHPHTMLDOM/simple_html_dom.php';
class cingredient {
	public $ingredient;
	public $measurement;
	public $unit;
}


class cRecipelist extends useraccount {
		private $Name;
		
		public function __construct() {
			parent::__construct();
		}
		
		public function AddRecipe($name, $preptime, $nationality, $dietaryrestrictions, $foodtype, $img, $serving, $ingredient, $step) {
				//ingredient will be an object with veriables ingredient, measurement, unit
				//step is just a 1-n string array.
			$accountid = parent::getaccountid();
			$data = array();
			$data['result'] = false;
			$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
			if($mysqli->connect_errno) {
				//echo "There was a problem connecting to server. Contact Admin.";
				return $data;
			}
			//sql table recipes -> id, name, preptime, nationality, dietaryrestrictions, foodtype, servingsize, accountid, spoonid
			//sql table personalrecipes -> id, accountid, recipeid
			$query = "INSERT INTO recipes (name, preptime, nationality, dietaryrestrictions, foodtype, servingsize, accountid, spoondid) VALUES('$name', '$preptime', '', '', '', '$serving', '$accountid', '-1'); INSERT INTO pantryrecipe (name, img, serving) VALUES('$accountid', '$itemid', '$quantity')";
			$result=$mysqli->query($query);
			if($result == true) {
				$data['result'] = true;
			}
			$mysqli->close();
		}
		
		public function RemoveRecipe($id) {
			$accountid = parent::getaccountid();
			$data = array();
			$data['result'] = false;
			$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
			if($mysqli->connect_errno) {
				//echo "There was a problem connecting to server. Contact Admin.";
				return $data;
			}
			//sql table recipes -> id, name, preptime, nationality, dietaryrestrictions, foodtype, servingsize, accountid, spoonid
			//sql table personalrecipes -> id, accountid, recipeid
			$query = "DELETE FROM personalrecipes WHERE accountid = '$accountid' AND id = '$id';";
			$result=$mysqli->query($query);
			if($result == true) {
				$data['result'] = true;
			}
			$mysqli->close();
		}
		
		public function UpdateRecipe($id, $name, $img, $serving, $ingredient, $step) {
			
		}
		public function getRecipes($id, $fav, $dislike) {
			$accountid = parent::getaccountid();
			$data = array();
			$data['result'] = false;
			$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
			if($mysqli->connect_errno) {
				//echo "There was a problem connecting to server. Contact Admin.";
				return $data;
			}
				if($id != 0) { //specific id for editing
					//get recipe and ingredients. since requesting id it is requesting all data for recipe
					$query = "SELECT * FROM recipes WHERE accountid = $accountid AND id = $id;";
					$result=$mysqli->query($query);
					$myArray = array();
					while($row = $result->fetch_array(MYSQLI_ASSOC)) {
						$myArray[] = $row;
					}
					$query = "SELECT * FROM ingredients WHERE recipeid = $id;";
					$result=$mysqli->query($query);
					$ingredientlist = array();
					while($row = $result->fetch_array(MYSQLI_ASSOC)) {
						$ingredientlist[] = $row;
					}
					$query = "SELECT * FROM recipestep WHERE recipeid = $id;";
					$result=$mysqli->query($query);
					$recipestep = array();
					while($row = $result->fetch_array(MYSQLI_ASSOC)) {
						$recipestep[] = $row;
					}
					$data['result'] = true;
					$data['recipe'] = $myArray[0];
					$data['ingredients'] = $ingredientlist;
					$data['recipestep'] = $recipestep;
					$mysqli->close();
					return $data;
				}
				else { 
					$query = "SELECT * FROM recipes WHERE accountid='$accountid'";
					$result=$mysqli->query($query);
					if($result == false) {
						$mysqli->close();
						return $data;
					}
					$myArray = array();
					while($row = $result->fetch_array(MYSQLI_ASSOC)) {
						$myArray[] = $row;
					}
					$query = "SELECT * FROM favdislikes INNER JOIN recipes ON recipes.id = favdislikes.recipeid  WHERE favdislikes.accountid='$accountid' AND favdislikes.spoonid = 0;";
					$result=$mysqli->query($query);
					if($result == false) {
						$mysqli->close();
						return $data;
					}
					$favdislike = array();
					while($row = $result->fetch_array(MYSQLI_ASSOC)) {
						$favdislike[] = $row;
					}
					//get spoonid recipe information.. need api request here then insert the recipe information appended to $favdislike array
					$query = "SELECT * FROM favdislikes WHERE accountid='$accountid' AND spoonid <> 0;"; //does not = 0
					$result=$mysqli->query($query);
					//loop results and request spoonid information.
				}
				$mysqli->close();
				$data['result'] = true;
				$data['recipe'] = $myArray;
				$data['favdis'] = $favdislike;
				return $data;
		}
}
?>