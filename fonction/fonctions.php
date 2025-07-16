<?php
/**
 * @author Chokri Kadoussi
 * @date 2025-07-15
 * @version 1.0.0
 * 
 * Présentation du fichier :
 * 
 *     hehe
 *     hehe
 * 
 * TODO:
 * - Organiser le fichier
 * - Ajouter les commentaires inter-fonctions
 * - Ajouter les commentaires PHP Doc
 * 
 * ==================================================================================================
 *                 FICHIER REGROUPANT L'ENSEMBLE DES FONCTIONS UTILISEES PAR LE SITE                 
 * ==================================================================================================
 * 
 */

// Récupération des paramètres et constantes
require_once __DIR__ . "/../parametrage/param.php";


//==================================================================================================
//                                    BASE DE DONNEES / LOGS                 
//==================================================================================================



/**
 * Fonction utilitaire pour logger les erreurs de manière cohérente
 * @param string $function Nom de la fonction qui a généré l'erreur
 * @param string $message Message d'erreur à logguer
 * @param array $context Tableau associatif lié au contexte de l'erreur
 */
function logErreur(string $function, string $message, array $context = array()): void
{
    $contextStr = !empty($context) ? json_encode($context) : '';
    error_log(
        date('Y-m-d H:i:s') . " | " . $function . "() : " . $message . " | Contexte : {" . $contextStr . "}\n",
        3,
        LOG_PATH
    );
}

/**
 * Crée et maintient une connexion unique à la base de données MySQL via PDO
 * L'appelant s'occupera de l'exception puis de la redirection en cas d'erreur (ex: vers la page erreur.php)
 * 
 * @return PDO Instance de connexion configuré à  partir des constantes DB_*
 * @throws Exception En cas d'erreur de connexion
 */
function connexionBaseDeDonnees(): PDO
{
    // Utilisation d'un pattern singleton permettant l'unicité de connexion à la BDD.
    static $co = null;

    // Si la connexion n'est pas déjà établie, on la crée
    if ($co === null) {
        // Construction du DSN à partir des constantes
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        try {
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // permet de lever une PDOException pour toute erreur SQL (connexion, requête mal formée…)
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // chaque ligne est retournée sous la forme d’un tableau associatif donc plus de doublon de données
            );
            $co = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Enregistrer le log d'erreur dans le fichier bdd_erreurs.log
            logErreur(__FUNCTION__, $e->getMessage());
            throw new Exception("Erreur de connexion à la base de données");
        }
    }
    return $co;
}

//==================================================================================================
//                                         UTILISATEURS                 
//==================================================================================================

/**
 * Vérifie que l’e-mail est bien formatté
 * 
 * @param $email L'adresse email à vérifier
 * @return bool True si l'email est bien formatté, false sinon
 */
function estValideMail(string $email): bool
{
    // Vérifier la longueur maximale (RFC 5321)
    if (mb_strlen(trim($email)) > 254) {
        return false;
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}


/**
 * Vérifie que le mot de passe remplit les critères :
 * - Au moins 8 caractères
 * - Au moins une lettre majuscule
 * - Au moins un chiffre
 * - Au moins un caractère spécial (non alphanumérique)
 *
 * @param string $mdp Mot de passe à vérifier
 * @return bool True si le mot de passe est valide, false sinon
 */
function estValideMotdepasse(string $mdp): bool
{
    $criteres = array(
        mb_strlen($mdp) >= PSSWD_MIN_LEN,              // longueur minimale
        preg_match('/[A-Z]/', $mdp),         // Au moins une majuscule
        preg_match('/[0-9]/', $mdp),         // Au moins un chiffre
        preg_match('/[^a-zA-Z\d]/', $mdp),   // Au moins un caractère spécial
    );

    // Si "false" n'est pas trouvé dans le tableau $critères alors retourne True
    return !in_array(false, $criteres, true);
}

/**
 * Authentifie un utilisateur avec email et mot de passe
 *
 * @param string $email Email de l'utilisateur
 * @param string $mdp Mot de passe de l'utilisateur
 * @return bool True si les identifiants sont existants et valides, false sinon
 * @throws Exception En cas d'erreur de BDD durant l'auth
 */
function authentification(string $email, string $mdp): bool
{
    $co = connexionBaseDeDonnees();

    try {
        // Récupération de l'email en base
        $req = $co->prepare(
            'SELECT id, mdp_securise 
            FROM users 
            WHERE email = :email'
        );
        $req->execute(array("email" => $email, ));
        $resultat = $req->fetch();

        // Si aucun email trouvé, retourne false
        if (!$resultat) {
            return false;
        }

        // Vérification du mot de passe et validation de l'auth
        return password_verify($mdp, $resultat['mdp_securise']);

    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array('email' => $email, ));
        throw new Exception("Erreur lors de l'authentification");
    }
}

/**
 * Charge les données utilisateur depuis la base de données
 *
 * @param string $email l'utilisateur
 * @return array|null Les données utilisateur ou null si non trouvé
 * @throws Exception En cas d'erreur de base de données
 */
function connexionUtilisateur(string $email): array|null
{
    $co = connexionBaseDeDonnees();

    try {
        // Récupération des données utilisateur en base à partir de l'email
        $req = $co->prepare(
            'SELECT id, prenom, nom, DATE_FORMAT(created_at,"%d-%m-%Y") AS created_at, role
             FROM users
             WHERE email = :email'
        );
        $req->execute(array("email" => $email, ));
        $user = $req->fetch();

        // Si aucun utilisateur trouvé, retourne null
        if (!$user) {
            return null;
        }

        // Retourne les données utilisateurs
        return array(
            'id' => $user['id'],
            'email' => $email,
            'prenom' => $user['prenom'],
            'nom' => $user['nom'],
            'role' => $user['role'],
            'date_creation' => $user['created_at'],
        );

    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array('email' => $email, ));
        throw new Exception('Erreur lors du chargement des données utilisateur');
    }
}

/**
 * Vérifie si un utilisateur existe avec cet email
 * 
 * @param string $email Email à vérifier
 * @param int|null $excludeId Id de l'utilisateur à exclure (si fiche de mise à jour utilisateur)
 * @return bool True si un utilisateur avec cet e-mail existe déjà, faux sinon
 * @throws Exception Erreur de base de données
 */
function isUtilisateur(string $email, ?int $excludeId = null): bool
{
    $co = connexionBaseDeDonnees();

    try {
        // Renvoi 1 si l'utilisateur est existant en base de données
        $sql = "SELECT 1 FROM users WHERE email = :email";
        if ($excludeId) {
            $sql .= " AND id <> :id";
        }
        $req = $co->prepare($sql);
        $params = array('email' => $email, );
        if ($excludeId) {
            $params['id'] = $excludeId;
        }
        $req->execute($params);
        return (bool) $req->fetchColumn();  // Test d'existence, si 1 alors True, false sinon
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), ['email' => $email]);
        throw new Exception("Erreur lors de la vérification d'existence");
    }
}

// ---------------- CRUD utilisateurs ----------------

/**
 * Enregistre un nouvel utilisateur en base de données
 *
 * @param array $donnees Données utilisateur (email, mdp_securise, prenom, nom)
 * @return bool True si enregistrement réussi, false sinon
 * @throws Exception En cas d'erreur de base de données
 */
function enregistrerUtilisateur(array $donnees): bool
{
    // Validation des champs requis pour l'enregistrement
    $champsRequis = array('email', 'mdp_securise', 'prenom', 'nom', );
    foreach ($champsRequis as $champ) {
        if (empty($donnees[$champ])) {
            throw new InvalidArgumentException("Le champ '" . $champ . "' est requis");
        }
    }

    $co = connexionBaseDeDonnees();
    $sql = "
      INSERT INTO users (email, mdp_securise, prenom, nom)
      VALUES (:email, :mdp_securise, :prenom, :nom)
    ";

    try {
        // Insertion des données utilisateur dans la base de données
        $req = $co->prepare($sql);
        return $req->execute(array(
            "email" => $donnees["email"],
            "mdp_securise" => $donnees["mdp_securise"],
            "prenom" => $donnees["prenom"],
            "nom" => $donnees["nom"],
        ));
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array("email" => $donnees["email"], ));
        throw new Exception("Erreur lors de l'enregistrement de l'utilisateur");
    }
}

/**
 * Récupère la liste de tous les utilisateurs
 * 
 * @return array Liste des utilisateurs
 * @throws Exception En cas d'erreur de base de données
 */
function getListeUtilisateurs(): array
{
    $co = connexionBaseDeDonnees();
    $sql = "
    SELECT 
      id, 
      CONCAT(prenom,' ',nom) AS nom_complet, 
      email, 
      role,
      DATE_FORMAT(created_at,'%d-%m-%Y') AS date_creation,
      DATE_FORMAT(updated_at,'%d-%m-%Y') AS date_modification
    FROM users 
    ORDER BY id
    ";

    try {
        // Récupération de la liste des utilisateurs
        $req = $co->prepare($sql);
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage());
        throw new Exception('Erreur lors de la récupération des utilisateurs');
    }
}

/**
 * Récupère un utilisateur par son ID
 * 
 * @param int $id ID de l'utilisateur
 * @return array|null Données utilisateur ou null si non trouvé
 * @throws Exception En cas d'erreur de base de données
 */
function getUtilisateurParId(int $id): array
{
    $co = connexionBaseDeDonnees();

    try {
        // Récupère les données de l'utilisateur
        $req = $co->prepare('SELECT * FROM users WHERE id = :id');
        $req->execute(array('id' => $id, ));
        $result = $req->fetch(PDO::FETCH_ASSOC);

        // Renvoi null si aucune ligne n'a été trouvée
        return $result ? $result : null;
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array('id' => $id, ));
        throw new Exception("Erreur lors de la récupération de l'utilisateur");
    }
}

/**
 * Met à jour un utilisateur existant
 *
 * @param int $id ID de l’utilisateur
 * @param array $donnees Tableau associatif des champs à mettre à jour
 * @return bool True si la mise à jour a réussi, false sinon
 * @throws Exception En cas d'erreur de base de données
 */
function modifierUtilisateur(int $id, array $donnees): bool
{
    // Vérifie si les champs requis sont présents, sinon renvoie exception
    if (empty($donnees)) {
        throw new InvalidArgumentException('Aucun champ à mettre à jour');
    }

    $co = connexionBaseDeDonnees();

    try {
        // Vérifier que l'utilisateur existe
        if (!getUtilisateurParId($id)) {
            return false;
        }

        // Construction dynamique de la clause SET
        $sets = array();
        $params = array();
        foreach ($donnees as $key => $val) {
            $sets[] = $key . "=" . ":$key";
            $params[$key] = $val;
        }
        $params['id'] = $id;

        // Construction de la requête SQL avec implode  
        $sql = "
            UPDATE users 
            SET " . implode(', ', $sets) . " 
            WHERE id = :id
        ";

        $req = $co->prepare($sql);
        return $req->execute($params);
    } catch (PDOException $e) {
        // Log l’erreur en prod
        logErreur(__FUNCTION__, $e->getMessage(), array("id" => $id, ));
        throw new Exception("Erreur lors de la mise à jour de l'utilisateur");
    }
}

/**
 * Supprime un utilisateur par son id
 * 
 * @param int $id ID de l'utilisateur à supprimer
 * @return bool True si suppression réussie, false sinon
 * @throws Exception En cas d'erreur de base de données
 */
function supprimerUtilisateur(int $id): bool
{
    $co = connexionBaseDeDonnees();

    try {
        // Suppression de l'utilisateur
        $req = $co->prepare("DELETE FROM users WHERE id = :id");
        return $req->execute(array('id' => $id, ));
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array('id' => $id, ));
        throw new Exception("Erreur lors de la suppression de l'utilisateur");
    }
}

/**
 * Valide les données utilisateur pour create ou update.
 * @param array $data Données à valider
 * @param int|null $excludeId ID à exclure pour la vérification d'unicité
 * @return array Liste des erreurs (vide si OK)
 */
function validerDonneesUtilisateur(array $data, ?int $excludeId = null): array
{
    // Initialisation du tableau d'erreurs
    $erreurs = array();

    // Validation email
    try {
        if (empty($data['email']) || !estValideMail($data['email'])) {
            array_push($erreurs, 'Adresse e-mail invalide.');
        } elseif (isUtilisateur($data['email'], $excludeId)) {
            array_push($erreurs, 'Cet e-mail est déjà utilisé.');
        }
    } catch (Exception $e) {
        array_push($erreurs, 'Erreur en base de données. Merci de nous-contacter via la page contact.');
    }

    // Validation mot de passe
    if (!empty($data['motdepasse']) || $excludeId === null) {  // Si mot de passe est fourni ou si on est en update
        // si création (excludeId null) ou motdepasse renseigné en update
        if (empty($data['motdepasse']) || !estValideMotdepasse($data['motdepasse'])) {
            array_push($erreurs, 'Le mot de passe ne respecte pas les contraintes.');
        }
        if ($data['motdepasse'] !== ($data['confirm'] ?? '')) {
            array_push($erreurs, 'La confirmation du mot de passe ne correspond pas.');
        }
    }

    // Validation champs obligatoires
    if (empty($data['prenom'])) {
        array_push($erreurs, 'Le prénom est requis.');
    }
    if (empty($data['nom'])) {
        array_push($erreurs, 'Le nom est requis.');
    }

    return $erreurs;
}

//==================================================================================================
//                                           TEAM                 
//==================================================================================================

/**
 * Enregistre un nouvel entraineur en base de données
 *
 * @param array $donnees Données entraineur (prenom, nom, bio, photo)
 * @return bool True si enregistrement réussi, false sinon
 * @throws Exception En cas d'erreur de base de données
 */
function enregistrerEntraineur(array $donnees): bool
{

    // Validation des champs requis pour l'enregistrement
    $champsRequis = array('prenom', 'nom', 'bio', );
    foreach ($champsRequis as $champ) {
        if (empty($donnees[$champ])) {
            throw new InvalidArgumentException("Le champ '" . $champ . "' est requis");
        }
    }

    $co = connexionBaseDeDonnees();
    $sql = "
    INSERT INTO team (prenom, nom, bio, photo, created_at, updated_at)
    VALUES (:prenom, :nom, :bio, :photo, NOW(), NOW())
    ";

    try {
        // Insertion des données entraineur dans la base de données
        $req = $co->prepare($sql);
        return $req->execute(array(
            'prenom' => $donnees['prenom'],
            'nom' => $donnees['nom'],
            'bio' => $donnees['bio'],
            'photo' => $donnees['photo'] ?? null, // Null si url (nom) photo n'est pas fournie
        ));
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array("nom" => $donnees["nom"], "prenom" => $donnees["prenom"], ));
        throw new Exception("Erreur lors de l'enregistrement de l'entraineur");
    }
}

/**
 * Valide les données entraineur pour create ou update.
 * 
 * @param array $data Données à valider
 * @return array Liste des erreurs (vide si OK)
 */
function validerDonnesEntraineur(array $data): array
{
    // Initialisation du tableau d'erreurs
    $erreurs = array();

    // Validation champs obligatoires
    if (!isset($data['prenom']) || empty($data['prenom'])) {
        array_push($erreurs, 'Le prénom est requis.');
    }
    if (!isset($data['nom']) || empty($data['nom'])) {
        array_push($erreurs, 'Le nom est requis.');
    }
    if (!isset($data['bio']) || empty($data['bio'])) {
        array_push($erreurs, 'La biographie est requise.');
    }

    return $erreurs;
}

/**
 * Récupère un entraineur par son ID
 * 
 * @param int $id ID de l'entraineur
 * @return array|null Données entraineur ou null si non trouvé
 * @throws Exception En cas d'erreur de base de données
 */
function getEntraineurParId(int $id): array|null
{
    $co = connexionBaseDeDonnees();
    $sql = "
    SELECT id, prenom, nom, bio, photo, created_at, updated_at 
    FROM team 
    WHERE id = :id
    ";

    try {
        // Récupère les données de l'utilisateur
        $req = $co->prepare($sql);
        $req->execute(array('id' => $id, ));
        $result = $req->fetch(PDO::FETCH_ASSOC);

        // Renvoi null si aucune ligne n'a été trouvée
        return $result ? $result : null;
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array('id' => $id, ));
        throw new Exception("Erreur lors de la récupération de l'entraineur");
    }
}

/**
 * Récupère la liste de tous les entraineurs
 * 
 * @return array Liste des entraineurs
 * @throws Exception En cas d'erreur de base de données
 */
function getListeEntraineurs(): array
{
    $co = connexionBaseDeDonnees();
    $sql = "
    SELECT id,
        CONCAT(prenom,' ',nom) AS nom_complet,
        LEFT(bio,100) AS extrait_bio,
        bio,
        DATE_FORMAT(created_at,'%d-%m-%Y') AS date_creation,
        DATE_FORMAT(updated_at,'%d-%m-%Y') AS date_modification,
        photo
    FROM team
    ORDER BY id
    ";

    try {
        // Récupération de la liste des entraineurs
        $req = $co->prepare($sql);
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage());
        throw new Exception('Erreur lors de la récupération des entraineurs');
    }
}

/**
 * Met à jour un entraineur existant
 *
 * @param int $id ID de l'entraineur
 * @param array $donnees Tableau associatif des champs à mettre à jour
 * @return bool True si la mise à jour a réussi, false sinon
 * @throws Exception En cas d'erreur de base de données
 */
function modifierEntraineur(int $id, array $donnees): bool
{
    // Vérifie si les champs requis sont présents, sinon renvoie exception
    if (empty($donnees)) {
        throw new InvalidArgumentException('Aucun champ à mettre à jour');
    }

    $co = connexionBaseDeDonnees();

    try {
        // Vérifier que l'entraineur existe
        if (!getEntraineurParId($id)) {
            return false;
        }

        // Construction dynamique de la clause SET
        $sets = array();
        $params = array();
        foreach ($donnees as $key => $val) {
            $sets[] = $key . "=" . ":$key";
            $params[$key] = $val;
        }
        $params['id'] = $id;

        // Construction de la requête SQL avec implode  
        $sql = "
            UPDATE team 
            SET " . implode(', ', $sets) . " 
            WHERE id = :id
        ";

        $req = $co->prepare($sql);
        return $req->execute($params);
    } catch (PDOException $e) {
        // Log l’erreur en prod
        logErreur(__FUNCTION__, $e->getMessage(), array("id" => $id, ));
        throw new Exception("Erreur lors de la mise à jour de l'entraineur");
    }
}

/**
 * Supprime un entraineur par son id
 * 
 * @param int $id ID de l'entraineur à supprimer
 * @return bool True si suppression réussie, false sinon
 * @throws Exception En cas d'erreur de base de données
 */
function supprimerEntraineur(int $id): bool
{
    $co = connexionBaseDeDonnees();

    try {
        // Suppression de l'entraineur
        $req = $co->prepare("DELETE FROM team WHERE id = :id");
        return $req->execute(array('id' => $id, ));
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array('id' => $id, ));
        throw new Exception("Erreur lors de la suppression de l'entraineur");
    }
}

//==================================================================================================
//                                           COURS                 
//==================================================================================================

/**
 * Récupère un cours par son ID
 * 
 * @param int $id ID du cours
 * @return array|null Données cours ou null si non trouvé
 * @throws Exception En cas d'erreur de base de données
 */
function getCoursParId(int $id): array
{
    $co = connexionBaseDeDonnees();
    $sql = "
    SELECT id, nom, niveau, prix, description, team_id, date_creation, updated_at
    FROM classes
    WHERE id = :id
    ";

    try {
        // Récupère les données du cours
        $req = $co->prepare($sql);
        $req->execute(array('id' => $id, ));
        $result = $req->fetch(PDO::FETCH_ASSOC);

        // Renvoi null si aucune ligne n'a été trouvée
        return $result ? $result : null;
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array('id' => $id, ));
        throw new Exception("Erreur lors de la récupération du cours");
    }
}

/** 
 * Récupère la liste de tous les cours
 * 
 * @return array Liste des cours
 * @throws Exception En cas d'erreur de base de données
 */
function getListeCours(): array
{
    $co = connexionBaseDeDonnees();
    $sql = "
        SELECT
          c.id,
          c.nom,
          c.niveau,
          CONCAT(c.prix,' €') AS prix_aff,
          c.prix,
          LEFT(c.description,100) AS extrait_desc,
          CONCAT(t.prenom,' ',t.nom) AS entraineur,
          DATE_FORMAT(c.date_creation,'%d-%m-%Y') AS date_creation,
          DATE_FORMAT(c.updated_at,'%d-%m-%Y') AS date_modification
        FROM classes c
        LEFT JOIN team t ON t.id = c.team_id
        ORDER BY c.nom
    ";

    try {
        // Récupération de la liste des cours
        $req = $co->prepare($sql);
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage());
        throw new Exception('Erreur lors de la récupération des cours');
    }
}

/**
 * Récupère le planning des cours
 * 
 * @return array Liste des cours selon leurs horaires
 * @throws Exception En cas d'erreur de base de données
 */
function getCoursPlanning(): array
{
    $co = connexionBaseDeDonnees();
    $sql = "
    SELECT
      s.jour AS jour,
      s.heure_debut,
      s.heure_fin,
      c.nom,
      c.niveau
    FROM schedules AS s
    INNER JOIN classes AS c ON s.class_id = c.id
    ORDER BY s.jour 
    ";

    try {
        // Récupération du planning des cours
        $req = $co->prepare($sql);
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage());
        throw new Exception('Erreur lors de la récupération du planning des cours');
    }
}

/**
 * Valide les données d'un cours pour create ou update.
 * 
 * @param array $donnees Données à valider
 * @return array Liste des erreurs (vide si OK)
 */
function validerDonneesCours(array $donnees): array
{
    $erreurs = array();

    // Validation des champs requis
    $champsRequis = ['nom', 'niveau', 'description'];
    foreach ($champsRequis as $champ) {
        if (empty($donnees[$champ])) {
            array_push($erreurs, "Le champ '" . $champ . "' est requis.");
        }
    }

    if (!isset($donnees['prix']) || !is_numeric($donnees['prix']) || $donnees['prix'] < 0) {
        array_push($erreurs, 'Le prix doit être un nombre positif.');
    }

    // Validation du format de l'ID entraineur
    if (empty($donnees['team_id']) || !ctype_digit((string) $donnees['team_id'])) {
        array_push($erreurs, 'Un entraineur est requise.');
    } else {
        // Vérification de l’entraîneur ID
        if (getEntraineurParId((int) $donnees['team_id']) == null) {
            array_push($erreurs, 'L\'entraineur n\'existe pas.');
        }
    }

    return $erreurs;
}

/**
 * Enregistre un nouveau cours en base de données
 *
 * @param array $donnees Données cours (nom, niveau, description, prix)
 * @return bool True si enregistrement réussi, false sinon
 * @throws Exception En cas d'erreur de base de données
 */
function enregistrerCours(array $donnees): bool
{
    // Validation des champs requis pour l'enregistrement
    $champsRequis = array('nom', 'niveau', 'prix', 'description', 'team_id', );
    foreach ($champsRequis as $champ) {
        if (empty($donnees[$champ])) {
            throw new InvalidArgumentException("Le champ '" . $champ . "' est requis");
        }
    }

    $co = connexionBaseDeDonnees();
    $sql = "
    INSERT INTO classes (nom, niveau, prix, description, team_id, date_creation, updated_at)
    VALUES (:nom, :niveau, :prix, :description, :team_id, NOW(), NOW())
    ";

    try {
        // Insertion des données entraineur dans la base de données
        $req = $co->prepare($sql);
        return $req->execute(array(
            'nom' => $donnees['nom'],
            'niveau' => $donnees['niveau'],
            'prix' => $donnees['prix'],
            'description' => $donnees['description'],
            'team_id' => $donnees['team_id'],
        ));
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array(
            "nom" => $donnees["nom"],
            "niveau" => $donnees["niveau"],
            "prix" => $donnees["prix"],
        ));
        throw new Exception("Erreur lors de l'enregistrement du cours");
    }
}

/**
 * Met à jour un cours existant.
 * 
 * @param int $id ID du cours
 * @param array $donnees Tableau associatif des champs à mettre à jour
 * @return bool True si la mise à jour a réussi, false sinon
 * @throws Exception En cas d'erreur de base de données
 */
function modifierClasse(int $id, array $donnees): bool
{
    // Vérifie si les champs requis sont présents, sinon renvoie exception
    if (empty($donnees)) {
        throw new InvalidArgumentException('Aucun champ à mettre à jour');
    }

    $co = connexionBaseDeDonnees();

    try {
        // Vérifier que le cours existe
        if (!getCoursParId($id)) {
            return false;
        }

        // Construction dynamique de la clause SET
        $sets = array();
        $params = array();
        foreach ($donnees as $key => $val) {
            $sets[] = $key . "=" . ":$key";
            $params[$key] = $val;
        }
        $params['id'] = $id;

        // Construction de la requête SQL avec implode  
        $sql = "
            UPDATE classes
            SET " . implode(',', $sets) . ", updated_at = NOW()
            WHERE id = :id
        ";

        $req = $co->prepare($sql);
        return $req->execute($params);
    } catch (PDOException $e) {
        // Log l’erreur en prod
        logErreur(__FUNCTION__, $e->getMessage(), array("id" => $id, ));
        throw new Exception("Erreur lors de la mise à jour du cours");
    }
}

/**
 * Supprime un cours par id
 * 
 * @param int $id ID du cours à supprimer
 * @return bool True si suppression réussie, false sinon
 * @throws Exception En cas d'erreur de base de données
 */
function supprimerCours(int $id): bool
{
    $co = connexionBaseDeDonnees();

    try {
        // Suppression de l'entraineur
        $req = $co->prepare("DELETE FROM classes WHERE id = :id");
        return $req->execute(array('id' => $id, ));
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array('id' => $id, ));
        throw new Exception("Erreur lors de la suppression du cours");
    }
}

//==================================================================================================
//                                           MESSAGES                 
//==================================================================================================

/**
 * Valide les données d'un message.
 * 
 * @param array $donnees Données à valider
 * @return array Liste des erreurs (vide si OK)
 */
function validerDonneesMessage(array $donnees): array
{
    $erreurs = array();

    // Validation des champs requis
    $champsRequis = array('nom', 'message',);
    foreach ($champsRequis as $champ) {
        if (empty($donnees[$champ])) {
            array_push($erreurs, "Le champ '" . $champ . "' est requis.");
        }
    }

    if (empty($donnees['email']) || !estValideMail($donnees['email'])) {
        array_push($erreurs, "Adresse e-mail invalide.");
    }

    return $erreurs;
}

/**
 * Récupère la liste de tous les messages, non-lu en premier
 * 
 * @return array Liste des cours
 * @throws Exception En cas d'erreur de base de données
 */
function getListeMessages(): array
{
    $co = connexionBaseDeDonnees();
    // Requête dynamique prenant en compte l'état (lu ou non) du message
    $sql = "
    SELECT id, 
        nom, 
        email, 
        sujet,
        DATE_FORMAT(created_at,'%d-%m-%Y') AS date_sent,
        is_read
    FROM messages
    ";
    if (!empty($_GET['unread'])) {
        $sql .= " WHERE is_read = 0";
    }
    $sql .= " ORDER BY is_read ASC, date_sent DESC";

    try {
        // Récupération de la liste des messages
        $req = $co->prepare($sql);
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage());
        throw new Exception('Erreur lors de la récupération des messages');
    }
}

/**
 * Récupère un message par son ID.
 * 
 * @param int $id ID du message
 * @return array|null Données message ou null si non trouvé
 * @throws Exception En cas d'erreur de base de données
 */
function getMessageParId(int $id): array
{
    $co = connexionBaseDeDonnees();
    $sql = "
        SELECT
          id,
          nom,
          email,
          sujet,
          contenu,
          is_read,
          DATE_FORMAT(created_at, '%d-%m-%Y %H:%i') AS date_sent
        FROM messages
        WHERE id = :id
    ";

    try {
        // Récupère les données du message
        $req = $co->prepare($sql);
        $req->execute(array('id' => $id, ));
        $result = $req->fetch(PDO::FETCH_ASSOC);

        // Renvoi null si aucune ligne n'a été trouvée
        return $result ? $result : null;
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array('id' => $id, ));
        throw new Exception("Erreur lors de la récupération du message");
    }
}

/**
 * Marque un message comme lu.
 * 
 * @param int $id ID du message lu
 * @return bool True si la mise à jour a réussi, false sinon
 * @throws Exception En cas d'erreur de base de données
 */
function setMessageLu(int $id): bool
{
    $co = connexionBaseDeDonnees();

    try {
        // Récupère les données du cours
        $req = $co->prepare("UPDATE messages SET is_read = 1 WHERE id = :id");
        $req->execute(array('id' => $id, ));
        return $req->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array('id' => $id, ));
        throw new Exception("Erreur lors de la récupération du maj statut du message");
    }
}

/**
 * Enregistre un nouveau message en base de données
 *
 * @param array $donnees Données du message (nom, email, contenu, sujet)
 * @return bool True si enregistrement réussi, false sinon
 * @throws Exception En cas d'erreur de base de données
 */
function enregistrerMessage(array $donnees): bool
{
    $co = connexionBaseDeDonnees();
    $sql = "
        INSERT INTO messages (nom, email, contenu, sujet)
        VALUES (:nom, :email, :contenu, :sujet)
    ";
    try {
        // Insertion des données du message dans la base de données
        $req = $co->prepare($sql);
        return $req->execute([
            ':nom' => $donnees['nom'],
            ':email' => $donnees['email'],
            ':contenu' => $donnees['message'],
            ':sujet' => $donnees['sujet'] ?? null,
        ]);
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array(
            "nom" => $donnees["nom"],
            "email" => $donnees["email"],
            "sujet" => $donnees["sujet"],
        ));
        throw new Exception("Erreur lors de l'enregistrement du message");
    }
}

/**
 * Supprime un message par id
 * 
 * @param int $id ID du message à supprimer
 * @return bool True si suppression réussie, false sinon
 * @throws Exception En cas d'erreur de base de données
 */
function supprimerMessage(int $id): bool
{
    $co = connexionBaseDeDonnees();

    try {
        // Suppression de l'entraineur
        $req = $co->prepare("DELETE FROM messages WHERE id = :id");
        return $req->execute(array('id' => $id, ));
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array('id' => $id, ));
        throw new Exception("Erreur lors de la suppression du message");
    }
}

//==================================================================================================
//                                  ACTU / ARTICLE / POST / TAGS                
//==================================================================================================

/**
 * Valide les données d'un post pour create ou update.
 * 
 * TODO: Ajouter la vérif auteur
 * @param array $donnees Données à valider
 * @return array Liste des erreurs (vide si OK)
 */
function validerDonneesPost(array $donnees): array
{

    $erreurs = array();

    // Validation des champs requis
    $champsRequis = array('titre', 'contenu', 'auteur',);
    foreach ($champsRequis as $champ) {
        if (empty($donnees[$champ])) {
            array_push($erreurs, "Le champ '" . $champ . "' est requis.");
        }
    }

    if (!is_numeric($donnees['auteur'])) {
        array_push($erreurs, "Auteur invalide");
    }

    return $erreurs;
}

/** 
 * Récupère la liste des auteurs
 * 
 * @return array Liste des auteurs
 * @throws Exception En cas d'erreur de base de données
 */
function getListeAuteurs(): array
{
    $co = connexionBaseDeDonnees();
    $sql = "
    SELECT id, CONCAT(prenom,' ',nom) AS nom_complet 
    FROM users 
    WHERE role='admin'
    ";

    try {
        // Récupération des auteurs
        $req = $co->prepare($sql);
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage());
        throw new Exception('Erreur lors de la récupération des auteurs');
    }
}

/**
 * Enregistre un nouveau cours en base de données
 *
 * @param array $donnees Données cours (nom, niveau, description, prix)
 * @return int Si succès, retourne Id du post enregistré, -1 sinon
 * @throws Exception En cas d'erreur de base de données
 */
function enregistrerPost(array $donnees): int
{
    $co = connexionBaseDeDonnees();
    $sql = "
    INSERT INTO posts (titre, contenu, auteur, photo)
    VALUES (:titre,:contenu,:auteur, :photo)
    ";

    try {
        // Insertion des données du post dans la base de données
        $req = $co->prepare($sql);

        // Insère les données, si succès alors retourne l'id du post sinon retourne -1
        return $req->execute(array(
            'titre' => $donnees['titre'],
            'contenu' => $donnees['contenu'],
            'auteur' => $donnees['auteur'],
            'photo' => $donnees['photo'] ?? null,
        )) ? $co->lastInsertId() : -1;
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array("titre" => $donnees["titre"], "auteur" => $donnees["auteur"], ));
        throw new Exception("Erreur lors de l'enregistrement du cours");
    }
}

/**
 * Met à jour un post existant.
 * 
 * @param int $id ID du cours
 * @param array $donnees Tableau associatif des champs à mettre à jour
 * @return bool True si la mise à jour a réussi, false sinon
 * @throws Exception En cas d'erreur de base de données
 */
function modifierPost(int $id, array $donnees): bool
{
    // Vérifie si les champs requis sont présents, sinon renvoie exception
    if (empty($donnees)) {
        throw new InvalidArgumentException('Aucun champ à mettre à jour');
    }

    $co = connexionBaseDeDonnees();

    try {
        // Vérifier que le post existe
        if (!getPostParId($id)) {
            return false;
        }

        // Construction dynamique de la clause SET
        $sets = array();
        $params = array();
        foreach ($donnees as $key => $val) {
            $sets[] = $key . "=" . ":$key";
            $params[$key] = $val;
        }
        $params['id'] = $id;

        // Construction de la requête SQL avec implode  
        $sql = "
            UPDATE posts
            SET " . implode(',', $sets) . ", updated_at = NOW()
            WHERE id = :id
        ";

        $req = $co->prepare($sql);
        return $req->execute($params);
    } catch (PDOException $e) {
        // Log l’erreur en prod
        logErreur(__FUNCTION__, $e->getMessage(), array("id" => $id, ));
        throw new Exception("Erreur lors de la mise à jour du post");
    }

}

/**
 * Supprime un post.
 * 
 * @param int $id ID du post à supprimer
 * @return bool True si suppression réussie, false sinon
 * @throws Exception En cas d'erreur de base de données
 */
function supprimerPost(int $id): bool
{
    $co = connexionBaseDeDonnees();

    try {
        // Suppression de les tags liés au post
        $req_tags = $co->prepare("DELETE FROM post_tag WHERE post_id = :id");
        $req_tags->execute(array('id' => $id, ));

        $req_post = $co->prepare("DELETE FROM posts WHERE id = :id");
        $req_post->execute(array('id' => $id, ));

        return true;
    } catch (PDOException $e) {
        // Si la transaction de suppression est en cours, donc en échec, alors on annule la transaction (rollback)
        if ($co->inTransaction()) {
            $co->rollBack();
        }
        logErreur(__FUNCTION__, $e->getMessage(), array('id' => $id, ));
        throw new Exception("Erreur lors de la suppression du post et des tags");
    }
}

/**
 * Récupère un post par son ID
 * 
 * @param int $id ID du post
 * @return array|null Données post ou null si non trouvé
 * @throws Exception En cas d'erreur de base de données
 */
function getPostParId(int $id): array
{
    $co = connexionBaseDeDonnees();
    $sql = "
    SELECT
      p.id,
      p.titre,
      p.contenu,
      p.photo,
      p.auteur,
      CONCAT(u.prenom,' ',u.nom) AS auteur_nom,
      DATE_FORMAT(p.created_at,'%d-%m-%Y') AS date_publication
    FROM posts AS p
    JOIN users AS u ON u.id = p.auteur
    WHERE p.id = :id
    ";

    try {
        // Récupère les données du post
        $req = $co->prepare($sql);
        $req->execute(array('id' => $id, ));
        $result = $req->fetch(PDO::FETCH_ASSOC);

        // Renvoi null si aucune ligne n'a été trouvée
        return $result ? $result : null;
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array('id' => $id, ));
        throw new Exception("Erreur lors de la récupération du post");
    }
}

/**
 * Récupère les posts, avec extrait, auteur, date, et liste de tags.
 * Peut filtrer par tag et trier par date.
 *
 * @param int $excerptLength  longueur de l’extrait
 * @param string|null $filterTag nom du tag à filtrer (ou null pour aucun filtre)
 * @param string $sort 'asc' ou 'desc' (par date de création)
 * @return array Tableau de posts (id, titre, photo, excerpt, auteur_nom, created_at, updated_at, tags)
 * @throws Exception En cas d'erreur de base de données
 */
function getListePosts(int $excerptLength = 200, ?string $filterTag = null, string $sort = 'desc'): array
{
    $co = connexionBaseDeDonnees();
    $sort = strtolower($sort) === 'asc' ? 'ASC' : 'DESC';
    $params = array("len" => $excerptLength, );
    $sql = "
    SELECT
        p.id,
        p.titre,
        p.photo,
        LEFT(p.contenu, :len) AS excerpt,
        CONCAT(u.prenom,' ',u.nom) AS auteur_nom,
        DATE_FORMAT(p.created_at,'%d-%m-%Y') AS created_at,
        DATE_FORMAT(p.updated_at,'%d-%m-%Y') AS updated_at,
        -- concaténation des tags
        (SELECT GROUP_CONCAT(t2.name SEPARATOR ', ')
        FROM post_tag pt2
        JOIN tags t2 ON t2.id = pt2.tag_id
        WHERE pt2.post_id = p.id
        ) AS tags 
    FROM posts p
    JOIN users u ON u.id = p.auteur
    ";

    if ($filterTag) {
        // filtre par tag
        $sql .= "
        JOIN post_tag pt ON pt.post_id = p.id
        JOIN tags t ON t.id = pt.tag_id
        WHERE t.name = :tag
        ";
        $params['tag'] = $filterTag;
    }

    $sql .= " GROUP BY p.id ORDER BY p.created_at " . $sort;

    try {
        // Récupération de la liste des cours
        $req = $co->prepare($sql);
        $req->execute($params);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage());
        throw new Exception('Erreur lors de la récupération des posts');
    }

}

/**
 * Synchronise la liste des tags pour un post donné.
 *
 * @param int $postId ID du post
 * @param string $tagsCsv Chaîne CSV des noms de tags
 */
function syncPostTags(int $postId, string $tagsCsv): void
{
    $co = connexionBaseDeDonnees();

    // Séparer, normaliser et dédupliquer
    $names = array_filter(array_map('trim', explode(',', $tagsCsv)));
    $names = array_unique(array_map('mb_strtolower', $names));

    if (count($names) === 0) {
        // supprimer toutes les liaisons existantes
        $co->prepare("DELETE FROM post_tag WHERE post_id = ?")
            ->execute([$postId]);
        return;
    }

    // 2) Insérer les nouveaux tags (IGNORE les doublons)
    $iStmt = $co->prepare("INSERT IGNORE INTO tags (name) VALUES (:name)");
    foreach ($names as $n) {
        $iStmt->execute(['name' => $n]);
    }

    // 3) Récupérer leurs IDs
    $placeholders = implode(',', array_fill(0, count($names), '?'));
    $sStmt = $co->prepare("SELECT id FROM tags WHERE name IN ($placeholders)");
    $sStmt->execute($names);
    $tagIds = $sStmt->fetchAll(PDO::FETCH_COLUMN);

    // 4) Réinitialiser les liaisons, puis recréer
    $co->prepare("DELETE FROM post_tag WHERE post_id = ?")
        ->execute([$postId]);

    $ptStmt = $co->prepare("INSERT INTO post_tag (post_id, tag_id) VALUES (?, ?)");
    foreach ($tagIds as $tid) {
        $ptStmt->execute([$postId, $tid]);
    }
}

/**
 * Retourne la liste des tags associés à un post.
 * 
 * @param int $id ID du post
 * @return array Liste des cours
 * @throws Exception En cas d'erreur de base de données
 */
function getTagsPourPost(int $id): array
{
    $co = connexionBaseDeDonnees();
    $sql = "
    SELECT
        t.name
    FROM tags t
    JOIN post_tag pt ON pt.tag_id = t.id
    WHERE pt.post_id = :id
    ";

    try {
        // Récupération de la liste des cours
        $req = $co->prepare($sql);
        $req->execute(array('id' => $id, ));
        return $req->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage(), array('id' => $id, ));
        throw new Exception("Erreur lors de la récupération des tags pour le post");
    }
}

/**
 * Récupère tous les tags existants, ordonnés alphabetiquement.
 *
 * @return array Liste des tags
 * @throws Exception En cas d'erreur de base de données
 */
function getListeTags()
{
    $co = connexionBaseDeDonnees();
    $sql = "SELECT id, name FROM tags ORDER BY name";
    try {
        // Récupération de la liste des tags
        $req = $co->prepare($sql);
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logErreur(__FUNCTION__, $e->getMessage());
        throw new Exception('Erreur lors de la récupération des tags');
    }
}

//==================================================================================================
//                                        MESSAGES FLASHS                 
//==================================================================================================

/**
 * Affiche les messages flash et les supprime de la session
 * 
 * @return void
 */
function displayFlash(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }

    // Classes Tailwind pour les types de messages
    $typesClasses = [
        'success' => 'bg-green-100 text-green-800 border-green-200',
        'error' => 'bg-red-100 text-red-800 border-red-200',
        'warning' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'info' => 'bg-blue-100 text-blue-800 border-blue-200',
    ];

    // Boucle d'affichage des messages flash
    foreach ($_SESSION['flash'] as $type => $messages) {

        // Raccourci PHP permettant la meme opération :   $classes = $$typesClasses[$type] ?? $typesClasses['info'];
        $classes = isset($typesClasses[$type]) && !empty($typesClasses[$type]) ? $typesClasses[$type] : $typesClasses['info'];

        echo '<div class="mb-4 p-4 border rounded ' . $classes . '">';
        echo '<ul class="list-disc pl-5">';

        foreach ($messages as $msg) {
            echo '<li>' . $msg . '</li>';
        }

        echo '</ul></div>';
    }

    unset($_SESSION['flash']);
}

/**
 * Définit un flash message (success|error|warning|info).
 * 
 * @param string $type Type du message (success, error, warning, info)
 * @param string $msg Message à afficher
 * @return void
 */
function setFlash(string $type, string $msg): void
{
    $typesValides = ['success', 'error', 'warning', 'info'];

    // Si le type de message n'est pas valide, on lève une exception
    if (!in_array($type, $typesValides, true)) {
        throw new InvalidArgumentException("Type '" . $type . "' invalide");
    }

    $_SESSION['flash'][$type][] = htmlspecialchars($msg, ENT_QUOTES);
}

//==================================================================================================
//                                           AUTRES                 
//==================================================================================================

/**
 * Redirige vers la page de connexion si non connecté
 * 
 * @return void
 */
function requireConnexion(): void
{
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Nettoie un tableau de données en supprimant les espaces et balises HTML
 *
 * @param array $data Tableau associatif des champs à nettoyer
 * @return array Tableau nettoyé
 */
function nettoyerDonnees(array $data): array
{
    // Utilisation de array_map pour appliquer une fonction sur chaque élément d'un tableau
    // Utilisation d'une 'arrow function' permettant d'alléger la syntaxte du callback pour le mapping

    return array_map(function ($value) {
        return is_string($value) ? strip_tags(trim($value)) : $value;
    }, $data);
}

/**
 * Paginer un tableau en mémoire.
 *
 * @param array  $all    Le tableau complet de lignes.
 * @param string $param  Le nom du paramètre GET à lire pour la page courante (ex. 'p').
 * @param int    $perPage Nombre d’éléments par page.
 * @return array [ 'page' => int, 'perPage'=>int, 'total'=>int, 'totalPages'=>int, 'offset'=>int, 'slice'=>array ]
 */
function paginateArray(array $all, string $param, int $perPage = 10): array
{
    $total = count($all);
    $totalPages = (int) ceil($total / $perPage);
    $pageNum = isset($_GET[$param]) ? max(1, (int) $_GET[$param]) : 1;
    if ($pageNum > $totalPages)
        $pageNum = $totalPages;
    $offset = ($pageNum - 1) * $perPage;
    $slice = array_slice($all, $offset, $perPage);
    return compact('pageNum', 'perPage', 'total', 'totalPages', 'offset', 'slice');
}

/**
 * Renvoie la liste des témoignages.
 * Pour l'instant, cette fonction renvoi une liste statique. Voir la documentation pour plus d'informations.
 *  
 * @return array Liste des témoignages
 */
function getTemoignages(): array
{
    // TODO: Ajouter table et requête pour récupérer les avis/témoignages

    return array(
        [
            'quote' => 'Le club est exceptionnel, les coachs sont très professionnels.',
            'name' => 'Sophie Dubois',
            'role' => 'Mère de famille',
        ],
        [
            'quote' => 'Je progresse rapidement grâce aux entraînements adaptés.',
            'name' => 'Antoine Martin',
            'role' => 'Étudiant',
        ],
        [
            'quote' => 'Ambiance conviviale et sportive, je recommande à tous.',
            'name' => 'Julien Lefèvre',
            'role' => 'Salarié',
        ],
    );
}