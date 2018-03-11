<?php

//$attack_type = readline("Attack Type (slander/promote): ");
$attack_type = "slander";
require_once("run.php");

for($j = 0; $j < 5; $j++){
	for($i = 0; $i < 5; $i++){
		//Rating is 0 if slander, MAX_RATE if self-promoting
		$rating = MAX_RATE;
		$product_id = 4;
		if($attack_type == "slander"){
			//Lowest rating is 1 NOT 0
			$rating = 1;
			$product_id = 29;
		}
		
		//Null customer rating- creates new customer id
		//product id = 29, as stated in question
		log_new_rating($db, null, $product_id, $rating);
	}

	$base_output = "output/" . ucfirst($attack_type) . "_" . 
		strval(5 * ($j + 1)) . "_Alpha_" . strval(ALPHA) . "_";
	require("output.php");
}