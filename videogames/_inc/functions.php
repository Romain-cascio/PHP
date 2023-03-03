<?php

function ContactProcess($name, $email, $message)
{
  $errors = ContactValidate($name, $email, $message);

  if (empty($errors)) {
    // Envoyer le message de contact par e-mail ou le sauvegarder dans la base de données

    // Enregistrer un message flash dans la session pour informer l'utilisateur que le message a été envoyé avec succès
    $_SESSION['notice'] = "Vous serez contacté dans les plus brefs délais.";

    // Rediriger l'utilisateur vers la page d'accueil
    header('Location: index.php');
    exit();
  }

  return $errors;
}

function ContactValidate($name, $email, $message)
{
  $errors = [];

  // Vérifier le nom
  if (empty($name)) {
    $errors[] = "Le nom est obligatoire.";
  }

  // Vérifier l'adresse e-mail
  if (empty($email)) {
    $errors[] = "L'adresse e-mail est obligatoire.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "L'adresse e-mail n'est pas valide.";
  }

  // Vérifier le message
  if (empty($message)) {
    $errors[] = "Le message est obligatoire.";
  }

  return $errors;
}


function isEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isLong($value) {
    return strlen($value) >= 10;
}

function connect_to_db() {
    $host = 'localhost'; // remplacer par votre hôte de base de données
    $username = 'root'; // remplacer par votre nom d'utilisateur
    $password = ''; // remplacer par votre mot de passe
    $database = 'videogames'; // remplacer par le nom de votre base de données
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
      die("Connexion échouée: " . $conn->connect_error);
    }
    
    return $conn;
  }

  // fonction pour retourner n jeux vidéo sélectionnés aléatoirement
function get_rng_games($n) {
    $conn = connect_to_db();
    $sql = "SELECT * FROM game ORDER BY RAND() LIMIT $n";
    $result = $conn->query($sql);
    $games = array();
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $games[] = $row;
      }
    }
    $conn->close();
    return $games;
  }
  
  // fonction pour retourner tous les jeux vidéo présents dans la table game
  function get_all_games() {
    $conn = connect_to_db();
    $sql = "SELECT * FROM game";
    $result = $conn->query($sql);
    $games = array();
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $games[] = $row;
      }
    }
    $conn->close();
    return $games;
  }
  
  // fonction pour retourner un jeu vidéo à l'aide de son identifiant
  function Games_by_id($id) {
    $conn = connect_to_db();
    $sql = "SELECT * FROM game WHERE id=$id";
    $result = $conn->query($sql);
    $game = null;
    if ($result->num_rows > 0) {
      $game = $result->fetch_assoc();
    }
    $conn->close();
    return $game;
  }

  function Login_Validation($email, $password) {
    $errors = array();
  
    if (empty($email)) {
      $errors[] = "L'adresse e-mail est obligatoire.";
    }
  
    if (empty($password)) {
      $errors[] = "Le mot de passe est obligatoire.";
    }
  
    return $errors;
  }

  function Admin_Email($email) {
    $conn = connect_to_db();
    $email = $conn->real_escape_string($email);
    $sql = "SELECT * FROM admin WHERE email='$email'";
    $result = $conn->query($sql);
    $admin = null;

    if ($result->num_rows > 0) {
      $admin = $result->fetch_assoc();
    }
    $conn->close();
    return $admin;
}

function Verify_admin_created($email, $password) {
    $admin = Admin_Email($email);
    // exit(var_dump( password_verify($password, $admin['password'])  ));
    if (!$admin) {
      return false;
    }
    return password_verify($password, $admin['password']);
  }

  
  function getSessionFlashMessage($key)
  {
    if (array_key_exists($key, $_SESSION)) {
      $value = $_SESSION[$key];
      unset($_SESSION[$key]);
      return $value;
    }
    return null;
  }

  
  function Login_Process($email, $password)
  {
    $errors = Login_Validation($email, $password);
    if (empty($errors)) {
      // Vérifier si les identifiants de l'administrateur sont valides
      if (Verify_admin_created($email, $password)) {
        // Stocker l'identifiant de l'administrateur dans la session
        $admin_id = Admin_Email($email);
        $_SESSION['user'] = $admin_id;
        // Rediriger l'utilisateur vers la page d'accueil
        header('Location: index.php');
        exit();
      } else {
        $errors[] = "Identifiants incorrects.";
      }
    } else {
      // Enregistrer un message flash dans la session pour informer l'utilisateur des erreurs
      $_SESSION['notice'] = "Identifiants incorrects.";
    }
    
    return $errors;
  }
  function getSessionData($key)
  {
    if (array_key_exists($key, $_SESSION)) {
      return $_SESSION[$key];
    } else {
      return null;
    }
  }

  function checkAuthentication() {
    if (!array_key_exists('user', $_SESSION)) {
      $_SESSION['notice'] = 'Accès refusé';
      header('Location: ../index.php');
      exit;
    }
  }
  
  function Game_Process($data)
{
    // Validation des données du formulaire
    $constraints = Game_Contraints();
    $errors = validate_Data($data, $constraints);
    var_dump($data['title'], $data['description'], $data['release_date']);
    // Si le formulaire n'est pas valide, stocker les erreurs dans la session et rediriger
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $data;
        return false;
    }

    // Récupération des données du formulaire
    $id = $data['id'];
    $title = $data['title'];
    $description = $data['description'];
    $release_date = $data['release_date'];
    $poster = $data['poster'];
    $price = $data['price'];
      
    // Vérification si c'est une création ou une modification
    if (empty($id)) {
        // Création d'un nouveau jeu vidéo
        NewGame_Insert($title, $description, $release_date, $poster, $price);
    } else {
        // Modification d'un jeu vidéo existant
        Game_Update($id, $title, $description, $release_date, $poster, $price);
    }

    // Redirection vers la liste des jeux vidéo
    header('Location: index.php');
    exit;
}

/**
 * Retourne les contraintes de validation du formulaire des jeux vidéo
 *
 * @return array Les contraintes de validation
 */
function Game_Contraints()
{
    return [
        'id' => [], // Champ caché, pas besoin de validation
        'title' => [
            'required' => true,
            'min_length' => 3,
            'max_length' => 255,
        ],
        'description' => [
            'required' => true,
            'min_length' => 10,
        ],
        'release_date' => [
            'required' => true,
            'date_format' => 'Y-m-d',
        ],
        'poster' => [
            'required' => true,
            'url' => true,
            'max_length' => 255,
        ],
        'price' => [
            'required' => true,
            'numeric' => true,
            'isFloatInRange' => true,
        ],
    ];
}

function isFloatInRange($input, $min, $max)
{
    $options = [
        'options' => [
            'min_range' => $min,
            'max_range' => $max,
        ],
    ];

    return filter_var($input, FILTER_VALIDATE_FLOAT, $options) !== false;
}

function NewGame_Insert($data) {
  $conn = connect_to_db();
  $title = $conn->real_escape_string($data['title']);
  $description = $conn->real_escape_string($data['description']);
  $release_date = $conn->real_escape_string($data['release_date']);
  $poster = $conn->real_escape_string($data['poster']);
  $price = $conn->real_escape_string($data['price']);
  $editor_id = $conn->real_escape_string($data['editor_id']);

  $sql = "INSERT INTO game (title, description, release_date, poster, price) VALUES ('$title', '$description', '$release_date', '$poster', '$price')";
  
  $query = $conn->query($sql);
  $last_id = $conn->insert_id;
  
  $conn->close();
  return $last_id;
}

function validate_Data($data) {
  $errors = [];
  
  // Vérification du titre
  if (empty($data['title'])) {
    $errors[] = "Le champ titre est obligatoire.";
  } else if (strlen($data['title']) > 255) {
    $errors[] = "Le champ titre ne doit pas dépasser 255 caractères.";
  }
  
  // Vérification de la description
  if (empty($data['description'])) {
    $errors[] = "Le champ description est obligatoire.";
  }
  
  // Vérification de la date de sortie
  if (empty($data['release_date'])) {
    $errors[] = "Le champ date de sortie est obligatoire.";
  } else {
    $date = DateTime::createFromFormat('Y-m-d', $data['release_date']);
    if (!$date || $date->format('Y-m-d') !== $data['release_date']) {
      $errors[] = "Le champ date de sortie doit être au format AAAA-MM-JJ.";
    }
  }
  
  // Vérification de l'image
  if (empty($data['poster'])) {
    $errors[] = "Le champ image est obligatoire.";
  }
  
  // Vérification du prix
  if (empty($data['price'])) {
    $errors[] = "Le champ prix est obligatoire.";
  } else if (!isFloatInRange($data['price'], 0, 999.99)) {
    $errors[] = "Le champ prix doit être un nombre décimal compris entre 0 et 999.99.";
  }
  
  return $errors;
}

function Game_Update($data)
{
  $conn = connect_to_db();
  $id = $conn->real_escape_string($data['id']);
  $title = $conn->real_escape_string($data['title']);
  $description = $conn->real_escape_string($data['description']);
  $release_date = $conn->real_escape_string($data['release_date']);
  $poster = $conn->real_escape_string($data['poster']);
  $price = $conn->real_escape_string($data['price']);
  $editor_id = $conn->real_escape_string($data['editor_id']);
  $sql = "UPDATE game SET title='$title', description='$description', release_date='$release_date', poster='$poster', price=$price, editor_id=$editor_id WHERE id=$id";
  $result = $conn->query($sql);
  $conn->close();
}


function deleteGame($id) {
    $conn = connect_to_db();
    $id = $conn->real_escape_string($id);
    $sql = "DELETE FROM game WHERE id=$id";
    $result = $conn->query($sql);
    $conn->close();
    $_SESSION['notice'] = "Jeu vidéo supprimé";
}

function Get_Editors() {
  $conn = connect_to_db();
  $sql = "SELECT * FROM editor";
  $result = $conn->query($sql);
  $editors = array();
  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      $editors[] = $row;
    }
  }
  $conn->close();
  return $editors;
}


?>
