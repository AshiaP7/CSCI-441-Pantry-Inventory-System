<?php
include_once "useraccount.php";
//include_once 'PHPHTMLDOM/simple_html_dom.php';
class cingredient {
	public $ingredient;
	public $measurement;
	public $unit;
}
class favobj {
	public $id;
	public $spoonid;
}


class cRecipelist extends useraccount {
		private $Name;
		private $apikey = "fbd4007d4eae44aebd9d387fc1a9292c";
		
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
			$query = "SELECT name FROM recipes WHERE name = '$name'";
			$result=$mysqli->query($query);
			if($result->num_rows > 0) {
				$data['message'] = "Name Duplicated";
				return $data;
			}
			$query = "INSERT INTO recipes (name, preptime, nationality, dietaryrestrictions, foodtype, servingsize, accountid, spoonid) VALUES('$name', '$preptime', '$nationality', '$dietaryrestrictions', '$foodtype', '$serving', '$accountid', '0');";
			$result=$mysqli->query($query);
			if($result == true) {
				$recid = $mysqli->insert_id;
				foreach($ingredient as $ing) {
					$query = "INSERT INTO ingredients (recipeid, name, quantity, measurement) VALUES('$recid', '". $ing['name'] . "', '" . $ing['measure'] . "', '" . $ing['unit'] . "');";
					$result=$mysqli->query($query);
					
				}
				foreach($step as $st) {
					$query = "INSERT INTO recipestep (step, num, recipeid) VALUES('". $st['content'] . "', '" . $st['id'] . "', '$recid');";
					$result=$mysqli->query($query);
				}
				$data['result'] = true;
				return $data;
			}
			echo "query fail: $query";
			$mysqli->close();
		}
		
		/*public function RemoveRecipe($id) {
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
		}*/
		
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
					$query = "SELECT favdislikes.id, favdislikes.accountid, favdislikes.recipeid, favdislikes.spoonid, favdislikes.like, recipes.name, recipes.preptime, recipes.nationality, recipes.dietaryrestrictions, recipes.foodtype, recipes.servingsize, recipes.spoonid FROM favdislikes INNER JOIN recipes ON recipes.id = favdislikes.recipeid  WHERE favdislikes.accountid='$accountid' AND favdislikes.spoonid = 0;";
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
					if($result != false) {
						$urlspoon = "https://api.spoonacular.com/recipes/informationBulk?apiKey=" . $this->apikey . "&ids=";
						$total = 0;
						$ListOfObjects[] = array();
						while ($row = $result->fetch_assoc()) {
							
							//bulk url https://api.spoonacular.com/recipes/informationBulk?apiKey=fbd4007d4eae44aebd9d387fc1a9292c&ids=1,2
							$urlspoon .= $row['spoonid'] . ",";
							$ListOfObjects[$total] = new favobj;
							$ListOfObjects[$total]->id = $row['id'];
							$ListOfObjects[$total]->spoonid = $row['spoonid'];
							$total++;
						}
						if($total > 0) {
							$urlspoon = rtrim($urlspoon, ","); //trim comma from the last spoonid.
							$json = file_get_contents($urlspoon);
							$decode = json_decode($json, true);
							
							//unshift will add at the top.
							//array_unshift($decode['results'], array('id' => 0, 'name' => $sptitle, 'preptime' => $spprep, 'spoonid' => $row['id']));
							foreach($decode as $list) { //loop list json output
								if(isset($list['cuisines'])) {
									switch($list['cuisines']) {
										default:
											$nationality = $list['cuisines'][0];
										break;
									}
								}
								foreach($ListOfObjects as $favid) 
								if($favid->spoonid == $list['id']) {
									array_push($favdislike, array('id' => $favid->id, 'name' => $list['title'], 'preptime' => $list['readyInMinutes'], 'nationality' => $nationality, 'dietaryrestrictions' => $list['diets'][0], 'foodtype' => $list['dishTypes'][0], 'servingsize' => $list['servings'], 'accountid' => (int)$accountid, 'spoonid' => $list['id']));
								}
							}
						}
					}
				}
				$mysqli->close();
				$data['result'] = true;
				$data['recipe'] = $myArray;
				$data['favdis'] = $favdislike;
				return $data;
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
			
			$query = "DELETE FROM favdislikes WHERE recipeid = $id";
			$result=$mysqli->query($query);
			
			$query = "DELETE FROM recipestep WHERE recipeid = $id";
			$result=$mysqli->query($query);
			
			$query = "DELETE FROM ingredients WHERE recipeid = $id";
			$result=$mysqli->query($query);
			
			$query = "DELETE FROM recipes WHERE id = $id AND accountid='$accountid'";
			$result=$mysqli->query($query);
			if($result == false) {
				return $data;
			}
			$mysqli->close();
			$data['result'] = true;
			return $data;
		}
		
		public function RemoveFavDisRecipe($id) {
			$accountid = parent::getaccountid();
			$data = array();
			$data['result'] = false;
			$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
			if($mysqli->connect_errno) {
				//echo "There was a problem connecting to server. Contact Admin.";
				return $data;
			}
			$query = "DELETE FROM favdislikes WHERE id = $id AND accountid='$accountid'";
			$result=$mysqli->query($query);
			if($result == false) {
				$data['message'] = $query;
				return $data;
			}
			$mysqli->close();
			$data['result'] = true;
			return $data;
		}
}
?>