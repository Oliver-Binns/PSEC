<?php
function log_new_rating($db, $customer_id, $product_id, $rating){
	$trust = 0.5; //Equation 3 returns 0.5 when given the EMPTY SET
	//Check if this is a new user:
	if($customer_id == null){
		//This is a simulated attack:
		//completely new customer ID must be created:
		$stmt = $db->prepare(
			"INSERT INTO customers (trust_level) VALUES(?);"
		);
		$stmt->bind_param("s", $trust);
		$stmt->execute();

		$customer_id = $db->insert_id;
	}else{
		$stmt = $db->prepare(
			"SELECT COUNT(*) FROM customers where id=?;"
		);
		$stmt->bind_param("s", $customer_id);
		$stmt->execute();
		if($stmt->get_result()->fetch_assoc()["COUNT(*)"] == 0){
			//initialise trust level if new customer: This is 0.5
			if($stmt = 
				$db->prepare(
					"INSERT INTO customers VALUES (?, ?);"
				)){
				$stmt->bind_param("ss", $customer_id, $trust);
				$stmt->execute();
			}
		}
	}
	

	//LOG THE NEW RATING:
	$stmt = $db->prepare(
		"INSERT INTO ratings (customer_id, product_id, rating)
		VALUES(?, ?, ?);"
	);
	$stmt->bind_param("sss", $customer_id, $product_id, $rating);
	$stmt->execute();

	//Calculate overall product rating
	$stmt = $db->prepare("SELECT COUNT(*) FROM products where id=?;");
	$stmt->bind_param("s", $product_id);
	$stmt->execute();
	//If NEW product
	if($stmt->get_result()->fetch_assoc()["COUNT(*)"] == 0){
		//initialise rating with the rating of the NEW customer:
		if($stmt = $db->prepare("INSERT INTO products VALUES (?, ?);")){
			$stmt->bind_param("ss", $product_id, floatval($rating));
			$stmt->execute();
		}
		//NEW PRODUCT, nothing left to update?
		return;
	}//IF EXISTING product:

	//Update trust levels of all customers who bought this product
	$stmt = $db->prepare(
		"SELECT customer_id FROM ratings WHERE product_id=?;"
	);
	$stmt->bind_param("s", $product_id);
	$stmt->execute();
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()){
		update_trust($db, $row["customer_id"]);

	}

	//Recalculate all products other than project j
	//LIMIT THIS TO PRODUCTS THAT HAVE BEEN AFFECTED!:
	$stmt = $db->prepare(
		"SELECT DISTINCT product_id FROM ratings
		WHERE customer_id IN
		(SELECT customer_id FROM ratings WHERE product_id = ?)"
	);
	$stmt->bind_param("s", $product_id);
	$stmt->execute();
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()){
		update_rating($db, $row["product_id"]);
	}
}

function update_rating($db, $product_id){
	$stmt = $db->prepare(
		"SELECT rating, trust_level FROM ratings, customers
		WHERE customer_id = customers.id AND product_id = ?;"
	);
	$stmt->bind_param("s", $product_id);
	$stmt->execute();
	$result = $stmt->get_result();

	//Equation 2 (Brief.pdf):
	$numerator = 0;
	$denominator = 0;

	foreach($result as $rating){
		$numerator += $rating["rating"] * $rating["trust_level"];
		$denominator += $rating["trust_level"];
	}

	$stmt = $db->prepare("UPDATE products SET rating = ? WHERE id = ?;");
	$rating = $numerator / $denominator;
	$stmt->bind_param("ss", $rating, $product_id);
	$stmt->execute();
}


function update_trust($db, $customer_id){
	$fetch_products = "SELECT
		ratings.rating AS customer_rating,
		products.rating AS overall_rating
		FROM ratings, products 
		WHERE product_id=products.id AND customer_id=?;";

	$stmt = $db->prepare($fetch_products);
	$stmt->bind_param("s", $customer_id);
	$stmt->execute();
	$result = $stmt->get_result();

	$stmt = $db->prepare(
		"UPDATE customers SET trust_level = ? WHERE id = ?;"
	);
	$tl = trust_index($result);
	$stmt->bind_param("ss", $tl, $customer_id);
	$stmt->execute();
}

//Equation 3 (Brief.pdf):
function trust_index($products){
	$numerator = 1;
	$denominator = 2;

	foreach($products as $product){
		$numerator += is_trusted(
			$product["overall_rating"],
			$product["customer_rating"]
		);

		$denominator++;
	}

	return $numerator / $denominator; 
}

//Equation 4 (Brief.pdf):
function is_trusted($overall_rating, $customer_rating){
	if(abs($overall_rating - $customer_rating) <= ALPHA){
		return 1;
	}
	return 0;
}