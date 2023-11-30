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
        if (empty($_GET["search"])) {
            $recettesDAO = new RecetteDAO($connexion);
            $ingredientsDAO = new IngredientDAO($connexion);
            $recettes = $recettesDAO->afficher_recettes();
        }

        foreach ($recettes as $recette) {
            echo "<div class='recette'>";
            echo "<h2>" . $recette->getNomRecette() . "</h2>";
            echo "<p>" . $recette->getInstructions() . "</p>";
            echo "<p>Temps de préparation : " . $recette->getTmp_prep() . " minutes</p>";
            // On affiche les ingrédients de la recette
            $ingredients = $ingredientsDAO->getAllIngredientOfRecette($recettesDAO->getID($recette->getNomRecette()));
            foreach ($ingredients as $ingredient) {
                echo "<p>" . $ingredient->getNomIngredient() . "</p>";
            }
            echo "</div>";
        }
        ?>
    </div>
    <div class="addRecette">
        <!-- Formulaire qui permet d'ajouter une recette -->
        <!-- Lorsqu'on clique sur submit, on utilise la fonction ajouter_recette -->
        <form action="front.php" method="POST">
            <input type="text" name="nomRecette" placeholder="Nom de la recette">
            <input type="text" name="instructions" placeholder="Instructions">
            <input type="number" name="tmp_prep" placeholder="Temps de préparation">
            <select name="categorie">
                <option value="Poisson">Poisson</option>
                <option value="Vegetarien">Vegetarien</option>
                <option value="Viande">Viande</option>
            </select>
            <input type="text" name="nom_ingredient" placeholder="Nom de l'ingrédient">
            <input type="number" name="quantite" placeholder="Quantité">
            <input type="submit" name="ajouter_recette" value="Ajouter une recette">
        </form>
        <?php
        if (isset($_POST["ajouter_recette"])) {
            $lst_ingredients = [];
            array_push($lst_ingredients, ["nom_ingredient" => $_POST["nom_ingredient"], "quantite" => $_POST["quantite"]]);
            $recettesDAO->ajouter_recette(
                $_POST["nomRecette"],
                $_POST["instructions"],
                $_POST["tmp_prep"],
                $_POST["categorie"],
                $lst_ingredients,
                $ingredientsDAO,
            );
        }
        ?>

    </div>
</body>

</html>