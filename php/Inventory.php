<?php
include "useraccount.php";

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
			$query = "SELECT id, upc FROM items WHERE upc = '$upc' LIMIT 1;";
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
				
				$mysqli->close();
				if($result == true) $data['result'] = true;
				return $data;
			}
			
			/*This query only runs if Item does not exist in items table or pantryinventory -> note it shouldnt be-able to exist
				in pantryinventory if it does not in items table because of the FK restriction CASCAD to itemid on table pantryinventory.
			*/
			$query = "INSERT INTO items (upc, name, image)  VALUES ('$upc', '$name', '$image');
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
			
			$query = "SELECT pantryinventory.id, pantryinventory.quantity, pantryinventory.accountid, items.name, items.image, items.upc FROM pantryinventory INNER JOIN items ON pantryinventory.itemid = items.id WHERE pantryinventory.accountid = $accountid;";
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
		public function updateInventory($id, $value) {
			$data = array();
			$data['result'] = false;
			if($this->validation == false) return $data;
			$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
			if($mysqli->connect_errno) {
				return $data;
			}
			if($value > 0) $query = "UPDATE pantryinventory SET quantity = '$value' WHERE id = $id;";
			else $query = "DELETE FROM pantryinventory WHERE id = $id;";
			$result=$mysqli->query($query);
			if($result == false) {
				$mysqli->close();
				return $data;
			}
			$mysqli->close();
			$data['result'] = true;
			return $data;
		}
}
?>