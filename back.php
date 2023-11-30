<?php

require_once("./config.php");

try {
    $host = DB_HOST;
    $user = DB_USER;
    $pwd = DB_PWD;
    $db_name = DB_NAME;

    $connexion = new PDO("mysql:host=$host;dbname=$db_name", $user, $pwd);
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
            $requete = $this->bdd->prepare("SELECT id FROM recettes WHERE nom_recette = ?");
            $requete->execute([$nom_recette]);
            return $requete;
        }catch(PDOException $e){
            echo "Erreur lors de la récupération de l'id de la recette ".$e->getMessage();
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

}

class Ingredient{

    private $nom_ingredient;

    public function __construct($nom_ingredient)
    {
        $this->nom_ingredient = $nom_ingredient;
    }

    public function getNom_ingredient(){
        return $this->nom_ingredient;
    }

    public function setNom_ingredient($nom_ingredient)
    {
        $this->nom_ingredient = $nom_ingredient;
    }
}


class IngredientDAO {
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
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
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

}



?>