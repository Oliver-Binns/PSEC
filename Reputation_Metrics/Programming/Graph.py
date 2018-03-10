import matplotlib.pyplot as pyplot

products = {4: [], 7: [], 29: []}
alphas = [1, 1.5, 2, 5]

def get_file_name(ALPHA, attack_type="", attack_length=0):
	fn = "output/"
	if attack_length > 0:
		fn += attack_type + "_" + str(attack_length) + "_"
	return fn +"Alpha_" + str(ALPHA) + "_Products.txt"

def get_product_data(fn, product_ids, avg=False):
	product_data = {}

	with open(fn) as data:
		for line in data:
			vals = line.split(" ");
			product_id = int(vals[0])

			if(product_id in product_ids):
				if avg:
					product_data[product_id] = float(vals[2].replace("(", "").replace(")", ""))
				else:
					product_data[product_id] = float(vals[1])

	return product_data

avgs = []
for ALPHA in alphas:
	fn = get_file_name(ALPHA);
	if ALPHA == alphas[0]:
		#IF this is the FIRST alpha value
		#first get AVERAGE value
		avgs = get_product_data(fn, products, True)


	read_data = get_product_data(fn, products)
	for product in read_data:
		products[product].append(read_data[product])

for (key, ratings) in products.items():
	pyplot.axhline(y=avgs[key], color='r', linestyle='-')
	pyplot.scatter(alphas, ratings)
	pyplot.show()


def plot_attack(attack_type, product_id, loc="upper right"):
	alpha_attack = {}
	for ALPHA in alphas:
		alpha_attack[ALPHA] = []
	avg_attack = []

	attack_lengths = [0, 5, 10, 15, 20, 25]
	for attack in attack_lengths:
		fn = get_file_name(1, attack_type, attack)
		avg_attack.append(get_product_data(fn, [product_id], True)[product_id])

		for ALPHA in alphas:
			fn = get_file_name(ALPHA, attack_type, attack)
			products = get_product_data(fn, [product_id])
			alpha_attack[ALPHA].append(products[product_id])

	#pyplot.title("Effect of " + attack_type + " Attacks (varying sizes)")
	legend = ["Arithmetic Mean"]
	pyplot.plot(attack_lengths, avg_attack)
	for ALPHA in alphas:
		legend.append(r'$\alpha$ = ' + str(ALPHA))
		pyplot.plot(attack_lengths, alpha_attack[ALPHA])
	pyplot.legend(legend, loc=loc)
	pyplot.xlabel("Attack Size")
	pyplot.ylabel("Product Rating")
	pyplot.show()

plot_attack("Promote", 4, "lower right")
plot_attack("Slander", 29)