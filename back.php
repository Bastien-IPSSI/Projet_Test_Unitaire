<?php

require_once("./config.php");

try {
    $host = DB_HOST;
    $user = DB_USER;
    $pwd = DB_PWD;
    $db_name = DB_NAME;
    $db_port = DB_PORT;

    $connexion = new PDO("mysql:host=$host;port=$db_port;dbname=$db_name", $user, $pwd);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "Erreur lors de la connexion à la database : " . $e->getMessage();
    die();
}

class Recette {

    private $nom_recette;
    private $instructions;
    private $tmp_prep;

    public function __construct($nom_recette, $instructions, $tmp_prep)
    {
        $this->nom_recette = $nom_recette;
        $this->instructions = $instructions;
        $this->tmp_prep = $tmp_prep;
    }

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

    public function setNomRecette($nom_recette){
        $this->nom_recette=$nom_recette;
    }

    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;
    }

    public function setTmp_prep($tmp_prep)
    {
        $this->tmp_prep = $tmp_prep;
    }
}

class RecetteDAO {
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    public function afficher_recettes(){
        $liste_recette = [];
        try{
            $requete = $this->bdd->prepare("SELECT * FROM recettes");
            $requete->execute();
            $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultat as $recette){
                $rec = new Recette($recette["nom_recette"], $recette["instructions"], $recette["tmp_prep"]);
                array_push($liste_recette, $rec);
            }
            return $liste_recette;
        }catch(PDOException $e){
            echo "Erreur lors de la récupération ".$e->getMessage();
            return [];
        }
    }

    public function lister_recette($nom_recette){
        try{
            $requete = $this->bdd->prepare("SELECT * FROM recettes WHERE nom_recette=?");
            $requete->execute([$nom_recette]);
            $result = $requete->fetch(PDO::FETCH_ASSOC);
            return $result;
        }catch(PDOException $e){
            echo "Erreur lors de la récupération des recettes ".$e->getMessage();
            return [];
        }
    }
    public function getID($nom_recette){
        try{
            $requete = $this->bdd->prepare("SELECT id_recette FROM recettes WHERE nom_recette = ?");
            $requete->execute([$nom_recette]);
            $resultat = $requete->fetch(PDO::FETCH_ASSOC);
            return $resultat["id_recette"];
        }catch(PDOException $e){
            echo "Erreur lors de la récupération de l'id de la recette ".$e->getMessage();
            return;
        }
    }

    public function getIdCategorie($nom_recette){
        try{
            $requete = $this->bdd->prepare("SELECT id_categorie FROM recettes WHERE nom_recette = ?");
            $requete->execute([$nom_recette]);
            $resultat = $requete->fetch(PDO::FETCH_ASSOC);
            return $resultat["id_categorie"];
        }catch(PDOException $e){
            echo "Erreur lors de la récupération de l'id de la catégorie ".$e->getMessage();
            return;
        }
    }

    public function afficher_la_recette($idRecette)
    {
        try{
            $requete = $this->bdd->prepare("SELECT * FROM recettes WHERE id_recette=?");
            $requete->execute([$idRecette]);
            $result = $requete->fetch(PDO::FETCH_ASSOC);
            return $result;
        }catch(PDOException $e){
            echo "Erreur lors de la réupération de l'ingrédient".$e->getMessage();
            return [];
        }
    }

    public function ajouter_recette($nom_recette,$instruction,$tmp_prep,$id_categorie, $lst_ingredients, $ingredientsDAO){

        // Vérifier si la recette existe déjà
        try{
            $requete = $this->bdd->prepare("SELECT COUNT(*) FROM recettes WHERE nom_recette = :nom_recette");
            $requete->execute([
                ":nom_recette" => $nom_recette
            ]);
            
            $exists = $requete->fetchColumn();

            if ($exists) {
                // La recette existe déjà, on ne l'ajoute pas sans afficher d'erreur
                echo "La recette existe déjà";
            }
        } catch (PDOException $e) {
            // Gérer l'erreur si nécessaire
            echo "Erreur lors de l'ajout de la recette : " . $e->getMessage();
            die();
        }

        // Insertion de la recette dans la table recette
        if(!empty($nom_recette) && !empty($instruction) && !empty($tmp_prep) && !empty($id_categorie) && $exists == 0){
            try{
                $requete = $this->bdd->prepare("INSERT INTO recettes (nom_recette, instructions, tmp_prep, id_categorie) VALUES (?, ?, ?, ?)");
                $requete->execute([$nom_recette,$instruction,$tmp_prep,$id_categorie]);
            }catch(PDOException $e){
                echo "Erreur lors de l'insertion".$e->getMessage();
            }
    
            // Insertion des ingredients dans la table ingredients

            foreach($lst_ingredients as $ingredient){
                $ingredientsDAO->addIngredient($ingredient["nom_ingredient"]);
            }
    
            // Insertion des ingredients dans la table recetteingredient
    
            for($i=0; $i<count($lst_ingredients); $i++){
                $ingredientsDAO->addIngredientToRecette($this->getID($nom_recette), $ingredientsDAO->getIdIngredient($lst_ingredients[$i]["nom_ingredient"]), $lst_ingredients[$i]["quantite"]);
            }
            
        }elseif($exists == 1){
            echo "<p>La recette existe déjà</p>";
        }
        else{
            echo "<p>Erreur lors de l'ajout de la recette</p>";
        }
    }

    public function rechercher_recette($nom_recette){
        $liste_recette = [];
        try{
            $requete = $this->bdd->prepare("SELECT * FROM recettes WHERE nom_recette LIKE ?");
            $requete->execute(["%".$nom_recette."%"]);
            $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultat as $recette){
                $rec = new Recette($recette["nom_recette"], $recette["instructions"], $recette["tmp_prep"]);
                array_push($liste_recette, $rec);
            }
            return $liste_recette;
        }catch(PDOException $e){
            echo "Erreur lors de la récupération ".$e->getMessage();
            return [];
        }
    }

}

class Ingredient{

    private $nom_ingredient;
    private $quantite;

    public function __construct($nom_ingredient, $quantite)
    {
        $this->nom_ingredient = $nom_ingredient;
        $this->quantite = $quantite;
    }

    public function getNomIngredient(){
        return $this->nom_ingredient;
    }

    public function getQuantite(){
        return $this->quantite;
    }

}


class IngredientDAO {
private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllIngredientOfRecette($id_recette){
        $ids_ingredient = [];
        $ingredients = [];
        try{
            $requete = $this->db->prepare("SELECT id_ingredient FROM recetteingredient WHERE id_recette = :id_recette");
            $requete->execute([
                ":id_recette" => $id_recette
            ]);
            $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultat as $id_ingredient){
                array_push($ids_ingredient, $id_ingredient["id_ingredient"]);
            }
        }
        catch(Exception $e){
            echo "Erreur lors de la récupération des ingrédients de la recette : ".$e->getMessage();
            die();
        }

        foreach($ids_ingredient as $id_ingredient){
            try{
                $requete = $this->db->prepare("SELECT nom_ingredient, quantite FROM ingredients INNER JOIN recetteingredient ON ingredients.id_ingredient = recetteingredient.id_ingredient WHERE ingredients.id_ingredient = :id_ingredient");
                $requete->execute([
                    ":id_ingredient" => $id_ingredient
                ]);
                $resultat = $requete->fetch(PDO::FETCH_ASSOC);
                $ingredient = new Ingredient($resultat["nom_ingredient"], $resultat["quantite"]);
                array_push($ingredients, $ingredient);
            }
            catch(Exception $e){
                echo "Erreur lors de la récupération des ingrédients de la recette : ".$e->getMessage();
                die();
            }
        }
        return $ingredients;
    }

    public function getIdIngredient($nom_ingredient){
        try{
            $requete = $this->db->prepare("SELECT id_ingredient FROM ingredients WHERE nom_ingredient = :nom_ingredient");
            $requete->execute([
                ":nom_ingredient" => $nom_ingredient
            ]);
            $resultat = $requete->fetch(PDO::FETCH_ASSOC);
            return $resultat["id_ingredient"];
        }
        catch(Exception $e){
            echo "Erreur lors de la récupération de l'id de l'ingrédient : ".$e->getMessage();
            die();
        }
    }

    public function getIngredient($id_ingredient){
        try{
            $requete = $this->db->prepare("SELECT nom_ingredient FROM ingredients WHERE id_ingredient = :id_ingredient");
            $requete->execute([
                ":id_ingredient" => $id_ingredient
            ]);
            $resultat = $requete->fetch(PDO::FETCH_ASSOC);
            return $resultat["nom_ingredient"];
        }
        catch(Exception $e){
            echo "Erreur lors de la récupération de l'ingrédient : ".$e->getMessage();
            die();
        }
    }

    public function addIngredient($nom_ingredient){
        try {
            // Vérifier d'abord si l'ingrédient existe
            $existsQuery = $this->db->prepare("SELECT COUNT(*) FROM ingredients WHERE nom_ingredient = :nom_ingredient");
            $existsQuery->execute([
                ":nom_ingredient" => $nom_ingredient
            ]);
            
            $exists = $existsQuery->fetchColumn();
    
            if (!$exists) {
                // L'ingrédient n'existe pas, on l'ajoute
                $insertQuery = $this->db->prepare("INSERT INTO ingredients(nom_ingredient) VALUES (:nom_ingredient)");
                $insertQuery->execute([
                    ":nom_ingredient" => $nom_ingredient
                ]);
            }
        } catch (PDOException $e) {
            // Gérer l'erreur si nécessaire
            echo "Erreur lors de l'ajout de l'ingrédient : " . $e->getMessage();
            die();
        }
    }
    

    public function addIngredientToRecette($id_recette, $id_ingredient, $quantite){
        try{
            $requete = $this->db->prepare("INSERT INTO recetteingredient(id_recette, id_ingredient, quantite) VALUES (:id_recette, :id_ingredient, :quantite)");
            $requete->execute([
                ":id_recette" => $id_recette,
                ":id_ingredient" => $id_ingredient,
                ":quantite" => $quantite
            ]);
        }
        catch(Exception $e){
            echo "Erreur lors de l'ajout de l'ingrédient à la recette : ".$e->getMessage();
            die();
        }
    }
}

class Categorie {
    private $nom_categorie;

    public function __construct($nom_categorie)
    {
        $this->nom_categorie = $nom_categorie;
    }

    public function getCategorie(){
        return $this -> nom_categorie;
    }

    public function setNomCategorie($nom_categorie)
    {
        $this->nom_categorie = $nom_categorie;
    }

}

class CategorieDAO {
private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getIdCategorie($nom_categorie){
        try{
            $requete = $this->db->prepare("SELECT id_categorie FROM categories WHERE nom_categorie=?");
            $requete->execute([$nom_categorie]);
            $id_categorie = $requete->fetchAll(PDO::FETCH_ASSOC);
            return $id_categorie[0]["id_categorie"];
        }catch(PDOException $e){
            echo "Erreur lors de la récupération de l'id".$e->getMessage();
            return [];
        }
    }
    public function getCategorie($id_categorie){
        try{
            $requete = $this->db->prepare("SELECT nom_categorie FROM categories WHERE id_categorie = :id_categorie");
            $requete->execute([
                ":id_categorie" => $id_categorie
            ]);
            $resultat = $requete->fetch(PDO::FETCH_ASSOC);
            return $resultat["nom_categorie"];
        }
        catch(Exception $e){
            echo "Erreur lors de la récupération de la catégorie : ".$e->getMessage();
            die();
        }
    }

    public function addCategorie($nom_categorie){
        try{
            $requete = $this->db->prepare("INSERT INTO categories(nom_categorie) VALUES (:nom_categorie)");
            $requete->execute([
                ":nom_categorie" => $nom_categorie
            ]);
        }
        catch(Exception $e){
            echo "Erreur lors de l'ajout de la catégorie : ".$e->getMessage();
            die();
        }
    }
}



?>