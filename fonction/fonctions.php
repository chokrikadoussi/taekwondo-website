<?php
/*
TODO:
PRIORITE : revoir singleton, c'est quoi ? c'est utile ? comment s'en passé ?

- revoir le workflow de création d'un utilisateur

- revoir les fonctions auth et user_login
*/


// Récupération des paramètres et constantes
require __DIR__ . "/../parametrage/param.php";

/**
 * Retourne une instance PDO unique configurée avec les constantes DB_*, ou false en cas d'erreur.
 * L'appelant devra choisir entre rediriger vers la page d'erreur (erreur.php) ou exit l'appel
 * 
 * @return PDO|false 
 */
function connexionBaseDeDonnees()
{
    static $pdo = null;

    if ($pdo === null) {
        // Construction du DSN à partir des constantes
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ));
        } catch (PDOException $e) {
            // Affiche un message simplifié et arrête le script
            // En prod, tu peux logger $e->getMessage() dans un fichier de log

            error_log(
                date('Y-m-d H:i:s') . " | connexionBaseDeDonnees() PDO erreur: " . $e->getMessage() . "\n",
                3,
                __DIR__ . '/../../logs/bdd_erreurs.log'
            );
            return false;
        }
    }

    return $pdo;
}

/**
 * Vérifie que l’e-mail est bien formé et renvoi un booléen
 * 
 * @param $email
 * @return bool
 */
function estValideMail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Vérifie que le mot de passe remplit les critères :
 * - Au moins 8 caractères
 * - Au moins une lettre majuscule
 * - Au moins un chiffre
 * - Au moins un caractère spécial (non alphanumérique)
 *
 * @param $mdp
 * @return bool
 */
function estValideMotdepasse($mdp)
{
    // Vérification de la longueur minimale
    if (mb_strlen($mdp) < 8) {
        return false;
    }

    // Vérification de la présence d’au moins une majuscule
    if (!preg_match('/[A-Z]/', $mdp)) {
        return false;
    }

    // Vérification de la présence d’au moins un chiffre
    if (!preg_match('/[0-9]/', $mdp)) {
        return false;
    }

    // Vérification de la présence d’au moins un caractère spécial
    if (!preg_match('/[^a-zA-Z\d]/', $mdp)) {
        return false;
    }

    return true;
}


/**
 * Vérifie les identifiants d’un utilisateur.
 *
 * @param string $email
 * @param string $mdp
 * @return bool
 */
function authentification($email, $mdp)
{
    $co = connexionBaseDeDonnees();
    if (!$co) {
        // Échec de la connexion, déjà loggué dans connexionBaseDeDonnees()
        return false;
    }

    try {
        $req = $co->prepare(
            'SELECT id, mdp_securise 
            FROM users 
            WHERE email = :email'
        );
        $req->execute(array("email" => $email, ));
        $resultat = $req->fetch();
    } catch (PDOException $e) {
        error_log(
            date('Y-m-d H:i:s')
            . " | authentification PDO erreur: {$e->getMessage()}"
            . " | email: {$email}\n",
            3,
            __DIR__ . '/../../logs/bdd_erreurs.log'
        );
        return false;
    }

    if (!$resultat) {
        // Aucun utilisateur trouvé
        return false;
    }

    // Vérification du mot de passe
    return password_verify($mdp, $resultat['mdp_securise']);
}

/**
 * Charge les données utilisateur en session.
 *
 * @param string $email
 * @return void
 */
function connexionUtilisateur($email)
{
    $co = connexionBaseDeDonnees();
    if (!$co) {
        return;
    }

    try {
        $req = $co->prepare(
            'SELECT id, prenom, nom, role
             FROM users
             WHERE email = :email'
        );
        $req->execute(array("email" => $email, ));
        $user = $req->fetch();
    } catch (PDOException $e) {
        error_log(
            date('Y-m-d H:i:s')
            . " | login_user PDO erreur: {$e->getMessage()}"
            . " | email: {$email}\n",
            3,
            __DIR__ . '/../../logs/bdd_erreurs.log'
        );
        return;
    }

    if ($user) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $email,
            'prenom' => $user['prenom'],
            'nom' => $user['nom'],
            'role' => $user['role'],
        ];
    }
}

/**
 * Retourne vrai si un utilisateur avec cet e-mail existe déjà, faux sinon
 * 
 * @param $email
 * @return bool
 */
function utilisateurExiste($email)
{
    if (!$co = connexionBaseDeDonnees()) {
        return false;
    }
    $req = $co->prepare('SELECT 1 FROM users WHERE email = :email');
    $req->execute(array("email" => $email, ));
    return (bool) $req->fetchColumn();
}

/**
 * Applique un nettoyage minimal à toutes les valeurs d’un tableau :
 * - trim() pour retirer espaces début/fin
 * - strip_tags() pour ôter toutes balises HTML
 *
 * @param $data Tableau associatif des champs à nettoyer
 * @return array      Tableau nettoyé
 */
function nettoyerDonnees($data)
{
    $clean = array();
    foreach ($data as $key => $value) {
        // Si c’est une chaîne, on la nettoie ; sinon (ex. int), on la conserve
        if (is_string($value)) {
            $clean[$key] = strip_tags(trim($value));
        } else {
            $clean[$key] = $value;
        }
    }
    return $clean;
}

/**
 * Crée un nouvel utilisateur (membre ou parent)
 *
 * @param array $donnees Doit contenir 'email', 'motdepasse', 'prenom', 'nom'
 * @return bool       true si enregistrement OK, false sinon
 */
function enregistrerUtilisateur($donnees)
{
    $co = connexionBaseDeDonnees();
    $sql = "
      INSERT INTO users (email, mdp_securise, prenom, nom, role)
      VALUES (:email, :mdp_securise, :prenom, :nom, :role)
    ";

    $req = $co->prepare($sql);

    try {
        return $req->execute(array(
            "email" => $donnees["email"],
            "mdp_securise" => $donnees["mdp_securise"],
            "prenom" => $donnees["prenom"],
            "nom" => $donnees["nom"],
            "role" => $donnees["role"],
        ));
    } catch (PDOException $e) {
        error_log(
            date('Y-m-d H:i:s')
            . " | enregistrerUtilisateur() PDO erreur: " . $e->getMessage()
            . " | email: " . $donnees['email'] . "\n",
            3,
            __DIR__ . '/../../logs/bdd_erreurs.log'
        );
        return false;
    }

}

/**
 * Summary of isConnected
 * @return bool
 */
function isConnected()
{
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }
    return true;
}

/**
 * Summary of getListeUtilisateurs
 * @return array
 */
function getListeUtilisateurs()
{
    $co = connexionBaseDeDonnees();
    $sql = "SELECT * FROM users";
    $req = $co->prepare($sql);

    $liste = array();
    while ($row = $req->fetch()) {
        array_push($liste, $row);
    }

    return $liste;
}

/**
 * Met à jour un utilisateur par son ID.
 *
 * @param int   $id     ID de l’utilisateur
 * @param array $fields Tableau associatif colonne=>valeur à mettre à jour
 * @return bool         true si la mise à jour a réussi, false sinon
 */
function modifierUtilisateur($id, $fields)
{
    if (!$co = connexionBaseDeDonnees()) {
        return false;
    }

    // Construction dynamique de la clause SET
    $sets = array();
    $params = array();
    foreach ($fields as $col => $val) {
        $sets[] = "$col = :$col";
        $params[":$col"] = $val;
    }
    $params[':id'] = $id;

    $sql = "
    UPDATE users 
    SET " . implode(', ', $sets) . " 
    WHERE id = :id
    ";

    try {
        $req = $co->prepare($sql);
        return $req->execute($params);
    } catch (PDOException $e) {
        // Log l’erreur en prod
        error_log(
            date('Y-m-d H:i:s') . " | updateUser PDO erreur: {$e->getMessage()} | id: {$id}\n",
            3,
            '/../../logs/bdd_erreurs.log'
        );
        return false;
    }
}

/**
 * Supprime un utilisateur par son ID.
 *
 * @param int $id ID de l’utilisateur à supprimer
 * @return bool   true si la suppression a réussi, false sinon
 */
function deleteUser(int $id): bool
{
    $pdo = connexionBaseDeDonnees();
    if (!$pdo) {
        return false;
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM `users` WHERE `id` = :id');
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        // En prod, logger l’erreur :
        error_log(
            date('Y-m-d H:i:s') . " | deleteUser PDO erreur: {$e->getMessage()} | id: {$id}\n",
            3,
            __DIR__ . '/../../logs/bdd_erreurs.log'
        );
        return false;
    }
}
