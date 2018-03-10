<?php
//$filename = readline("Input File: ");
$filename = "Q2Ratings.txt";
$myfile = fopen($filename, "r");

define("MAX_RATE", intval(readline("Max Rate: ")));
define("ALPHA", floatval(readline("Alpha: ")));


$db = new mysqli("localhost", "psec", "password");
$db->query("DROP DATABASE psec_assessment;");
$table_setup = "
	CREATE DATABASE psec_assessment;
	USE psec_assessment;
	CREATE TABLE ratings(
		id INT AUTO_INCREMENT PRIMARY KEY,
		customer_id INT,
		product_id INT,
		rating INT
	);
	CREATE TABLE customers(
		id INT AUTO_INCREMENT PRIMARY KEY,
		trust_level FLOAT
	);
	CREATE TABLE products(
		id INT AUTO_INCREMENT PRIMARY KEY,
		rating FLOAT
	);
";
$db->multi_query($table_setup);
while($db->more_results()){
	$res = $db->next_result();
}