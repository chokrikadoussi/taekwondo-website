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
      INSERT INTO users (email, mdp_securise, prenom, nom)
      VALUES (:email, :mdp_securise, :prenom, :nom)
    ";

    $req = $co->prepare($sql);

    try {
        return $req->execute(array(
            "email" => $donnees["email"],
            "mdp_securise" => $donnees["mdp_securise"],
            "prenom" => $donnees["prenom"],
            "nom" => $donnees["nom"],
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
        $params[$col] = $val;
    }
    $params['id'] = $id;

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

/**
 * Renvoie l’utilisateur (assoc) ou [] si non trouvé.
 */
function getUserById(int $id): array
{
    $pdo = connexionBaseDeDonnees();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return (array) $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Valide les données utilisateur pour create ou update.
 * @param array      $data      Nettoyé par nettoyerDonnees()
 * @param int|null   $excludeId Si non-null, exclut cet id pour l’unicité email
 * @return string[]  Liste d’erreurs (vide si OK)
 */
function validateUserData(array $data, ?int $excludeId = null): array
{
    $errors = [];

    if (empty($data['email']) || !estValideMail($data['email'])) {
        $errors[] = 'Adresse e-mail invalide.';
    } else {
        // unicité
        $pdo = connexionBaseDeDonnees();
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        if ($excludeId) {
            $sql .= " AND id <> :id";
        }
        $stmt = $pdo->prepare($sql);
        $params = ['email' => $data['email']];
        if ($excludeId) {
            $params['id'] = $excludeId;
        }
        $stmt->execute($params);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Cet e-mail est déjà utilisé.';
        }
    }

    if (!empty($data['motdepasse']) || $excludeId === null) {
        // si création (excludeId null) ou motdepasse renseigné en update
        if (empty($data['motdepasse']) || !estValideMotdepasse($data['motdepasse'])) {
            $errors[] = 'Le mot de passe ne respecte pas les contraintes.';
        }
        if ($data['motdepasse'] !== ($data['confirm'] ?? '')) {
            $errors[] = 'La confirmation du mot de passe ne correspond pas.';
        }
    }

    if (empty($data['prenom'])) {
        $errors[] = 'Le prénom est requis.';
    }
    if (empty($data['nom'])) {
        $errors[] = 'Le nom est requis.';
    }

    return $errors;
}

/**
 * Redirige vers la page du profile Admin correspondante.
 * @param string $page Page de destination
 */
function redirectToProfile(string $page): void
{
    header("Location: profile.php?page=" . $page);
    exit;
}

/**
 * Définit un flash message (success|error).
 */
function setFlash(string $type, string $msg): void
{
    $_SESSION['flash'][$type][] = $msg;
}

/**
 * Affiche puis vide les flash messages.
 */
function displayFlash(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    foreach ($_SESSION['flash'] as $type => $messages) {
        $bg = $type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        echo "<div class=\"mb-4 p-4 $bg rounded\">";
        echo '<ul class="list-disc pl-5">';
        foreach ($messages as $m) {
            echo '<li>' . htmlspecialchars($m, ENT_QUOTES) . '</li>';
        }
        echo '</ul></div>';
    }
    unset($_SESSION['flash']);
}

/**
 * Récupère un entraîneur par son ID.
 * @param int $id
 * @return array
 */
function getTrainerById(int $id): array
{
    $pdo = connexionBaseDeDonnees();
    $stmt = $pdo->prepare("SELECT id, prenom, nom, bio, photo, created_at, updated_at FROM team WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Valide les données d’un entraîneur en création ou édition.
 * @param array $data
 * @param ?int $excludeId
 * @return array
 */
function validateTrainerData(array $data, ?int $excludeId = null): array
{
    $errors = [];
    if (empty($data['prenom'])) {
        $errors[] = 'Le prénom est requis.';
    }
    if (empty($data['nom'])) {
        $errors[] = 'Le nom est requis.';
    }
    if (empty($data['bio'])) {
        $errors[] = 'La biographie est requise.';
    }
    // (Optionnel : valider la taille de bio, le format de photo, etc.)
    return $errors;
}

/**
 * Crée un nouvel entraîneur.
 * @param array $data
 * @return bool 
 */
function enregistrerEntraineur(array $data): bool
{
    $pdo = connexionBaseDeDonnees();
    $stmt = $pdo->prepare(
        "INSERT INTO team (prenom, nom, bio, photo, created_at, updated_at)
         VALUES (:prenom, :nom, :bio, :photo, NOW(), NOW())"
    );
    return $stmt->execute([
        'prenom' => $data['prenom'],
        'nom' => $data['nom'],
        'bio' => $data['bio'],
        'photo' => $data['photo'] ?? null,
    ]);
}

/**
 * Met à jour un entraîneur existant.
 */
function modifierEntraineur(int $id, array $fields): bool
{
    $pdo = connexionBaseDeDonnees();
    $sets = [];
    $params = [];
    foreach ($fields as $col => $val) {
        $sets[] = "$col = :$col";
        $params[$col] = $val;
    }
    $params['id'] = $id;
    $sql = "UPDATE team SET " . implode(',', $sets) . ", updated_at = NOW() WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Supprime un entraîneur.
 */
function deleteEntraineur(int $id): bool
{
    $pdo = connexionBaseDeDonnees();
    $stmt = $pdo->prepare("DELETE FROM team WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Récupère un cours par son ID.
 */
function getClasseById(int $id): array
{
    $pdo = connexionBaseDeDonnees();
    $stmt = $pdo->prepare("
        SELECT id, nom, niveau, prix, description, team_id, date_creation, updated_at
        FROM classes
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Valide les données d’un cours.
 */
function validateClasseData(array $data, ?int $excludeId = null): array
{
    $errors = [];
    if (empty($data['nom'])) {
        $errors[] = 'Le nom du cours est requis.';
    }
    if (empty($data['niveau'])) {
        $errors[] = 'Le niveau est requis.';
    }
    if (!isset($data['prix']) || !is_numeric($data['prix']) || $data['prix'] < 0) {
        $errors[] = 'Le prix doit être un nombre positif.';
    }
    if (empty($data['description'])) {
        $errors[] = 'La description est requise.';
    }
    // **Nouvelle validation team_id**
    if (empty($data['team_id']) || !ctype_digit((string) $data['team_id'])) {
        $errors[] = "L'entraîneur est requis.";
    } else {
        // vérifier que l’entraîneur existe bien
        $pdo = connexionBaseDeDonnees();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM team WHERE id = ?");
        $stmt->execute([(int) $data['team_id']]);
        if ($stmt->fetchColumn() == 0) {
            $errors[] = "Entraîneur invalide.";
        }
    }
    return $errors;
}

/**
 * Crée un nouveau cours.
 */
function enregistrerClasse(array $data): bool
{
    $pdo = connexionBaseDeDonnees();
    $stmt = $pdo->prepare("
        INSERT INTO classes (nom, niveau, prix, description, date_creation, updated_at)
        VALUES (:nom, :niveau, :prix, :description, NOW(), NOW())
    ");
    return $stmt->execute([
        'nom' => $data['nom'],
        'niveau' => $data['niveau'],
        'prix' => $data['prix'],
        'description' => $data['description'],
    ]);
}

/**
 * Met à jour un cours existant.
 */
function modifierClasse(int $id, array $fields): bool
{
    $pdo = connexionBaseDeDonnees();
    $sets = [];
    $params = [];
    foreach ($fields as $col => $val) {
        $sets[] = "$col = :$col";
        $params[$col] = $val;
    }
    $params['id'] = $id;
    $sql = "
        UPDATE classes
        SET " . implode(',', $sets) . ", updated_at = NOW()
        WHERE id = :id
    ";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Supprime un cours.
 */
function deleteClasse(int $id): bool
{
    $pdo = connexionBaseDeDonnees();
    $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Récupère tous les messages, triés par non lus d’abord.
 */
function getAllMessages(array $opts = [])
{
    $pdo = connexionBaseDeDonnees();
    $sql = "
    SELECT id, nom, email, sujet,
    DATE_FORMAT(created_at,'%d-%m-%Y') AS date_sent,
    is_read
    FROM messages";
    $params = [];
    if (!empty($_GET['unread'])) {
        $sql .= " WHERE is_read = 0";
    }
    $sql .= " ORDER BY created_at DESC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère un message par son ID.
 */
function getMessageById(int $id): array
{
    $pdo = connexionBaseDeDonnees();
    $stmt = $pdo->prepare("
        SELECT
          id,
          nom,
          email,
          sujet,
          contenu,
          is_read,
          DATE_FORMAT(created_at, '%d-%m-%Y %H:%i') AS date_sent
        FROM messages
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Marque un message comme lu.
 */
function markMessageRead(int $id): bool
{
    $pdo = connexionBaseDeDonnees();
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Supprime un message.
 */
function deleteMessage(int $id): bool
{
    $pdo = connexionBaseDeDonnees();
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Valide title/content pour un post.
 */
function validatePostData(array $data, ?int $excludeId = null): array
{
    $errors = [];
    if (empty($data['titre'])) {
        $errors[] = 'Le titre est requis.';
    }
    if (empty($data['contenu']) || mb_strlen($data['contenu']) < 20) {
        $errors[] = 'Le contenu est trop court (min. 20 chars).';
    }
    if (empty($data['auteur']) || !is_numeric($data['auteur'])) {
        $errors[] = 'Auteur invalide.';
    }
    return $errors;
}

/** 
 * Retourne la liste des auteurs possibles (ex : tous les admins/parents).
 */
function getAllAuthors(): array
{
    $pdo = connexionBaseDeDonnees();
    return $pdo
        ->query("SELECT id, CONCAT(prenom,' ',nom) AS nom_complet FROM users WHERE role='admin'")
        ->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Insère un nouvel article.
 */
function enregistrerPost(array $d)
{
    $pdo = connexionBaseDeDonnees();
    $stmt = $pdo->prepare("
    INSERT INTO posts (titre, contenu, auteur)
    VALUES (:titre,:contenu,:auteur)
  ");
    $stmt->execute([
        'titre' => $d['titre'],
        'contenu' => $d['contenu'],
        'auteur' => $d['auteur'],
    ]);
    return (int) $pdo->lastInsertId();
}

/**
 * Met à jour un article existant.
 */
function modifierPost(int $id, array $d): bool
{
    $pdo = connexionBaseDeDonnees();
    $stmt = $pdo->prepare("
    UPDATE posts
    SET titre = :titre,
        contenu = :contenu,
        auteur = :auteur,
        updated_at = NOW()
    WHERE id = :id
  ");
    return $stmt->execute([
        'titre' => $d['titre'],
        'contenu' => $d['contenu'],
        'auteur' => $d['auteur'],
        'id' => $id,
    ]);
}

/**
 * Supprime un article.
 */
function deletePost(int $id)
{
    $pdo = connexionBaseDeDonnees();
    $pdo->prepare("DELETE FROM post_tag WHERE post_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM posts     WHERE id       = ?")->execute([$id]);
}

/**
 * Récupère un article.
 */
function getPostById(int $id): array
{
    $pdo = connexionBaseDeDonnees();
    $stmt = $pdo->prepare("
    SELECT
      p.id,
      p.titre,
      p.contenu,
      p.auteur,
      CONCAT(u.prenom,' ',u.nom) AS auteur_nom,
      DATE_FORMAT(p.created_at,'%d-%m-%Y') AS date_publication
    FROM posts AS p
    JOIN users AS u ON u.id = p.auteur
    WHERE p.id = ?
  ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Récupère les posts, avec extrait, auteur, date, et liste de tags.
 * Peut filtrer par tag et trier par date.
 *
 * @param int         $excerptLength  longueur de l’extrait
 * @param string|null $filterTag      nom du tag à filtrer (ou null pour aucun filtre)
 * @param string      $sort           'asc' ou 'desc' (par date de création)
 * @return array
 */
function getAllPosts(int $excerptLength = 200, ?string $filterTag = null, string $sort = 'desc'): array
{
    $pdo = connexionBaseDeDonnees();
    $sort = strtolower($sort) === 'asc' ? 'ASC' : 'DESC';

    if ($filterTag) {
        // filtre par tag
        $sql = "
            SELECT
              p.id,
              p.titre,
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
            JOIN post_tag pt ON pt.post_id = p.id
            JOIN tags t ON t.id = pt.tag_id
            JOIN users u ON u.id = p.auteur
            WHERE t.name = :tag
            GROUP BY p.id
            ORDER BY p.created_at $sort
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['len' => $excerptLength, 'tag' => $filterTag]);
    } else {
        // sans filtre
        $sql = "
            SELECT
              p.id,
              p.titre,
              LEFT(p.contenu, :len) AS excerpt,
              CONCAT(u.prenom,' ',u.nom) AS auteur_nom,
              DATE_FORMAT(p.created_at,'%d-%m-%Y') AS created_at,
              DATE_FORMAT(p.updated_at,'%d-%m-%Y') AS updated_at,
              (SELECT GROUP_CONCAT(t2.name SEPARATOR ', ')
               FROM post_tag pt2
               JOIN tags t2 ON t2.id = pt2.tag_id
               WHERE pt2.post_id = p.id
              ) AS tags
            FROM posts p
            JOIN users u ON u.id = p.auteur
            GROUP BY p.id
            ORDER BY p.created_at $sort
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['len' => $excerptLength]);
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Synchronise la liste des tags pour un post donné.
 *
 * @param int    $postId   ID du post
 * @param string $tagsCsv  Chaîne CSV des noms de tags
 */
function syncPostTags(int $postId, string $tagsCsv): void
{
    $pdo = connexionBaseDeDonnees();
    // 1) Séparer, normaliser et dédupliquer
    $names = array_filter(array_map('trim', explode(',', $tagsCsv)));
    $names = array_unique(array_map('mb_strtolower', $names));

    if (count($names) === 0) {
        // supprimer toutes les liaisons existantes
        $pdo->prepare("DELETE FROM post_tag WHERE post_id = ?")
            ->execute([$postId]);
        return;
    }

    // 2) Insérer les nouveaux tags (IGNORE les doublons)
    $iStmt = $pdo->prepare("INSERT IGNORE INTO tags (name) VALUES (:name)");
    foreach ($names as $n) {
        $iStmt->execute(['name' => $n]);
    }

    // 3) Récupérer leurs IDs
    $placeholders = implode(',', array_fill(0, count($names), '?'));
    $sStmt = $pdo->prepare("SELECT id FROM tags WHERE name IN ($placeholders)");
    $sStmt->execute($names);
    $tagIds = $sStmt->fetchAll(PDO::FETCH_COLUMN);

    // 4) Réinitialiser les liaisons, puis recréer
    $pdo->prepare("DELETE FROM post_tag WHERE post_id = ?")
        ->execute([$postId]);

    $ptStmt = $pdo->prepare("INSERT INTO post_tag (post_id, tag_id) VALUES (?, ?)");
    foreach ($tagIds as $tid) {
        $ptStmt->execute([$postId, $tid]);
    }
}

/**
 * Retourne la liste des tags (leurs noms) associés à un post.
 */
function getTagsForPost(int $postId): array
{
    $pdo = connexionBaseDeDonnees();
    return $pdo
        ->prepare("SELECT t.name FROM tags t JOIN post_tag pt ON pt.tag_id=t.id WHERE pt.post_id=?")
        ->execute([$postId])
        ? $pdo->query("SELECT t.name FROM tags t JOIN post_tag pt ON pt.tag_id=t.id WHERE pt.post_id=$postId")->fetchAll(PDO::FETCH_COLUMN)
        : [];
}

/**
 * Récupère tous les tags existants, ordonnés alphabetiquement.
 *
 * @return array  Chaque élément : ['id' => int, 'name' => string]
 */
function getAllTags(): array
{
    $pdo = connexionBaseDeDonnees();
    $stmt = $pdo->query("SELECT id, name FROM tags ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Insère un message de contact dans la table `messages`.
 *
 * @param array $data Doit contenir 'nom', 'email', 'contenu', 'sujet'
 * @return bool true si OK, false sinon
 */
function enregistrerMessage(array $data): bool
{
    $pdo = connexionBaseDeDonnees();
    $sql = "
        INSERT INTO messages (nom, email, contenu, sujet)
        VALUES (:nom, :email, :contenu, :sujet)
    ";
    $stmt = $pdo->prepare($sql);
    try {
        return $stmt->execute([
            ':nom' => $data['nom'],
            ':email' => $data['email'],
            ':contenu' => $data['message'],
            ':sujet' => $data['sujet'] ?? null,
        ]);
    } catch (PDOException $e) {
        error_log(
            date('Y-m-d H:i:s') . " | enregistrerMessage() PDO erreur: {$e->getMessage()}\n",
            3,
            __DIR__ . '/../../logs/bdd_erreurs.log'
        );
        return false;
    }
}