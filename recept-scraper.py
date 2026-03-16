import sys
import json
import io
from bs4 import BeautifulSoup
import requests
from urllib.parse import urlparse
from PIL import Image
import os
from io import BytesIO

sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

def download_and_convert_image(img_url, recipe_title, id):
    try:
        response = requests.get(img_url, stream=True)
        if response.status_code == 200:
            img = Image.open(BytesIO(response.content))
            title = "".join(c for c in recipe_title if c.isalnum() or c in (' ', '_', '-')).rstrip()
            filename = f"{id}_{title}.webp"
            save_path = os.path.join("Bilder", filename)
            os.makedirs("Bilder", exist_ok=True)
            img.save(save_path, "WEBP", quality=80)
            return save_path
    except Exception as e:
        pass
    return ""

def scrape_recipe_ica(url, user_id):
    response = requests.get(url)
    if response.status_code != 200:
        return None
    soup = BeautifulSoup(response.text, 'html.parser')
    recipe = soup.find_all(class_="cooking-steps-main__text")
    recipe_text = [step.get_text(strip=True) for step in recipe]
    # Try to get the title as well
    title_tag = soup.find('h1')
    title = title_tag.get_text(strip=True) if title_tag else 'Recept'
    # Try to get the image
    img_tag = soup.find('img', class_="recipe-header__image")
    img_url = img_tag['src'] if img_tag and img_tag.has_attr('src') else ""
    img_path = ""
    if img_url:
        if img_url.startswith("//"):
            img_url = "https:" + img_url
        elif img_url.startswith("/"):
            img_url = "https://www.ica.se" + img_url
        img_path = download_and_convert_image(img_url, title, user_id)
    # Try to get ingredients
    ingredients = soup.find_all(class_="ingredients-list-group__card")
    ingredients_list = [" ".join(item.get_text(" ",strip=True).split()) for item in ingredients]
    return {
        "title": title,
        "instructions": "<br>".join(recipe_text),
        "image": img_path,
        "ingredients": "<br>".join(ingredients_list)
    }


def scrape_recipe(url, user_id):
    domain = urlparse(url).netloc
    if "ica.se" in domain:
        return scrape_recipe_ica(url, user_id)
    elif "coop.se" in domain:
        return #scrape_recipe_coop(url)  # NYI
    else:
        return False

if __name__ == "__main__":
    if len(sys.argv) > 1:
        url = sys.argv[1]
        id = sys.argv[2] if len(sys.argv) > 2 else "0"
        result = scrape_recipe(url, id)
        print(json.dumps(result, ensure_ascii=False))