from functools import partial
import ProcessData

if __name__ == "__main__":
    # The following constants SHOULD be taken as input:
    MAX_RATE = 5
    ALPHA = int(input('Enter an alpha value: '))
    INPUT_FN = "Q2Ratings.txt"
      
    ProcessData.iterate_file(INPUT_FN, partial(ProcessData.use_line, ALPHA))
    
    print("Products")
    for id in sorted(products):
        print(id, products[id])
        
    print()
    
    print("Customers")
    for id in sorted(customers):
        print(id, customers[id])
