<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recettes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <h1>Recettes</h1>
        <!-- Création d'une barre de recherche -->
        <form action="front.php" method="GET">
            <input type="text" name="search" placeholder="Rechercher une recette">
            <input type="submit" value="Rechercher">
        </form>
    </nav>
    <div class="recettes">
        <?php
            require_once "./back.php";
            // Si la recherche est vide, on affiche toutes les recettes
            if(empty($_GET["search"])){
                $recettesDAO = new RecetteDAO($connexion);
                $ingredientsDAO = new IngredientDAO($connexion);
                $recettes = $recettesDAO->afficher_recettes();
            }

            foreach($recettes as $recette){
                echo "<div class='recette'>";
                echo "<h2>".$recette->getNomRecette()."</h2>";
                echo "<p>".$recette->getInstructions()."</p>";
                echo "<p>Temps de préparation : ".$recette->getTmp_prep()." minutes</p>";
                // On affiche les ingrédients de la recette
                $ingredients = $ingredientsDAO -> getAllIngredientOfRecette($recettesDAO->getID($recette->getNomRecette()));
                foreach($ingredients as $ingredient){
                    echo "<p>".$ingredient->getNomIngredient()."</p>";
                }
                echo "</div>";
            }
        ?>
    </div>
</body>
</html>