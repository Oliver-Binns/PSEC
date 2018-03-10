<?php
//Output to file or console:
$customers = fopen($base_output . "Customers.txt", "w");
$products = fopen($base_output . "Products.txt", "w");

$rep_based = $db->query("SELECT * FROM products;");
$average = $db->query(
	"SELECT AVG(rating) rating FROM ratings
	GROUP BY product_id ORDER BY product_id;"
);
foreach($rep_based as $product){
	$avg = $average->fetch_assoc();

	$out_str = sprintf("%u %0.2f (%0.2f)\n",
		$product["id"],
		$product["rating"],
		$avg["rating"]
	);

	echo $out_str;
	fwrite($products, $out_str);
}
echo "\n";
$result = $db->query("SELECT * FROM customers;");
foreach($result as $customer){
	$out_str = sprintf("%u %0.2f\n",
		$customer["id"],
		$customer["trust_level"]
	);

	echo $out_str;
	fwrite($customers, $out_str);
}