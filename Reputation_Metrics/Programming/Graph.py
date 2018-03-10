import matplotlib.pyplot as pyplot

products = {4: [], 7: [], 29: []}
alphas = [1, 1.5, 2, 5]

def get_file_name(ALPHA, attack_type="", attack_length=0):
	fn = "output/"
	if attack_length > 0:
		fn += attack_type + "_" + str(attack_length) + "_"
	return fn +"Alpha_" + str(ALPHA) + "_Products.txt"

def get_product_data(fn, product_ids):
	product_data = {}

	with open(fn) as data:
		for line in data:
			vals = line.split(" ");
			product_id = int(vals[0])

			if(product_id in product_ids):
				product_data[product_id] = float(vals[1])

	return product_data


for ALPHA in alphas:
	read_data = get_product_data(get_file_name(ALPHA), products)
	for product in read_data:
		products[product].append(read_data[product])

#for (key, ratings) in products.items():
#	pyplot.figure(key)
#	pyplot.scatter(alphas, ratings)
#	#pyplot.show()


def plot_attack(attack_type, product_id, loc="upper right"):
	alpha_attack = {}
	for ALPHA in alphas:
		alpha_attack[ALPHA] = []

	attack_lengths = [0, 5, 10, 15, 20, 25]
	for attack in attack_lengths:
		for ALPHA in alphas:
			fn = get_file_name(ALPHA, attack_type, attack)
			products = get_product_data(fn, [product_id])
			alpha_attack[ALPHA].append(products[product_id])

	pyplot.title("Effect of " + attack_type + " Attacks (varying sizes)")
	legend = []
	for ALPHA in alphas:
		legend.append(str(ALPHA))
		pyplot.plot(attack_lengths, alpha_attack[ALPHA])
	pyplot.legend(legend, title="Alpha Value", loc=loc)
	pyplot.xlabel("Attack Size")
	pyplot.ylabel("Product Rating")
	pyplot.show()

plot_attack("Promote", 4, "lower right")
plot_attack("Slander", 29)