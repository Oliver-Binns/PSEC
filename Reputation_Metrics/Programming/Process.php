<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$max_rate = readline("Max Rate: ");
$alpha = readline("Alpha: ");
$filename = readline("Input File: ");

define("ALPHA", floatval($alpha));
unset($alpha);

$myfile = fopen($filename, "r");

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

function update_rating($db, $product_id){
	$stmt = $db->prepare("SELECT rating, trust_level FROM ratings, customers WHERE customer_id = customers.id AND product_id = ?;");
	$stmt->bind_param("s", $product_id);
	$stmt->execute();
	$result = $stmt->get_result();

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
	$fetch_products = "SELECT ratings.rating AS customer_rating, products.rating AS overall_rating
		FROM ratings, products 
		WHERE product_id=products.id AND customer_id=?;";

	$stmt = $db->prepare($fetch_products);
	$stmt->bind_param("s", $customer_id);
	$stmt->execute();
	$result = $stmt->get_result();

	$stmt = $db->prepare("UPDATE customers SET trust_level = ? WHERE id = ?;");
	$tl = trust_index($result, $result->num_rows);
	$stmt->bind_param("ss", $tl, $customer_id);
	$stmt->execute();
}

function trust_index($products, $count){
	$numerator = 1;
	foreach($products as $product){
		$numerator += is_trusted($product["overall_rating"], $product["customer_rating"]);
	}
	$denominator = 2 + $count;

	return $numerator / $denominator; 
}

function is_trusted($overall_rating, $customer_rating){
	if(abs($overall_rating - $customer_rating) <= ALPHA){
		return 1;
	}
	return 0;
}

//mysqli_report(MYSQLI_REPORT_ALL);

while(!feof($myfile)){
	//FOR EACH RATING.. update the database:
	$data = explode(" ", fgets($myfile));
	if(count($data) != 3){break;}
	$customer_id = $data[0];
	$product_id = $data[1];
	$rating = $data[2];

	//LOG THE NEW RATING:
	$stmt = $db->prepare("INSERT INTO ratings (customer_id, product_id, rating) VALUES(?, ?, ?);");
	$stmt->bind_param("sss", $customer_id, $product_id, $rating);
	$stmt->execute();

	//Check if this is a new user:
	$stmt = $db->prepare("SELECT COUNT(*) FROM customers where id=?;");
	$stmt->bind_param("s", $customer_id);
	$stmt->execute();
	if($stmt->get_result()->fetch_assoc()["COUNT(*)"] == 0){
		//initialise trust level if new customer: This is 0.5
		if($stmt = $db->prepare("INSERT INTO customers VALUES (?, ?);")){
			$trust = trust_index([], 0);
			$stmt->bind_param("ss", $customer_id, $trust);
			$stmt->execute();
		}
	}

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
		continue;
	}//IF EXISTING product:

	//Update trust levels of all customers who bought this product
	$stmt = $db->prepare("SELECT customer_id FROM ratings WHERE product_id=?;");
	$stmt->bind_param("s", $product_id);
	$stmt->execute();
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()){
		update_trust($db, $row["customer_id"]);

	}

	//Recalculate all products other than project j
	//LIMIT THIS TO PRODUCTS THAT HAVE BEEN AFFECTED!:
	$stmt = $db->prepare("SELECT DISTINCT product_id FROM ratings WHERE customer_id IN (SELECT customer_id FROM ratings WHERE product_id = ?)");
	$stmt->bind_param("s", $product_id);
	$stmt->execute();
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()){
		update_rating($db, $row["product_id"]);
	}
}

$rep_based = $db->query("SELECT * FROM products;");
$average = $db->query("SELECT AVG(rating) rating FROM ratings GROUP BY product_id ORDER BY product_id;");
foreach($rep_based as $product){
	$avg = $average->fetch_assoc();
	echo $product["id"], " ", $product["rating"], " (", $avg["rating"], ")\n";
}
echo "\n";
$result = $db->query("SELECT * FROM customers;");
foreach($result as $customer){
	echo $customer["id"], " ", $customer["trust_level"], "\n";
}

fclose($myfile)
?>