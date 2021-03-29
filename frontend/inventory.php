<?php
session_start();
	if(!isset($_SESSION['email'])) exit();
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>Pantry Inventory System</title>
		<link rel="stylesheet" href="style.css" />
<script src="request.js"></script>
<link rel="stylesheet" href="style.css">
</head>
<body>
 <div class="page">
		<div class="header">
			<a href="index.html" id="logo"><img src="images/Pantry Inventory.png"> </a>
				<ul id=menu>
					<li class="selected"><a href="index.html">Home</a></li>
					<li><a href="accountcreate.html">Sign Up</a></li>
					<li id="loginlink"><a href="login.html">Login</a></li>
				</ul>
			</div>
			<div class="body">
				<div id="featured">
					<?php
						include "php\inventory.php";
						$account = new cInventory();
						if($account->validation == true) {
							//mysql and post all items here can use function in useraccount();
							echo $account->AddToInventory('00000', 'Test', '', 5);
						}
						
					?>
				</div>
<ul class="signup">
					<li>
						<div>
							<img src="images/pastries.jpg" alt=""/>
							<p>Savory recipes to make your mouth water.</p>
						</div>
					</li>
					<li>
						<div>
							<img src="images/fruits.jpg" alt=""/>
							<p>Sweet treats in a wide variety.</p>
						</div>
					</li>
					<li>
						<div>
							<img src="images/dinner.png" alt=""/>
							<p>Any meal that you want to cook our Pantry Inventory System can help.</p>
						</div>
					</li>
				</ul>
			</div>
			<div class="footer">
				<ul>
					<li><a href="index.html">Home</a></li>
					<li><a href="accountcreate.html">Sign Up</a></li>
					<li><a href="login.html">Login</a></li>
				</ul>
				<p>&#169; Copyright &#169; 2021. Pantry Inventory System all rights reserved</p>
			</div>
		</div>
<div id='showingredients'></div><br>
<div id='showsteps'></div>
<br>
<div id='showurl'></div>
</body>
</html>