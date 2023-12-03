<?php

// Inclusion du fichier de configuration
require_once("./config.php");

try {
    // Paramètres de connexion à la base de données
    $host = DB_HOST;
    $user = DB_USER;
    $pwd = DB_PWD;
    $db_name = DB_NAME;
    $db_port = DB_PORT;

    // Création d'une connexion PDO à la base de données
    $connexion = new PDO("mysql:host=$host;port=$db_port;dbname=$db_name", $user, $pwd);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    // Gestion de l'erreur de connexion à la base de données
    echo "Erreur lors de la connexion à la base de données : " . $e->getMessage();
    die();
}

// Classe représentant une recette
class Recette {
    private $nom_recette;
    private $instructions;
    private $tmp_prep;

    // Constructeur pour initialiser les propriétés de la recette
    public function __construct($nom_recette, $instructions, $tmp_prep) {
        $this->nom_recette = $nom_recette;
        $this->instructions = $instructions;
        $this->tmp_prep = $tmp_prep;
    }

    // Méthodes getter pour récupérer les propriétés de la recette
    public function getNomRecette() {
        return $this->nom_recette;
    }

    public function getInstructions() {
        return $this->instructions;
    }

    public function getTmp_prep() {
        return $this->tmp_prep;
    }
}

// Objet d'accès aux données (DAO) pour la gestion des recettes
class RecetteDAO {
    private $bdd;

    // Constructeur pour définir la connexion à la base de données
    public function __construct($bdd) {
        $this->bdd = $bdd;
    }

    // Méthode pour récupérer toutes les recettes de la base de données
    public function afficher_recettes() {
        $liste_recette = [];
        try {
            $requete = $this->bdd->prepare("SELECT * FROM recettes");
            $requete->execute();
            $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
            // Itération à travers le résultat et création d'objets Recette
            foreach ($resultat as $recette) {
                $rec = new Recette($recette["nom_recette"], $recette["instructions"], $recette["tmp_prep"]);
                array_push($liste_recette, $rec);
            }
            return $liste_recette;
        } catch (PDOException $e) {
            // Gestion de l'erreur de base de données lors de la récupération des recettes
            echo "Erreur lors de la récupération des recettes : " . $e->getMessage();
            return [];
        }
    }

    // Méthode pour obtenir l'ID d'une recette par son nom
    public function getID($nom_recette) {
        if ($nom_recette != " " && $nom_recette != "" && is_string($nom_recette)) {
            try {
                $requete = $this->bdd->prepare("SELECT id_recette FROM recettes WHERE nom_recette = ?");
                $requete->execute([$nom_recette]);
                $resultat = $requete->fetch(PDO::FETCH_ASSOC);
                return $resultat["id_recette"];
            } catch (PDOException $e) {
                echo "Erreur lors de la récupération de l'id de la recette " . $e->getMessage();
                return;
            }
        } else {
            throw new InvalidArgumentException("Valeur incorrecte");
        }
    }

    // Méthode pour ajouter une nouvelle recette à la base de données
    public function ajouter_recette($nom_recette, $instruction, $tmp_prep, $id_categorie, $lst_ingredients, $ingredientsDAO) {
        if ((is_string($nom_recette) && $nom_recette != "" && $nom_recette != " ") &&
            (is_string($instruction) && $instruction != "" && $instruction != " ") &&
            (is_int($tmp_prep) && $tmp_prep != null) &&
            (is_int($id_categorie) && $id_categorie != null)
        ) {
            // Vérifier si la recette existe déjà
            try {
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
            if (!empty($nom_recette) && !empty($instruction) && !empty($tmp_prep) && !empty($id_categorie) && $exists == 0) {
                try {
                    $requete = $this->bdd->prepare("INSERT INTO recettes (nom_recette, instructions, tmp_prep, id_categorie) VALUES (?, ?, ?, ?)");
                    $requete->execute([$nom_recette, $instruction, $tmp_prep, $id_categorie]);
                } catch (PDOException $e) {
                    echo "Erreur lors de l'insertion" . $e->getMessage();
                }

                // Insertion des ingredients dans la table ingredients
                foreach ($lst_ingredients as $ingredient) {
                    $ingredientsDAO->addIngredient($ingredient["nom_ingredient"]);
                }

                // Insertion des ingredients dans la table recetteingredient
                for ($i = 0; $i < count($lst_ingredients); $i++) {
                    $ingredientsDAO->addIngredientToRecette($this->getID($nom_recette), $ingredientsDAO->getIdIngredient($lst_ingredients[$i]["nom_ingredient"]), $lst_ingredients[$i]["quantite"]);
                }

            } elseif ($exists == 1) {
                echo "<p>La recette existe déjà</p>";
            } else {
                echo "<p>Erreur lors de l'ajout de la recette</p>";
            }
        } else {
            throw new InvalidArgumentException("valeur incorrecte");
        }
    }

    // Méthode pour rechercher des recettes par nom
    public function rechercher_recette($nom_recette) {
        $liste_recette = [];
        if ($nom_recette != " " && $nom_recette != "" && is_string($nom_recette)) {
            try {
                $requete = $this->bdd->prepare("SELECT * FROM recettes WHERE nom_recette LIKE ?");
                $requete->execute(["%" . $nom_recette . "%"]);
                $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
                foreach ($resultat as $recette) {
                    $rec = new Recette($recette["nom_recette"], $recette["instructions"], $recette["tmp_prep"]);
                    array_push($liste_recette, $rec);
                }
                return $liste_recette;
            } catch (PDOException $e) {
                echo "Erreur lors de la récupération " . $e->getMessage();
                return [];
            }
        } else {
            throw new InvalidArgumentException("valeur incorrecte");
        }
    }

    // Méthode pour supprimer une recette par son ID
    public function supprimer_recette($id_recette) {
        if (is_int($id_recette) && $id_recette != null) {
            // Suppression des ingredients de la recette dans la table recetteingredient
            try {
                $requete = $this->bdd->prepare("DELETE FROM recetteingredient WHERE id_recette = ?");
                $requete->execute([$id_recette]);
            } catch (PDOException $e) {
                echo "Erreur lors de la suppression" . $e->getMessage();
            }

            // Suppression de la recette dans la table recettes
            try {
                $requete = $this->bdd->prepare("DELETE FROM recettes WHERE id_recette = ?");
                $requete->execute([$id_recette]);
            } catch (PDOException $e) {
                echo "Erreur lors de la suppression" . $e->getMessage();
            }
        } else {
            throw new InvalidArgumentException("valeur incorrecte");
        }
    }
}

// Classe représentant un ingrédient
class Ingredient {
    private $nom_ingredient;
    private $quantite;

    // Constructeur pour initialiser les propriétés de l'ingrédient
    public function __construct($nom_ingredient, $quantite) {
        $this->nom_ingredient = $nom_ingredient;
        $this->quantite = $quantite;
    }

    // Méthodes getter pour récupérer les propriétés de l'ingrédient
    public function getNomIngredient() {
        return $this->nom_ingredient;
    }

    public function getQuantite() {
        return $this->quantite;
    }
}

// Objet d'accès aux données (DAO) pour la gestion des ingrédients
class IngredientDAO {
    private $db;

    // Constructeur pour définir la connexion à la base de données
    public function __construct($db) {
        $this->db = $db;
    }

    // Méthode pour obtenir tous les ingrédients d'une recette par son ID
    public function getAllIngredientOfRecette($id_recette) {
        $ids_ingredient = [];
        $ingredients = [];
        if ($id_recette != null && is_int($id_recette)) {
            try {
                $requete = $this->db->prepare("SELECT id_ingredient FROM recetteingredient WHERE id_recette = :id_recette");
                $requete->execute([
                    ":id_recette" => $id_recette
                ]);
                $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
                foreach ($resultat as $id_ingredient) {
                    array_push($ids_ingredient, $id_ingredient["id_ingredient"]);
                }
            } catch (Exception $e) {
                echo "Erreur lors de la récupération des ingrédients de la recette : " . $e->getMessage();
                die();
            }

            foreach ($ids_ingredient as $id_ingredient) {
                try {
                    $requete = $this->db->prepare("SELECT nom_ingredient FROM ingredients WHERE id_ingredient = :id_ingredient");
                    $requete->execute([
                        ":id_ingredient" => $id_ingredient
                    ]);
                    $resultat = $requete->fetch(PDO::FETCH_ASSOC);
                    array_push($ingredients, new Ingredient($resultat["nom_ingredient"]));
                } catch (Exception $e) {
                    echo "Erreur lors de la récupération des ingrédients de la recette : " . $e->getMessage();
                    die();
                }
            }
            var_dump($ingredients);
            return $ingredients;
        } else {
            throw new InvalidArgumentException("valeur incorrecte");
        }
    }

    // Méthode pour obtenir l'ID d'un ingrédient par son nom
    public function getIdIngredient($nom_ingredient) {
        if (is_string($nom_ingredient) && $nom_ingredient != "" && $nom_ingredient != " ") {
            try {
                $requete = $this->db->prepare("SELECT id_ingredient FROM ingredients WHERE nom_ingredient = :nom_ingredient");
                $requete->execute([
                    ":nom_ingredient" => $nom_ingredient
                ]);
                $resultat = $requete->fetch(PDO::FETCH_ASSOC);
                return $resultat["id_ingredient"];
            } catch (Exception $e) {
                echo "Erreur lors de la récupération de l'id de l'ingrédient : " . $e->getMessage();
                die();
            }
        } else {
            throw new InvalidArgumentException("valeur incorrecte");
        }
    }

    // Méthode pour ajouter un nouvel ingrédient à la base de données
    public function addIngredient($nom_ingredient) {
        if (is_string($nom_ingredient) && $nom_ingredient != "" && $nom_ingredient != " ") {
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

        } else {
            throw new InvalidArgumentException("valeur incorrecte");
        }
    }

    // Méthode pour ajouter un ingrédient à une recette
    public function addIngredientToRecette($id_recette, $id_ingredient, $quantite) {
        if ((is_int($id_recette) && $id_recette != null) && (is_int($id_ingredient) && $id_ingredient != null) && (is_int($quantite) && $quantite != null)) {
            try {
                $requete = $this->db->prepare("INSERT INTO recetteingredient(id_recette, id_ingredient, quantite) VALUES (:id_recette, :id_ingredient, :quantite)");
                $requete->execute([
                    ":id_recette" => $id_recette,
                    ":id_ingredient" => $id_ingredient,
                    ":quantite" => $quantite
                ]);
            } catch (Exception $
            } catch (Exception $e) {
                // Gérer l'erreur si nécessaire lors de l'ajout d'un ingrédient à une recette
                echo "Erreur lors de l'ajout de l'ingrédient à la recette : " . $e->getMessage();
                die();
            }
        } else {
            throw new InvalidArgumentException("valeur incorrecte");
        }
    }
}

// Classe représentant une catégorie de recettes
class Categorie {
    private $nom_categorie;

    // Constructeur pour initialiser le nom de la catégorie
    public function __construct($nom_categorie) {
        $this->nom_categorie = $nom_categorie;
    }
}

// Objet d'accès aux données (DAO) pour la gestion des catégories
class CategorieDAO {
    private $db;

    // Constructeur pour définir la connexion à la base de données
    public function __construct($db) {
        $this->db = $db;
    }

    // Méthode pour obtenir l'ID d'une catégorie par son nom
    public function getIdCategorie($nom_categorie) {
        if (is_string($nom_categorie) && $nom_categorie != "" && $nom_categorie != " ") {
            try {
                $requete = $this->db->prepare("SELECT id_categorie FROM categories WHERE nom_categorie=?");
                $requete->execute([$nom_categorie]);
                $id_categorie = $requete->fetchAll(PDO::FETCH_ASSOC);
                return $id_categorie[0]["id_categorie"];
            } catch (PDOException $e) {
                echo "Erreur lors de la récupération de l'ID : " . $e->getMessage();
                return [];
            }
        }
    }

    // Méthode pour obtenir le nom d'une catégorie par son ID
    public function getCategorie($id_categorie) {
        if (is_int($id_categorie) && $id_categorie != null) {
            try {
                $requete = $this->db->prepare("SELECT nom_categorie FROM categories WHERE id_categorie = :id_categorie");
                $requete->execute([
                    ":id_categorie" => $id_categorie
                ]);
                $resultat = $requete->fetch(PDO::FETCH_ASSOC);
                return $resultat["nom_categorie"];
            } catch (Exception $e) {
                echo "Erreur lors de la récupération de la catégorie : " . $e->getMessage();
                die();
            }
        } else {
            throw new InvalidArgumentException("valeur incorrecte");
        }
    }

    // Méthode pour obtenir toutes les catégories
    public function getAllCategorie() {
        $liste_categorie = [];
        try {
            $requete = $this->db->prepare("SELECT * FROM categories");
            $requete->execute();
            // Récupérer une liste de chaînes de caractères correspondant aux noms des catégories
            $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
            foreach ($resultat as $categorie) {
                array_push($liste_categorie, $categorie["nom_categorie"]);
            }
            return $liste_categorie;
        } catch (PDOException $e) {
            // Gérer l'erreur de base de données lors de la récupération des catégories
            echo "Erreur lors de la récupération : " . $e->getMessage();
            return [];
        }
    }
}

?>
