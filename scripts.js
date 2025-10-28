async function loadRecipes() {
    try {
        const response = await fetch('recipes.json');
        const data = await response.json();
        
        const container = document.getElementById('recipes-container');
        
        data.recipes.forEach(recipe => {
            const recipeHTML = `
                <div class="recept">
                    <h2>${recipe.title}</h2>
                    <div class="recept-img">
                        <img src="${recipe.image}" alt="${recipe.title}">
                    </div>
                    <div class="recept-text">${recipe.instructions}</div>
                    <button class="visa-recept">Tryck för att visa recept</button>
                </div>
            `;
            container.innerHTML += recipeHTML;
        });

        // Add event listeners after loading recipes
        addButtonListeners();
    } catch (error) {
        console.error('Error loading recipes:', error);
    }
}

function addButtonListeners() {
    const buttons = document.querySelectorAll('.visa-recept');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const receptText = this.previousElementSibling;
            if (receptText.style.display === "block") {
                receptText.style.display = "none";
                this.textContent = "Visa recept";
            } else {
                receptText.style.display = "block";
                this.textContent = "Dölj recept";
            }
        });
    });
}

// Load recipes when page loads
document.addEventListener('DOMContentLoaded', loadRecipes);