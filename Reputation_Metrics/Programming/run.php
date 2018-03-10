<?php
require_once("setup.php");
require_once("process.php");

while(!feof($myfile)){
	//FOR EACH RATING.. update the database:
	$data = explode(" ", fgets($myfile));
	if(count($data) != 3){break;}
	$customer_id = $data[0];
	$product_id = $data[1];
	$rating = $data[2];

	log_new_rating($db, $data[0], $data[1], $data[2]);	
}
fclose($myfile);

if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
	//Only run output if file was run DIRECTLY from console, NOT included in another file
	//i.e. attack.php
	$fn = "output/Alpha_" . strval(ALPHA) . "_";
	require_once("output.php");
}