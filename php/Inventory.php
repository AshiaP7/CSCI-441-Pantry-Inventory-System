<?php
include "useraccount.php";
include_once 'PHPHTMLDOM/simple_html_dom.php';

class cInventory extends useraccount {
		private $BarCode;
		private $Name;
		
		public function __construct() {
			parent::__construct();
		}
		
		public function AddToInventory($upc, $name, $image, $quantity) {
			$accountid = parent::getaccountid();
			$data = array();
			$data['result'] = false;
			$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
			if($mysqli->connect_errno) {
				//echo "There was a problem connecting to server. Contact Admin.";
				return $data;
			}
			$condition = "WHERE upc = '$upc'";
			if($upc == '') $condition = "WHERE name = '$name' AND accountid = $accountid";
			$query = "SELECT id, upc, name FROM items $condition LIMIT 1;";
			$result=$mysqli->query($query);
			if($result == true && $result->num_rows > 0) {
				//echo "Test) duplicated entry";
				//if already exist in item dB we add it with the item id here and run query to inert ignore into pantryinventory
				//we do ignore because itemid is unuqe and if duplicate exist it will throw and error and we ignore the insert
				//we also need in the query to On DUPLICATE UPDATE -> To update the quantity
				//if all querys process well here we will return true rather then false
				$obj = $result->fetch_object();
				$itemid = $obj->id;
				//on duplicate update the quantity.
				$query = "INSERT INTO pantryinventory (accountid, itemid, quantity) VALUES('$accountid', '$itemid', '$quantity') ON DUPLICATE KEY UPDATE quantity=quantity + $quantity;";
				$result=$mysqli->query($query);
				if($result == true) {
					$data['result'] = true;
				}
				$mysqli->close();
				return $data;
			}
			
			/*
			verify information insert is valid with if upc code is included and item does not exist. if it can be verified then insert accountid= 0 for public use.
			*/
			/*if($upc != '') {
				$content = file_get_html("https://www.upcdatabase.com/item/$upc");
				$row = $content->find('tr');
				$row[0]->
			}*/
			$query = "INSERT INTO items (upc, name, image, accountid)  VALUES ('$upc', '$name', '$image', '$accountid');
			INSERT INTO pantryinventory (accountid, itemid, quantity) VALUES('$accountid', LAST_INSERT_ID(), '$quantity');";
			$mysqli->multi_query($query);
			do {
				if($result = $mysqli->store_result()) {
					if($result == false) {
						//echo "There was a problem. please contact admin for help. Statment #2 $query";
						$result->free();
						$mysqli->close();
						return "There was a problem connecting to server. Contact Admin.";
					}

				}
			} while($mysqli->next_result());
			$mysqli->close();
			$data['result'] = true;
			return $data;
		}
		
		public function RemoveFromInventory() {
			$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
			if($mysqli->connect_errno) {
				//echo "There was a problem connecting to server. Contact Admin.";
				return false;
			}
		}
		
		public function GetInventory() {
			$accountid = parent::getaccountid();
			$data = array();
			$data['result'] = false;
			$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
			if($mysqli->connect_errno) {
				//echo "There was a problem connecting to server. Contact Admin.";
				return $data;
			}
			
			$query = "SELECT pantryinventory.id, pantryinventory.quantity, pantryinventory.accountid, pantryinventory.itemid, items.name, items.image, items.upc FROM pantryinventory INNER JOIN items ON pantryinventory.itemid = items.id WHERE pantryinventory.accountid = $accountid;";
			$result=$mysqli->query($query);
			$data['accid'] = $this->getaccountid();
			if($result == false) {
				//$data['mysql'] = $mysqli->error; //remove may just have a local log.
				$mysqli->close();
				return $data;
			}
			$myArray = array();
			while($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$myArray[] = $row;
			}
			$mysqli->close();
			$data['result'] = true;
			$data['item'] = $myArray;
			return $data;
			//loop and send data in json format.
		}
		public function updateInventory($id, $value, $itemid) {
			$accountid = parent::getaccountid();
			$data = array();
			$data['result'] = false;
			if($this->validation == false) return $data;
			$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
			if($mysqli->connect_errno) {
				return $data;
			}
			if($value > 0)  {
				$query = "UPDATE pantryinventory SET quantity = '$value' WHERE id = $id;";
				$result=$mysqli->query($query);
				if($result == false) {
					$mysqli->close();
					return $data;
				}
			}
			else {
				$query = "DELETE FROM pantryinventory WHERE id = $id; DELETE IGNORE FROM items WHERE id = $itemid AND accountid = $accountid;";
				$mysqli->multi_query($query);
				do {
					if($result = $mysqli->store_result()) {
						if($result == false) {
							//echo "There was a problem. please contact admin for help. Statment #2 $query";
							$result->free();
							$mysqli->close();
							return $data;
						}

					}
				} while($mysqli->next_result());
			}

			$mysqli->close();
			$data['result'] = true;
			return $data;
		}
}
?>