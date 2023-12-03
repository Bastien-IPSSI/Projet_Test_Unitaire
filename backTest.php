<?php

require_once "./back.php";
require_once "./config.php";

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
        // Utilisation de constantes (si définies)
        $host = defined('DB_HOST') ? constant('DB_HOST') : 'localhost';
        $dbname = defined('DB_NAME') ? constant('DB_NAME') : 'gestion_recette';
        $user = defined('DB_USER') ? constant('DB_USER') : 'testPOO';
        $password = defined('DB_PWD') ? constant('DB_PWD') : 'azerty';
    
        // Créez une instance de PDO (PHP Data Objects) pour la connexion à la base de données
        $this->bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    }
    
    

//     public function testAfficherRecette(){
//         $this->recettesDAO->afficher_recettes();
//         $stm = $this->bdd->query('SELECT nom_recette, instructions, tmp_prep FROM recettes');
//         $recettes = $stm->fetchAll(PDO::FETCH_ASSOC);
//         $this->assertEquals([
//             'nom_recette' => 'Poulet Alfredo',
//             'instructions' => 'Faire cuire le poulet et les pâtes. Mélanger avec la sauce Alfredo.',
//             'tmp_prep' => 30,
//         ],$recettes[0]);
//         $this->assertEquals([
//             'nom_recette' => 'Saumon Grillé aux Asperges',
//             'instructions' => 'Griller le saumon et les asperges. Assaisonner avec du citron et du poivre.',
//             'tmp_prep' => 25,
//         ],$recettes[1]);
//     }
//     /**
//      * @dataProvider providerGetId
//      */
//     public function testGetId($nom_recette, $expected)
//     {
//         if ($nom_recette != " " && $nom_recette != "" && is_string($nom_recette)) {
//             // Appel de la méthode getID
//             $id = $this->recettesDAO->getID($nom_recette);
    
//             // Préparez une requête SQL pour récupérer l'id_recette de la base de données
//             $stm = $this->bdd->prepare('SELECT id_recette FROM recettes WHERE nom_recette=?');
//             $stm->execute([$nom_recette]);
    
//             // Récupérez le résultat de la requête
//             $recette = $stm->fetch(PDO::FETCH_ASSOC);
    
//             // Vérifiez si l'id retourné par la méthode correspond à celui dans la base de données
//             $this->assertEquals($expected, $id);
    
//             // Vérifiez si l'id retourné par la méthode correspond à celui dans la base de données
//             $this->assertEquals($expected, $recette["id_recette"]);
//         } else {
//             // Si $nom_recette n'est pas valide, une exception InvalidArgumentException est attendue
//             $this->expectException(InvalidArgumentException::class);
//             $this->expectExceptionMessage("Valeur incorrecte");
    
//             // Appel de la méthode getID avec une valeur incorrecte pour déclencher l'exception
//             $this->recettesDAO->getID($nom_recette);
//         }
//     }
    

//     public static function providerGetId(){
//         return [
//             [" ",null],
//             [123,null],
//             [["df"],null],
//             ["Poulet Alfredo",1],
//             ["Alloco",4],
//         ];
//     }

//     /**
//      * @dataProvider providerListerRecette
//      */
//     public function testListerRecette($nom_recette,$instruction,$tmp_prep,$id_categorie, $lst_ingredients, $ingredientsDAO,$expected)
//     {
//         if((is_string($nom_recette) && $nom_recette!="" && $nom_recette!=" ") && (is_string($instruction) && $instruction!="" && $instruction!=" ") && (is_int($tmp_prep) && $tmp_prep!=null) && (is_int($id_categorie) && $id_categorie!=null))
//         {
//             $this->recettesDAO->ajouter_recette($nom_recette,$instruction,$tmp_prep,$id_categorie, $lst_ingredients, $ingredientsDAO);
//             $stm = $this->bdd->prepare("SELECT nom_recette,instructions,tmp_prep FROM recettes WHERE nom_recette=?");
//             $stm->execute([$nom_recette]);
//             $recettes = $stm->fetch(PDO::FETCH_ASSOC);
//             $this->assertEquals($expected,$recettes);
//         }
//         else{
//             $this->expectException(InvalidArgumentException::class);
//             $this->expectExceptionMessage("valeur incorrecte");
//             $this->recettesDAO->ajouter_recette($nom_recette,$instruction,$tmp_prep,$id_categorie, $lst_ingredients, $ingredientsDAO);
//         }
//     }

//     public static function providerListerRecette()
//     {
//         return [
//             // Cas de test avec des valeurs valides
//             [
//                 'nom_recette' => 'Alloco',
//                 'instruction' => "Faitre frire dans une casserolle et c'eest tout.",
//                 'tmp_prep' => 30,
//                 'id_categorie' => 6,
//                 'lst_ingredients' => [
//                     ['nom_ingredient' => 'Banane plantin', 'quantite' => 5],
//                 ],
//                 'ingredientsDAO' => IngredientDAO::class, 
//                 'expected' => [
//                     'nom_recette' => 'Alloco',
//                     'instructions' => "Faitre frire dans une casserolle et c'eest tout.",
//                     'tmp_prep' => 30,
//                 ],
//             ],
//             [
//                 'nom_recette' => ' ',
//                 'instruction' => "Faitre frire dans une casserolle et c'eest tout.",
//                 'tmp_prep' => 30,
//                 'id_categorie' => 6,
//                 'lst_ingredients' => [
//                     ['nom_ingredient' => 'Banane plantin', 'quantite' => 5],
//                 ],
//                 'ingredientsDAO' => IngredientDAO::class, 
//                 'expected' => null,
//             ],
//             [
//                 'nom_recette' => 'Alloco',
//                 'instruction' => "Faitre frire dans une casserolle et c'eest tout.",
//                 'tmp_prep' => "30",
//                 'id_categorie' => 6,
//                 'lst_ingredients' => [
//                     ['nom_ingredient' => 'Banane plantin', 'quantite' => 5],
//                 ],
//                 'ingredientsDAO' => IngredientDAO::class, 
//                 'expected' => null,
//             ],

//         ];
//     }
    
// /**
//  * @dataProvider providerRecherche
//  */
// public function testRecherche($nom_recette, $expected)
// {
//     if ($nom_recette != " " && $nom_recette != "" && is_string($nom_recette)) {
//         $resultat = $this->recettesDAO->rechercher_recette($nom_recette);
//         $this->assertInstanceOf(Recette::class, $resultat[0]); // Vérifiez que le résultat est une instance de Recette
//         $this->assertEquals($expected["nom_recette"], $resultat[0]->getNomRecette());
//         $this->assertEquals($expected["instructions"], $resultat[0]->getInstructions());
//         $this->assertEquals($expected["tmp_prep"], $resultat[0]->getTmp_prep());
//     } else {
//         $this->expectException(InvalidArgumentException::class);
//         $this->expectExceptionMessage("valeur incorrecte");
//         $this->recettesDAO->rechercher_recette($nom_recette);
//     }
// }


// public static function providerRecherche()
// {
//     return [
//         [
//             "Riz sauté au Tofu et Champignons", 
//             [
//                 "nom_recette" => "Riz sauté au Tofu et Champignons",
//                 "instructions" => "Sauter le tofu et les champignons. Ajouter le riz cuit et assaisonner.",
//                 "tmp_prep" => "20", // Notez que tmp_prep est une chaîne, car les résultats de la recherche sont des chaînes
//             ]
//         ],
//         [" ", null],

//     ];
// }

//     /**
//  * @dataProvider ingredientsProvider
//  */
// public function testIngredientsInRecette($expectedIngredients, $recetteId) {
//     if($recetteId!=null && is_int($recetteId)){
//         $ingredients = $this->ingredientDAO->getAllIngredientOfRecette($recetteId);
    
//         // Comparer les ingrédients récupérés avec ceux attendus
//         $this->assertEquals($expectedIngredients, $ingredients);
//     }
//     else{
//         $this-> expectException(InvalidArgumentException::class);
//         $this->expectExceptionMessage("valeur incorrecte");
//         $ingredients = $this->ingredientDAO->getAllIngredientOfRecette($recetteId);
//     }
//     // Appeler la méthode getAllIngredientOfRecette pour récupérer les ingrédients
// }

// public static function ingredientsProvider() {
//     // Définir les ingrédients attendus et l'ID de la recette pour chaque cas de test
//     return [
//         [
//             [
//                 new Ingredient("Saumon"),
//                 new Ingredient("Asperges"),
//             ],
//             2,
//         ],
//         [
//             null,"é"
//         ],
//         [null,""],
//         [null," "],

//     ];
// }

/**
 * @dataprovider providerGetIdIngredient
 */

//  public function testGetIdIngredient($nom_ingredient, $expected) {
//     if (is_string($nom_ingredient) && $nom_ingredient != "" && $nom_ingredient != " ") {
//         // Appeler la méthode getIdIngredient avant d'exécuter la requête SQL
//         $result = $this->ingredientDAO->getIdIngredient($nom_ingredient);

//         // Exécuter la requête SQL après l'appel à getIdIngredient
//         $stm = $this->bdd->prepare('SELECT id_ingredient FROM ingredients WHERE nom_ingredient = ?');
//         $stm->execute([$nom_ingredient]);
//         $resultats = $stm->fetch(PDO::FETCH_ASSOC);

//         $this->assertEquals($expected, $result);
//         $this->assertEquals($expected, $resultats['id_ingredient']);
//     } else {
//         $this->expectException(InvalidArgumentException::class);
//         $this->expectExceptionMessage('valeur incorrecte');

//         // Appeler la méthode getIdIngredient pour générer l'exception
//         $this->ingredientDAO->getIdIngredient($nom_ingredient);
//     }
// }

// public static function providerGetIdIngredient() {
//     return [
//         ["Alloco", 4],
//         ["",null],
//         [" ",null],
//         [1234,null],
//         ["1234",null]
//     ];
// }


    // /**
    //  * @dataProvider providerAddIngredient
    //  */
    // public function testAddIngredient($nom_ingredient, $expected) {
    //     if (is_string($nom_ingredient) && $nom_ingredient != "" && $nom_ingredient != " ") {
    //         $this->ingredientDAO->addIngredient($nom_ingredient);
    //         $stm = $this->bdd->prepare("SELECT nom_ingredient FROM ingredients WHERE nom_ingredient=?");
    //         $stm->execute([$nom_ingredient]);
    //         $resultats = $stm->fetch(PDO::FETCH_ASSOC);
    //         $this->assertEquals($expected, $resultats["nom_ingredient"]);
    //     } else {
    //         $this->expectException(InvalidArgumentException::class);
    //         $this->expectExceptionMessage("valeur incorrecte");
    //         $this->ingredientDAO->addIngredient($nom_ingredient);
    //     }
    // }
    

    // public static function providerAddIngredient(){
    //     return [
    //         ["lait","lait"],
    //         ["",null],
    //         [" ",null],
    //         [11,null],
    //     ];
    // }

// /**
//  * @dataProvider providerAddIngredientToRecette
//  */
// public function testAddIngredientToRecette($id_recette, $id_ingredient, $quantite, $expected) {
//     if((is_int($id_recette) && $id_recette!=null) && (is_int($id_ingredient) && $id_ingredient!=null) && (is_int($quantite) && $quantite!=null)){
//         $this->ingredientDAO->addIngredientToRecette($id_recette, $id_ingredient, $quantite);
    
//         $stm = $this->bdd->prepare("SELECT * FROM recetteingredient WHERE id_recette = ? AND id_ingredient = ?");
//         $stm->execute([$id_recette, $id_ingredient]);
//         $resultats = $stm->fetch(PDO::FETCH_ASSOC);
    
//         $this->assertEquals($expected, $resultats);
//     }else{
//                     $this->expectException(InvalidArgumentException::class);
//             $this->expectExceptionMessage("valeur incorrecte");
//             $this->ingredientDAO->addIngredientToRecette($id_recette, $id_ingredient, $quantite);
//     }
// }

// public function providerAddIngredientToRecette() {
//     return [
//         // Cas de test avec des valeurs valides
//         [11, 27, 3, ['id_recette' => 11, 'id_ingredient' => 27, 'quantite' => 3]],
//         ["",""," ",null]
//         // Ajoutez d'autres cas de test au besoin
//     ];
// }
/**
 * @dataProvider providerGetIdCategorie
 */
public function testGetIdCategorie($nom_categorie, $expected) {
    if (is_string($nom_categorie) && $nom_categorie != "" && $nom_categorie != " ") {
        $this->categorieDAO->getIdCategorie($nom_categorie);
        $stm = $this->bdd->prepare("SELECT id_categorie FROM categories WHERE nom_categorie=?");
        $stm->execute([$nom_categorie]);
        $resultats = $stm->fetchAll(PDO::FETCH_ASSOC);

        // Comparaison des valeurs individuelles plutôt que des arrays complets
        $actualIds = array_column($resultats, 'id_categorie');

        // Utiliser assertEquals pour une comparaison plus explicite
        $this->assertEquals($expected, $actualIds, "Les identifiants de catégorie ne correspondent pas.");
    } else {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("valeur incorrecte");
        $this->categorieDAO->getIdCategorie($nom_categorie);
    }
}

public static function providerGetIdCategorie(){
    return [
        ["Poisson", [2, 4]],
    ];
}

}
?>
