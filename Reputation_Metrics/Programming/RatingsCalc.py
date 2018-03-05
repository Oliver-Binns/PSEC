from functools import partial

def trust_level(products, ratings, ALPHA): 
    numerator = 1
    for product_id, rating in ratings.items():
        mean = products[product_id].overall_rating
        numerator += f(mean, rating, ALPHA)
        
    denominator = 2 + len(ratings)
    
    return numerator / denominator


def f(mean, rating, ALPHA):
    return 1 if abs(mean - rating) <= ALPHA else 0


def trust_enhanced_mean(product_id, customers, trust_func): 
    trustedRatingSum = 0
    trustedRatingCount = 0

    for c in customers:
        tl = trust_func(c.ratings)
        trustedRatingSum += tl * c.get_rating(product_id)
        trustedRatingCount += tl
    
    return trustedRatingSum / trustedRatingCount
