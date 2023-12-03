<?php

// Include the configuration file
require_once("./config.php");

try {
    // Database connection parameters
    $host = DB_HOST;
    $user = DB_USER;
    $pwd = DB_PWD;
    $db_name = DB_NAME;
    $db_port = DB_PORT;

    // Create a new PDO instance for database connection
    $connexion = new PDO("mysql:host=$host;port=$db_port;dbname=$db_name", $user, $pwd);
    // Set PDO attributes for error handling
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    // Handle any errors that occur during database connection
    echo "Erreur lors de la connexion à la database : " . $e->getMessage();
    die();
}

// Class representing a Recipe
class Recette {

    private $nom_recette;
    private $instructions;
    private $tmp_prep;

    // Constructor to initialize the recipe
    public function __construct($nom_recette, $instructions, $tmp_prep)
    {
        $this->nom_recette = $nom_recette;
        $this->instructions = $instructions;
        $this->tmp_prep = $tmp_prep;
    }

    // Getter methods for retrieving recipe details
    public function getNomRecette()
    {
        return $this->nom_recette;
    }

    public function getInstructions()
    {
        return $this->instructions;
    }

    public function getTmp_prep()
    {
        return $this->tmp_prep;
    }

    // Setter methods for updating recipe details
    public function setNomRecette($nom_recette){
        if($nom_recette!="" && $nom_recette!=" " && is_string($nom_recette)){
            $this->nom_recette=$nom_recette;
        }
        else{
            throw new InvalidArgumentException("Valeur incorrecte");
        }
    }

    public function setInstructions($instructions)
    {
        if($instructions!="" && $instructions!=" " && is_string($instructions)){
            $this->instructions = $instructions;
        } else {
            throw new InvalidArgumentException("Valeur incorrecte");
        }
    }

    public function setTmp_prep($tmp_prep)
    {
        if($tmp_prep!=null && is_int($tmp_prep)){
            $this->tmp_prep = $tmp_prep;
        } else {
            throw new InvalidArgumentException("Valeur incorrecte");
        }
    }
}

// Class for interacting with Recipe data in the database
class RecetteDAO {
    private $bdd;

    // Constructor to set the database connection
    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    // Method to retrieve and display all recipes
    public function afficher_recettes(){
        $liste_recette = [];
        try{
            $requete = $this->bdd->prepare("SELECT * FROM recettes");
            $requete->execute();
            $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
            // Create Recipe objects and add them to the list
            foreach($resultat as $recette){
                $rec = new Recette($recette["nom_recette"], $recette["instructions"], $recette["tmp_prep"]);
                array_push($liste_recette, $rec);
            }
            return $liste_recette;
        } catch(PDOException $e){
            // Handle any database errors during recipe retrieval
            echo "Erreur lors de la récupération ".$e->getMessage();
            return [];
        }
    }

    // Method to get the ID of a recipe based on its name
    public function getID($nom_recette){
        if($nom_recette!= " "&& $nom_recette!= "" && is_string($nom_recette)){
            try{
                $requete = $this->bdd->prepare("SELECT id_recette FROM recettes WHERE nom_recette = ?");
                $requete->execute([$nom_recette]);
                $resultat = $requete->fetch(PDO::FETCH_ASSOC);
                return $resultat["id_recette"];
            } catch(PDOException $e){
                // Handle any database errors during ID retrieval
                echo "Erreur lors de la récupération de l'id de la recette ".$e->getMessage();
                return;
            }
        } else {
            throw new InvalidArgumentException("Valeur incorrecte");
        }
    }

    // Method to add a recipe to the database
    public function ajouter_recette($nom_recette,$instruction,$tmp_prep,$id_categorie, $lst_ingredients, $ingredientsDAO){
        if((is_string($nom_recette) && $nom_recette!="" && $nom_recette!=" ") &&
            (is_string($instruction) && $instruction!="" && $instruction!=" ") &&
            (is_int($tmp_prep) && $tmp_prep!=null) &&
            (is_int($id_categorie) && $id_categorie!=null)) {
            // Check if the recipe already exists
            try {
                $requete = $this->bdd->prepare("SELECT COUNT(*) FROM recettes WHERE nom_recette = :nom_recette");
                $requete->execute([
                    ":nom_recette" => $nom_recette
                ]);
                $exists = $requete->fetchColumn();
    
                if ($exists) {
                    // The recipe already exists; do not add it without displaying an error
                    echo "La recette existe déjà";
                }
            } catch (PDOException $e) {
                // Handle any database errors during recipe addition
                echo "Erreur lors de l'ajout de la recette : " . $e->getMessage();
                die();
            }
    
            // Insert the recipe into the recette table
            if(!empty($nom_recette) && !empty($instruction) && !empty($tmp_prep) && !empty($id_categorie) && $exists == 0){
                try{
                    $requete = $this->bdd->prepare("INSERT INTO recettes (nom_recette, instructions, tmp_prep, id_categorie) VALUES (?, ?, ?, ?)");
                    $requete->execute([$nom_recette,$instruction,$tmp_prep,$id_categorie]);
                } catch(PDOException $e){
                    echo "Erreur lors de l'insertion".$e->getMessage();
                }
        
                // Insert ingredients into the ingredients table
                foreach($lst_ingredients as $ingredient){
                    $ingredientsDAO->addIngredient($ingredient["nom_ingredient"]);
                }
        
                // Insert ingredients into the recetteingredient table
                for($i=0; $i<count($lst_ingredients); $i++){
                    $ingredientsDAO->addIngredientToRecette(
                        $this->getID($nom_recette),
                        $ingredientsDAO->getIdIngredient($lst_ingredients[$i]["nom_ingredient"]),
                        $lst_ingredients[$i]["quantite"]
                    );
                }
                
            } elseif($exists == 1){
                echo "<p>La recette existe déjà</p>";
            } else {
                echo "<p>Erreur lors de l'ajout de la recette</p>";
            }
        } else {
            throw new InvalidArgumentException("valeur incorrecte");
        }
    }

    // Method to search for recipes based on the recipe name
    public function rechercher_recette($nom_recette){
        $liste_recette = [];
        if($nom_recette!= " " && $nom_recette!= "" && is_string($nom_recette)){
            try{
                $requete = $this->bdd->prepare("SELECT * FROM recettes WHERE nom_recette LIKE ?");
                $requete->execute(["%".$nom_recette."%"]);
                $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
                // Create Recipe objects and add them to the list
                foreach($resultat as $recette){
                    $rec = new Recette($recette["nom_recette"], $recette["instructions"], $recette["tmp_prep"]);
                    array_push($liste_recette, $rec);
                }
                return $liste_recette;
            } catch(PDOException $e){
                // Handle any database errors during recipe retrieval
                echo "Erreur lors de la récupération ".$e->getMessage();
                return [];
            }
        } else {
            throw new InvalidArgumentException("valeur incorrecte");
        }
    }

}

// Class representing an Ingredient
class Ingredient {

    private $nom_ingredient;

    // Constructor to initialize the ingredient
    public function __construct($nom_ingredient)
    {
        $this->nom_ingredient = $nom_ingredient;
    }

    // Getter method for retrieving the ingredient name
    public function getNomIngredient(){
        return $this->nom_ingredient;
    }
}

// Class for interacting with Ingredient data in the database
class IngredientDAO {
    private $db;

    // Constructor to set the database connection
    public function __construct($db)
    {
        $this->db = $db;
    }

    // Method to get all ingredients of a recipe
    public function getAllIngredientOfRecette($id_recette){
        $ids_ingredient = [];
        $ingredients = [];
        if($id_recette!=null && is_int($id_recette)){
            try{
                $requete = $this->db->prepare("SELECT id_ingredient FROM recetteingredient WHERE id_recette = :id_recette");
                $requete->execute([
                    ":id_recette" => $id_recette
                ]);
                $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
                foreach($resultat as $id_ingredient){
                    array_push($ids_ingredient, $id_ingredient["id_ingredient"]);
                }
            } catch(Exception $e){
                echo "Erreur lors de la récupération des ingrédients de la recette : ".$e->getMessage();
                die();
            }
    
            // Retrieve ingredient names using ingredient IDs
            foreach($ids_ingredient as $id_ingredient){
                try{
                    $requete = $this->db->prepare("SELECT nom_ingredient FROM ingredients WHERE id_ingredient = :id_ingredient");
                    $requete->execute([
                        ":id_ingredient" => $id_ingredient
                    ]);
                    $resultat = $requete->fetch(PDO::FETCH_ASSOC);
                    array_push($ingredients, new Ingredient($resultat["nom_ingredient"]));
                } catch(Exception $e){
                    echo "Erreur lors de la récupération des ingrédients de la recette : ".$e->getMessage();
                    die();
                }
            }
            var_dump($ingredients);
            return $ingredients;
        } else {
            throw new InvalidArgumentException("valeur incorrecte");
        }
    }

    // Method to get the ID of an ingredient based on its name
    public function getIdIngredient($nom_ingredient){
        if(is_string($nom_ingredient) && $nom_ingredient != "" && $nom_ingredient != " "){
            try{
                $requete = $this->db->prepare("SELECT id_ingredient FROM ingredients WHERE nom_ingredient = :nom_ingredient");
                $requete->execute([
                    ":nom_ingredient" => $nom_ingredient
                ]);
                $resultat = $requete->fetch(PDO::FETCH_ASSOC);
                return $resultat["id_ingredient"];
            } catch(Exception $e){
                echo "Erreur lors de la récupération de l'id de l'ingrédient : ".$e->getMessage();
                die();
            }
        } else {
            throw new InvalidArgumentException("valeur incorrecte");
        }
    }

    // Method to add an ingredient to the database
    public function addIngredient($nom_ingredient){
        if(is_string($nom_ingredient) && $nom_ingredient!="" && $nom_ingredient!= " "){
            try {
                // Check if the ingredient already exists
                $existsQuery = $this->db->prepare("SELECT COUNT(*) FROM ingredients WHERE nom_ingredient = :nom_ingredient");
                $existsQuery->execute([
                    ":nom_ingredient" => $nom_ingredient
                ]);
                $exists = $existsQuery->fetchColumn();
        
                if (!$exists) {
                    // The ingredient does not exist, add it
                    $insertQuery = $this->db->prepare("INSERT INTO ingredients(nom_ingredient) VALUES (:nom_ingredient)");
                    $insertQuery->execute([
                        ":nom_ingredient" => $nom_ingredient
                    ]);
                }
            } catch (PDOException $e) {
                // Handle any errors during ingredient addition
                echo "Erreur lors de l'ajout de l'ingrédient : " . $e->getMessage();
                die();
            }
        } else {
            throw new InvalidArgumentException("valeur incorrecte");
        }
    }

    // Method to add an ingredient to a recipe in the database
    public function addIngredientToRecette($id_recette, $id_ingredient, $quantite){
        if((is_int($id_recette) && $id_recette!=null) &&
            (is_int($id_ingredient) && $id_ingredient!=null) &&
            (is_int($quantite) && $quantite!=null)){
            try{
                $requete = $this->db->prepare("INSERT INTO recetteingredient(id_recette, id_ingredient, quantite) VALUES (:id_recette, :id_ingredient, :quantite)");
                $requete->execute([
                    ":id_recette" => $id_recette,
                    ":id_ingredient" => $id_ingredient,
                    ":quantite" => $quantite
                ]);
            } catch(Exception $e){
                echo "Erreur lors de l'ajout de l'ingrédient à la recette : ".$e->getMessage();
                die();
            }
        } else {
            throw new InvalidArgumentException("valeur incorrecte");
        }
    }
}

// Class representing a Category
class Categorie {
    private $nom_categorie;

    // Constructor to initialize the category
    public function __construct($nom_categorie)
    {
        $this->nom_categorie = $nom_categorie;
    }
}

// Class for interacting with Category data in the database

// Definition of the CategorieDAO class for managing categories in the database
class CategorieDAO {
    private $db; // Database connection

    // Constructor to set the database connection
    public function __construct($db)
    {
        $this->db = $db;
    }

    // Method to get the ID of a category based on its name
    public function getIdCategorie($nom_categorie){
        // Check if the input is a non-empty string
        if(is_string($nom_categorie) && $nom_categorie!="" && $nom_categorie!= " "){
            try{
                // Prepare and execute a database query to select the ID of the category
                $requete = $this->db->prepare("SELECT id_categorie FROM categories WHERE nom_categorie=?");
                $requete->execute([$nom_categorie]);
                // Fetch the result as an associative array
                $id_categorie = $requete->fetchAll(PDO::FETCH_ASSOC);
                // Return the ID of the category
                return $id_categorie[0]["id_categorie"];
            } catch(PDOException $e) {
                // Handle any database errors during ID retrieval
                echo "Erreur lors de la récupération de l'id".$e->getMessage();
                return [];
            }
        }
    }
    public function getCategorie($id_categorie){
        try{
            // Prepare and execute a database query to select the name of the category based on its ID
            $requete = $this->db->prepare("SELECT nom_categorie FROM categories WHERE id_categorie = :id_categorie");
            $requete->execute([
                ":id_categorie" => $id_categorie
            ]);
            // Fetch the result as an associative array
            $resultat = $requete->fetch(PDO::FETCH_ASSOC);
            // Return the name of the category
            return $resultat["nom_categorie"];
        }
        catch(Exception $e){
            // Handle any database errors during category retrieval
            echo "Erreur lors de la récupération de la catégorie : ".$e->getMessage();
            die();
        }
    }
    
    public function addCategorie($nom_categorie){
        try{
            // Prepare and execute a database query to insert a new category
            $requete = $this->db->prepare("INSERT INTO categories(nom_categorie) VALUES (:nom_categorie)");
            $requete->execute([
                ":nom_categorie" => $nom_categorie
            ]);
        }
        catch(Exception $e){
            // Handle any database errors during category addition
            echo "Erreur lors de l'ajout de la catégorie : ".$e->getMessage();
            die();
        }
    }
    
}

?>
