<?php
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }
  include('../../_inc/header.php');
  include('../_inc/nav.php');
  require_once '../../_inc/functions.php';
  checkAuthentication();
  $editors = Get_Editors();
  if(isset($_GET['id'])){
    $get_game = Games_by_id($_GET['id']);
    // var_dump($get_game);
  }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $errors = validate_Data($data);
    if (empty($errors)) {
      // var_dump(!isset($_GET['id'])); exit;
        if(!isset($_GET['id'])){
        $game_id = NewGame_Insert($data);
        $_SESSION['notice'] = "Jeu vidéo ajouté";
        header('Location: index.php');
        exit;
        }
        else{
        $game_id=Game_Update($data);
        $_SESSION['notice'] = "Jeu vidéo modifié";
        header('Location: index.php');
        exit;
        }
    }
  }
?>
<div class="container">
<form method="POST">
  <input type="hidden" name="id" value="">
  <div class="form-group mt-3">
    <label for="title">Titre :</label>
    <input type="text" name="title" id="title" class="form-control" value="<?= $get_game['title'] ?? null ?>">
  </div>
  <div class="form-group mt-3">
    <label for="description">Description :</label>
    <textarea name="description" id="description" class="form-control" ><?= $get_game['description'] ?? null ?></textarea>
    </div>
  <div class="form-group mt-3">
    <label for="release_date">Date de sortie :</label>
    <input type="date" name="release_date" id="release_date" class="form-control" value="<?= $get_game['release_date'] ?? null ?>">
    </div>
  <div class="form-group mt-3">
    <label for="poster">URL de l'affiche :</label>
    <input type="text" name="poster" id="poster" class="form-control" value="<?= $get_game['poster'] ?? null ?>">
    </div>
  <div class="form-group mt-3">
    <label for="price">Prix :</label>
    <input type="text" name="price" id="price" class="form-control" value="<?= $get_game['price'] ?? null ?>">
  </div>
  <div class="form-group mt-3">
    <?php 
    echo '<label for="editor_id">Editeur :</label>';
    echo '<select name="editor_id" id="editor_id" value="<?= $get_game[\'editor_id\'] ?? null ?>">';
    foreach ($editors as $editor) {
        echo '<option value="' . $editor['id'] . '">' . $editor['name'] . '</option>';
    }
    echo '</select>';
    ?>
  </div>
  <div class="d-flex justify-content-center">
    <input type="hidden" name="id" value="<?= $get_game['id'] ?? null ?>">
    <?php 
    if(isset($get_game['id'])){
      echo '<button type="submit" class="btn btn-warning mt-3 ">Modifier</button>';
    }
    else {
      echo '<button type="submit" class="btn btn-success mt-3 ">Ajouter</button>';
    }
    ?>
    
  </div>
</form>
</div>

<?php
include('../../_inc/footer.php');
?>
