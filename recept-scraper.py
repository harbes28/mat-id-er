from bs4 import BeautifulSoup
import requests

def scrape_recipe_ica(url):
    response = requests.get(url)
    
    if response.status_code == 200:
        soup = BeautifulSoup(response.text, 'html.parser')
    else:
        return None
    recipe = soup.find_all(class_="cooking-steps-main__text")
    recipe_text = [step.get_text(strip=True) for step in recipe]
    for step in recipe_text:
        print(step)

scrape_recipe_ica("https://www.ica.se/recept/kycklingcurry-med-ris-715779")
