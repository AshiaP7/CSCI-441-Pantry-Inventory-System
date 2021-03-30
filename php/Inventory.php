<?php
include "useraccount.php";

class cInventory extends useraccount {
		private $BarCode;
		private $Name;
		
		public function __construct() {
			return parent::__construct();
		}
		
		public function AddToInventory($upc, $name, $image, $quantity) {
			//$accountid = parent::getaccountid();
			$accountid = 6;
			$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
			if($mysqli->connect_errno) {
				//echo "There was a problem connecting to server. Contact Admin.";
				return "There was a problem connecting to server. Contact Admin.";
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
				return "There was a problem connecting to server. Contact Admin Error #2 ($accountid, $itemid, $quantity).";
			}
			
			/*This query only runs if Item does not exist in items table or pantryinventory -> note it shouldnt be-able to exist
				in pantryinventory if it does not in items table because of the FK restriction CASCAD to itemid on table pantryinventory.
			*/
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
			return true;
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
			
			$query = "SELECT pantryinventory.id, pantryinventory.quantity, pantryinventory.accountid, items.name, items.image FROM pantryinventory INNER JOIN items ON pantryinventory.accountid = items.accountid WHERE pantryinventory.accountid = $accountid;";
			$result=$mysqli->query($query);
			if($result == false) {
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
}
?>