<?php


require_once("./back.php");
require_once("./config.php");

use PHPUnit\Framework\TestCase;

class BackTest extends TestCase{
    private $bdd;
    private $recettesDAO;
    private $ingredientDAO;
    private $categorieDAO;

    protected function setUp(): void
    {
        $this->configureDatabase();
        $this-> recettesDAO = new RecetteDAO($this->bdd);
        $this->ingredientDAO = new IngredientDAO($this->bdd);
        $this->categorieDAO = new CategorieDAO($this->bdd);
    }

    protected function configureDatabase(): void
    {
        $host = 'DB_HOST';
        $dbname = 'DB_NAME';
        $user = 'DB_USER';
        $password = 'DB_PWD';

        // Créez une instance de PDO (PHP Data Objects) pour la connexion à la base de données
        $this->bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    }

    public function testAfficherRecettes(){

    }

    public function testListerRecette($nom_recette)
    {

    }

    public function testGetId($nom_recette)
    {
        
    }
}















?>