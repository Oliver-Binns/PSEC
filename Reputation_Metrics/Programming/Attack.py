from shutil import copyfile
import ProcessData

def copy(filename):
    copyfile(filename, "Attacked.txt")
    
def get_biggest_id(filename):
    iterate_file(filename)
    
def attack(filename, product_id, n, rating):
    next_id = get_biggest_id() + 1

copy("Q2Ratings.txt")
