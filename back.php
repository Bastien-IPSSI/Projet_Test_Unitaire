<?php

require_once("./config.php");

try {
    $host = DBHOST;
    $user = DBUSER;
    $pwd = DBPWD;
    $db_name = DBNAME;

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

}



?>