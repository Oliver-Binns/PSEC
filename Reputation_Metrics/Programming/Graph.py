import matplotlib.pyplot as pyplot

products = {4: [], 7: [], 29: []}
alphas = [1, 1.5, 2, 5]

for ALPHA in alphas:
	fn = "output/Alpha_" + str(ALPHA) + "_Products.txt"
	print(fn);

	with open(fn) as data:
		for line in data:
			vals = line.split(" ");
			product_id = int(vals[0])
			if(product_id in products):
				products[product_id].append(float(vals[1]))


for (key, ratings) in products.items():
	print(ratings)
