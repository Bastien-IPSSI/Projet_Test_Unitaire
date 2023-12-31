<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recettes</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    require_once "./back.php";

    // On récupere la liste des categories disponibles
    $categorieDAO = new CategorieDAO($connexion);
    $categories = $categorieDAO->getAllCategorie();
    ?>
    <nav>
        <form action="front.php" method="POST">
            <input class="input" type="submit" name="accueil" value="Accueil">
        </form>
        <?php
        if (isset($_POST["accueil"])) {
            unset($_POST["search"]);
        }
        ?>

        <h1>Recettes</h1>
        <form action="front.php" method="GET">
            <input class="select" type="text" name="search" placeholder="Rechercher une recette">
            <input class="input" type="submit" name="search_submit" value="Rechercher">
        </form>
        <?php
        if (isset($_GET["search_submit"])) {
            $_POST["search"] = $_GET["search"];
        }
        ?>
        <form action="front.php" method="POST">
            <select name="categorie">
                <?php
                foreach ($categories as $categorie) {
                    echo "<option value='$categorie'>$categorie</option>";
                }
                ?>
            </select>
            <input class="input" type="submit" name="categorie_submit" value="Valider">
        </form>
        <?php
        if (isset($_POST["categorie_submit"])) {
            $_POST["search"] = $_POST["categorie"];
        }
        ?>


    </nav>
    <div class="recettes">
        <?php


        // Si la recherche est vide, on affiche toutes les recettes
        if (empty($_POST["search"])) {
            $recettesDAO = new RecetteDAO($connexion);
            $ingredientsDAO = new IngredientDAO($connexion);
            $categorieDAO = new CategorieDAO($connexion);
            $recettes = $recettesDAO->afficher_recettes();
        }
        // Si la recherche est une categorie, on affiche les recettes de cette categorie

        elseif (in_array($_POST["search"], $categories)) {
            $recettesDAO = new RecetteDAO($connexion);
            $ingredientsDAO = new IngredientDAO($connexion);
            $categorieDAO = new CategorieDAO($connexion);
            $recettes = $recettesDAO->rechercher_recette_par_categorie($_POST["categorie"]);
        } else {
            // Sinon, on affiche les recettes qui correspondent à la recherche
            $recettesDAO = new RecetteDAO($connexion);
            $ingredientsDAO = new IngredientDAO($connexion);
            $categorieDAO = new CategorieDAO($connexion);
            $recettes = $recettesDAO->rechercher_recette($_GET["search"]);
        }

        foreach ($recettes as $recette) {
            $categorie = $categorieDAO->getCategorie($recettesDAO->getIdCategorie($recette->getNomRecette()));
            echo "<div class='recette $categorie'>";
            echo "<div class='topRecette'>";
            echo "<h2>" . $recette->getNomRecette() . "</h2>";
            // Bouton pour supprimer la recette
            echo "<form action='front.php' method='POST'>";
            echo "<input type='hidden' name='id_recette' value='" . $recettesDAO->getID($recette->getNomRecette()) . "'>";
            echo "<input class='input' type='submit' name='supprimer_recette' value='Supprimer la recette'>";
            echo "</form>";
            echo "</div>";
            echo "<p>" . $recette->getInstructions() . "</p>";
            echo "<p>Temps de préparation : " . $recette->getTmp_prep() . " minutes</p>";
            // On affiche les ingrédients de la recette
            echo "<div class='ingredients'>";
            echo "<p>Ingrédients : </p>";
            $ingredients = $ingredientsDAO->getAllIngredientOfRecette(intval($recettesDAO->getID($recette->getNomRecette())));
            echo "<p>";
            foreach ($ingredients as $ingredient) {
                echo $ingredient->getNomIngredient() . " : " . $ingredient->getQuantite() . "g, ";
            }
            echo "</p>";
            echo "</div>";
            echo "</div>";
        }
        if (isset($_POST["supprimer_recette"])) {
            $recettesDAO->supprimer_recette($_POST["id_recette"]);
            header("Location:front.php");
        }
        ?>
    </div>

    <div class="bandeNoire"></div>

    <div class="addRecette">
        <h2>Ajouter une recette</h2>
        <form action="front.php" method="POST">

            <!-- Input qui permet de rentrer le nombre d'ingredient que l'on veut ajouter -->
            <!-- Si l'utilisateur choisi par exemple d'en ajouter 2, on affiche 2 input pour entrer l'ingredient et sa quantité -->

            <input type="number" min=1 name="nb_ingredients" placeholder="Nombre d'ingrédients">
            <input class="input" type="submit" name="nb_ingredients_submit" value="Valider">
            <?php
            if (isset($_POST["nb_ingredients_submit"]) && !empty($_POST["nb_ingredients"]) && is_numeric($_POST["nb_ingredients"])) {
                $nb_ingredients = $_POST["nb_ingredients"];
                echo "<input type='hidden' name='nb_ingredients' value='$nb_ingredients'>";
                for ($i = 0; $i < $nb_ingredients; $i++) {
                    $id_affiche = $i + 1;
                    echo "<input type='text' name='nom_ingredient$i' placeholder=\"Nom de l'ingredient $id_affiche\">";
                    echo "<input type='number' name='quantite$i' placeholder='Quantité'>";
                }
            }
            ?>

            <input type="text" name="nomRecette" placeholder="Nom de la recette">
            <input type="text" name="instructions" placeholder="Instructions">
            <input type="number" min=1 name="tmp_prep" placeholder="Temps de préparation">
            <select name="categorie">
                <?php
                foreach ($categories as $categorie) {
                    echo "<option value='$categorie'>$categorie</option>";
                }
                ?>
            </select>
    </div>
    <div class="submitButton">
        <input class="input" type="submit" name="ajouter_recette" value="Ajouter une recette" onclick="reloadPage()">
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
    </div>
    </form>
    </div>
</body>

</html>