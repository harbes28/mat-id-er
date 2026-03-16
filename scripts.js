async function loadRecipes() {
    try {
        // Use the user-specific JSON file
        const response = await fetch(`user_recipes/user_recipes_${USER_ID}.json?v=` + new Date().getTime());
        if (!response.ok) {
            // If file doesn't exist yet, show no recipes
            document.getElementById('recipes-container').innerHTML = "<p>Inga recept hittades.</p>";
            return;
        }
        const data = await response.json();

        const container = document.getElementById('recipes-container');
        container.innerHTML = ""; // Clear previous content

        data.recipes.forEach(recipe => {
            const recipeHTML = `
                <div class="recept">
                    <button class="delete-recipe-btn" title="Radera recept" data-id="${recipe.id}">&times;</button>
                    <h2 class="recept-title">${recipe.title}</h2>
                    <div class="recept-img">
                        <img src="${recipe.image}" alt="${recipe.title}">
                    </div>
                    <div class="recept-ingredients">${recipe.ingredients}</div>
                    <div class="recept-text">
                        ${recipe.instructions
                            .split(/<br\s*\/?>|\n/)
                            .filter(step => step.trim() !== "")
                            .map((step, i) => `
                                <div class="instruction-row">
                                    <input type="checkbox" class="instruction-checkbox" data-step="${i}">
                                    <span class="instruction-step" data-step="${i}">${step.trim()}</span>
                                </div>
                            `).join('')}
                    </div>
                    <button class="redigera-recept">Tryck för att redigera receptet</button>
                    <button class="anteckna-recept">Tryck för att anteckna receptet</button>
                </div>
            `;
            container.innerHTML += recipeHTML;
        });
        
        setTimeout(() => {
            document.querySelectorAll('.instruction-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const step = this.dataset.step;
                    const span = this.parentElement.querySelector(`.instruction-step[data-step="${step}"]`);
                    if (this.checked) {
                        span.classList.add('struck');
                    } else {
                        span.classList.remove('struck');
                    }
                });
            });
            document.querySelectorAll('.delete-recipe-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const recipeId = this.getAttribute('data-id');
                    if (confirm('Är du säker på att du vill radera detta recept?')) {
                        fetch('add_recipes.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: `delete_recipe_id=${encodeURIComponent(recipeId)}`
                        })
                        .then(() => loadRecipes());
                    }
                });
            });
        }, 0);
        addButtonListeners();
    } catch (error) {
        console.error('Error loading recipes:', error);
        document.getElementById('recipes-container').innerHTML = "<p>Kunde inte ladda recept.</p>";
    }
}

function addButtonListeners() {
    document.querySelectorAll('.recept').forEach(card => {
        card.addEventListener('click', function(e) {
            // Prevent toggling when clicking the delete button, visa-recept button, or a checkbox/step
            if (
                e.target.classList.contains('delete-recipe-btn') ||
                e.target.classList.contains('visa-recept') ||
                e.target.classList.contains('instruction-checkbox') ||
                e.target.classList.contains('instruction-step')
            ) return;

            const ingredients = card.querySelector('.recept-ingredients');
            const instructions = card.querySelector('.recept-text');
            const expanded = instructions.classList.contains('expanded');
            if (expanded) {
                instructions.classList.remove('expanded');
                ingredients.classList.remove('expanded');
            } else {
                instructions.classList.add('expanded');
                ingredients.classList.add('expanded');
            }
        });
    });

    // Prevent checkbox clicks from bubbling up to the .recept card
    document.querySelectorAll('.instruction-checkbox').forEach(checkbox => {
        checkbox.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
}



function showAddRecipeForm() {
    const formBox = document.querySelector('.nya-recept');
    const overlay = document.querySelector('.overlay');
    formBox.style.display = 'block';
    overlay.classList.add('active');
}

function linkRecipe() {
    // Add the div first
}

function manualRecipe() {
    const formBox = document.querySelector('.manuell-recept-inmatning');
    const closeBox = document.querySelector('.recept-link-form');
    if (closeBox.style.display === 'flex') {
        closeBox.style.display = 'none';
        formBox.style.display = 'flex';
    } else {
        formBox.style.display = 'flex';
    }
}

function linkRecipe() {
    const formBox = document.querySelector('.recept-link-form');
    const closeBox = document.querySelector('.manuell-recept-inmatning')
    if (closeBox.style.display === 'flex') {
        closeBox.style.display = 'none';
        formBox.style.display = 'flex';
    } else {
        formBox.style.display = 'flex';
    }
}

// Load recipes when page loads
function closeAddRecipeForm() {
    const formBox = document.querySelector('.nya-recept');
    const overlay = document.querySelector('.overlay');
    const manualChoice = document.querySelector('.manuell-recept-inmatning');
    const linkChoice = document.querySelector('.recept-link-form');
    linkChoice.style.display = 'none';
    manualChoice.style.display = 'none';
    formBox.style.display = 'none';
    overlay.classList.remove('active');

}


document.querySelector('.overlay').addEventListener('click', closeAddRecipeForm);
document.addEventListener('DOMContentLoaded', loadRecipes);
