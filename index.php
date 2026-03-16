<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mat-idéer</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        const USER_ID = <?= json_encode($_SESSION['user_id']) ?>;
        document.addEventListener('DOMContentLoaded', function() {
            const linkInput = document.getElementById('recipe-link');
            const submitBtn = document.querySelector('#recipe-link-form button[type="submit"]');

            // List of supported URL patterns (add more as needed)
            const supportedPatterns = [
                /^https?:\/\/(www\.)?ica\.se\/recept\//i,
                /^https?:\/\/(www\.)?coop\.se\/recept\//i
                // Add more patterns here
            ];

            function checkUrlValidity(url) {
                return supportedPatterns.some(pattern => pattern.test(url));
            }

            function updateButtonState() {
                const url = linkInput.value.trim();
                if (checkUrlValidity(url)) {
                    submitBtn.disabled = false;
                    submitBtn.style.backgroundColor = "#4CAF50";
                    submitBtn.style.cursor = "pointer";
                } else {
                    submitBtn.disabled = true;
                    submitBtn.style.backgroundColor = "#888";
                    submitBtn.style.cursor = "not-allowed";
                }
            }

            linkInput.addEventListener('input', updateButtonState);

            // Initial state
            updateButtonState();
        });
    </script>
    <script src="scripts.js" defer></script>
</head>
<body>
    <div class="overlay"></div>
    <h1>Mat-idéer</h1>
    <aside>
        <button onclick="showAddRecipeForm()">Lägg till recept</button>
        <form action="logout.php" method="post" style="display:inline;">
            <button type="submit" style="background-color:#888; margin-left:10px;">Logga ut</button>    
        </form>
    </aside>
    
    <main>
        <div class="container" id="recipes-container">
            <!-- Recipes will be loaded here dynamically -->
        </div>
        <div class="nya-recept">
            <fieldset>
                <legend>Nytt recept</legend>
                <div class="recept-inmatning-knappar">
                    <button id="infoga-recept-knapp" onclick="linkRecipe()">infoga recept från länk</button>
                    <button id="manuell-recept-knapp" onclick="manualRecipe()" >Skriv in ditt egna recept</button>
                </div>
                <div class="manuell-recept-inmatning">
                    <form id="manual-recipe-form" action="add_recipes.php" method="post">
                        <input type="text" id="recipe-name" name="recipe-name" placeholder="Receptnamn" required><br>
                        <textarea id="recipe-ingredients" name="recipe-ingredients" placeholder="Ingredienser" required></textarea><br>
                        <textarea id="recipe-instructions" name="recipe-instructions" placeholder="Instruktioner" required></textarea><br>
                        <button type="submit">Spara recept</button>
                    </form>
                </div>
                <div class="recept-link-form">
                    <form id="recipe-link-form" action="add_recipes.php" method="post">
                        <input type="url" id="recipe-link" name="recipe-link" placeholder="Receptlänk" required><br>
                        <button type="submit">Hämta recept</button>
                    </form>
                </div>
                <div class="radera-alla-recept">
                    <form id="delete-all-recipes-form" action="add_recipes.php" method="post" onsubmit="return confirm('Är du säker på att du vill radera alla dina recept?');">
                        <input type="hidden" name="delete_all_recipes" value="1">
                        <button type="submit" style="background-color: #c0392b;">Radera alla recept</button>
                    </form>
                </div>
            </fieldset>
        </div>
    </main>
</body>
</html>