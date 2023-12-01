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
         <!-- Bouton qui renvoie vers l'accueil. Si on appuie sur ce bouton, on vide le $_POST["search"] -->
        <form action="front.php" method="POST">
            <input type="submit" name="accueil" value="Accueil">
        </form>
        <?php
        if (isset($_POST["accueil"])) {
            unset($_POST["search"]);
        }
        ?>

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
        }else{
            // Sinon, on affiche les recettes qui correspondent à la recherche
            $recettesDAO = new RecetteDAO($connexion);
            $ingredientsDAO = new IngredientDAO($connexion);
            $recettes = $recettesDAO->rechercher_recette($_GET["search"]);
        }

        foreach ($recettes as $recette) {
            echo "<div class='recette'>";
            echo "<h2>" . $recette->getNomRecette() . "</h2>";
            echo "<p>" . $recette->getInstructions() . "</p>";
            echo "<p>Temps de préparation : " . $recette->getTmp_prep() . " minutes</p>";
            // On affiche les ingrédients de la recette
            $ingredients = $ingredientsDAO->getAllIngredientOfRecette(intval($recettesDAO->getID($recette->getNomRecette())));
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
            <!-- Input qui permet de rentrer le nombre d'ingredient que l'on veut ajouter -->
            <!-- Si l'utilisateur choisi par exemple d'en ajouter 2, on affiche 2 input pour entrer l'ingredient et sa quantité -->

            <input type="number" name="nb_ingredients" placeholder="Nombre d'ingrédients">
            <input type="submit" name="nb_ingredients_submit" value="Valider">
            <?php
            if (isset($_POST["nb_ingredients_submit"]) && !empty($_POST["nb_ingredients"]) && is_numeric($_POST["nb_ingredients"])) {
                $nb_ingredients = $_POST["nb_ingredients"];
                echo "<input type='hidden' name='nb_ingredients' value='$nb_ingredients'>";
                for ($i = 0; $i < $nb_ingredients; $i++) {
                    echo "<input type='text' name='nom_ingredient$i' placeholder='Nom de l ingredient'>";
                    echo "<input type='number' name='quantite$i' placeholder='Quantité'>";
                }
            }
            ?>  
            <input type="submit" name="ajouter_recette" value="Ajouter une recette">
            <?php
            if (isset($_POST["ajouter_recette"])) {
                $lst_ingredients = [];
                $categorieDAO = new CategorieDAO($connexion);
                for ($i = 0; $i < $_POST["nb_ingredients"]; $i++) {
                    array_push($lst_ingredients, ["nom_ingredient" => $_POST["nom_ingredient$i"], "quantite" => $_POST["quantite$i"]]);
                }
                $recettesDAO->ajouter_recette(
                    $_POST["nomRecette"],
                    $_POST["instructions"],
                    $_POST["tmp_prep"],
                    $categorieDAO->getIdCategorie($_POST["categorie"]),
                    $lst_ingredients,
                    $ingredientsDAO,
                );
            }
            ?>
        </form>

    </div>
</body>

</html>