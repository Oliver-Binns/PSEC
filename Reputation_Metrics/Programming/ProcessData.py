from functools import partial
from functools import reduce
import RatingsCalc
import os
import shutil

customers = {}
products = {}

class Customer:
    def __init__(self, product_id, rating, tl): 
        self.ratings = {}
        self.new_rating(product_id, rating)
        self.update_trust(tl)
    
    def new_rating(self, product_id, rating):
        self.ratings[product_id] = rating
        
    def update_trust(self, trust_level):
        self.trust_level = trust_level(self.ratings)
    
    def get_rating(self, product_id):
        return self.ratings[product_id]
    
    def __repr__(self):
        return str(round(self.trust_level, 2))
  
 
class Product:
    def __init__(self, product_id, customer_id, rating):
        self._id = product_id
        self.overall_rating = rating
        self.customers = [customer_id]
        
    def new_rating(self, customer_id):
        self.customers.append(customer_id)
        self.update_rating()
    
    def update_rating(self):
        global customers
        fetch_cs = [customers[id] for id in self.customers]
        self.overall_rating = RatingsCalc.trust_enhanced_mean(
            self._id,
            fetch_cs,
            trust_level
        )
        
    def __repr__(self):
        global customers
        
        val = 0
        for i in self.customers:
            val += customers[i].get_rating(self._id)
        mean = val / len(self.customers)
        
        return '{0:.2f} ({1:.2f})'.format(self.overall_rating, mean)


def use_line(ALPHA, customer_id, product_id, rating):
    trust_level = partial(RatingsCalc.trust_level, products, ALPHA=ALPHA)
    
    #initialise if this is the first rating from customer i
    if customer_id not in customers.keys():
        customers[customer_id] = Customer(product_id, rating, trust_level)
    else:
        customers[customer_id].new_rating(product_id, rating)
        
    #use current trust levels to calculate overall rating for product j
    if product_id not in products.keys():
        products[product_id] = Product(product_id, customer_id, rating)
    else:
        products[product_id].new_rating(customer_id)
    
    #update the trust levels for all customers who provided ratings for product j so far
    for customer_id in products[product_id].customers:
        customers[customer_id].update_trust(trust_level)
    
    #recalculate overall ratings for affected products other than product j
    for id in products:
        if id != product_id:
            products[id].update_rating()
  
  
def iterate_file(filename, func):
    file = open(filename, "r")
    for line in file:
        data = line.replace("\n", "").split(" ")
        data = [int(x) for x in data]
    
        func(data[0], data[1], data[2])
