<?php
$myfile = fopen("Q2Ratings.txt", "r");

$db = new mysqli("localhost", "admin", "password");
$setup = "
	CREATE DATABASE psec_assessment;
	USE psec_assessment;
	CREATE TABLE ratings(
		id INT AUTO_INCREMENT PRIMARY KEY,
		user_id INT,
		product_id INT,
		rating INT
	);
	CREATE TABLE users(
		id INT AUTO_INCREMENT PRIMARY KEY,
		trust_level FLOAT
	);
	CREATE TABLE products(
		id INT AUTO_INCREMENT PRIMARY KEY,
		rating INT
	);
";
$db->query($setup);

while(!feof($myfile)){
	//FOR EACH RATING.. update the database:
	$data = explode(" ", fgets($myfile));

	//initialise trust level if new customer

	//Calculate overall product rating

	//Update trust levels of all customers who bought tis product

	//Recalculate all products other than project j
}

fclose($myfile)
?>