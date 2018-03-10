<?php
require_once("run.php");

$attack = intval(readline("Attack Size: "));
$attack_type = readline("Attack Type (slander/promote): ");

for($i = 0; $i < $attack; $i++){
	//Rating is 0 if slander, MAX_RATE if self-promoting
	$rating = ($attack_type == "slander") ? 0: MAX_RATE;
	//Null customer rating- creates new customer id
	//product id = 29, as stated in question
	log_new_rating($db, null, 29, $rating);
}


$fn = "output/" . ucfirst($attack_type) . "_" . strval($attack) . "_Alpha_" . strval(ALPHA) . "_";
require_once("output.php");