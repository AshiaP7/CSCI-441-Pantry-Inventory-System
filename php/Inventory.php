<?php
include "useraccount.php";

class cInventory extends useraccount {
		private $BarCode;
		private $Name;
		
		public function __construct() {
			return parent::__construct();
		}
		
		public function AddToInventory($upc, $name, $image, $accountid) {
			$mysqli = mysqli_connect(mysqlip, mysqluser, mysqlpass, "school");
			if($mysqli->connect_errno) {
				//echo "There was a problem connecting to server. Contact Admin.";
				return false;
			}
			$query = "SELECT id, upc FROM items WHERE upc = '$upc' LIMIT 1;";
			$result=$mysqli->query($query);
			if($result == true && $result->num_rows > 0) {
				//echo "Test) duplicated entry";
				//if already exist in item dB we add it with the item id here and run query to inert ignore into pantryinventory
				//we do ignore because itemid is unuqe and if duplicate exist it will throw and error and we ignore the insert
				//we also need in the query to On DUPLICATE UPDATE -> To update the quantity
				//if all querys process well here we will return true rather then false
				$mysqli->close();
				return false;
			}
			
			/*This query only runs if Item does not exist in items table or pantryinventory -> note it shouldnt be-able to exist
				in pantryinventory if it does not in items table because of the FK restriction CASCAD to itemid on table pantryinventory.
			*/
			$query = "INSERT INTO items (upc, name, image, accountid)  VALUES ('$upc', '$name', '$image', '$accountid');
			INSERT INTO pantryinventory (accountid, itemid, quantity) VALUES('6', LAST_INSERT_ID(), '0');";
			$mysqli->multi_query($query);
			do {
				if($result = $mysqli->store_result()) {
					if($result == false) {
						//echo "There was a problem. please contact admin for help. Statment #2 $query";
						$result->free();
						$mysqli->close();
						return false;
					}

				}
			} while($mysqli->next_result());
			$mysqli->close();
			return true;
		}
		
		public function RemoveFromInventory() {
			
		}
		
		public function GetInventory() {
			
		}
}
?>