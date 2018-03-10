<?php

$attack_type = readline("Attack Type (slander/promote): ");
require_once("run.php");

for($j = 0; $j < 5; $j++){
	for($i = 0; $i < 5; $i++){
		//Rating is 0 if slander, MAX_RATE if self-promoting
		$rating = ($attack_type == "slander") ? 0: MAX_RATE;
		//Null customer rating- creates new customer id
		//product id = 29, as stated in question
		log_new_rating($db, null, 4, $rating);
	}

	$base_output = "output/" . ucfirst($attack_type) . "_" . strval($attack * ($j + 1)) . "_Alpha_" . strval(ALPHA) . "_";
	require("output.php");
}