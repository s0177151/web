
<?php
  header('Content-Type: text/html; charset=UTF-8');

  $db = '';

  function conn(){
    global $db;
    include('connection.php');
  }
  
  if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['save'])) {
      // Если есть параметр save, то выводим сообщение пользователю.
      print('<div class="message">Спасибо, данные сохранены.</div>');
    }
    include('form.php');
    exit();
  }

  function errp($error){
    print("<div class='messageError'>$error</div>");
    exit();
  }

  function val_empty($val, $name, $o = 0){
    if(empty($val)){
      if($o == 0){
        errp("Заполните поле $name.<br/>");
      }
      if($o == 1){
        errp("Выберите $name.<br/>");
      }
      if($o == 2){
        errp("ознакомьтесь с контрактом<br/>");
      }
      exit();
    }
  }

  $errors = '';
  $fio = (isset($_POST['fio']) ? $_POST['fio'] : '');
  $phone = (isset($_POST['phone']) ? $_POST['phone'] : '');
  $email = (isset($_POST['email']) ? $_POST['email'] : '');
  $birthday = (isset($_POST['birthday']) ? strtotime($_POST['birthday']) : '');
  $gender = (isset($_POST['gender']) ? $_POST['gender'] : '');
  $like_lang = (isset($_POST['like_lang']) ? $_POST['like_lang'] : '');
  $biography = (isset($_POST['biography']) ? $_POST['biography'] : '');
  $oznakomlen = (isset($_POST['oznakomlen']) ? $_POST['oznakomlen'] : '');

  $phone = preg_replace('/\D/', '', $phone);
  
  $like_lang_s = ($like_lang != '') ? implode(", ", $like_lang) : [];
  
  val_empty($fio, "имя");
  val_empty($phone, "телефон");
  val_empty($email, "email");
  val_empty($birthday, "дата");
  val_empty($gender, "пол", 1);
  val_empty($like_lang, "языки", 1);
  val_empty($biography, "биографию");
  val_empty($oznakomlen, "ознакомлен", 2);
  if(empty($fio)){
    print('пустое поле фио');
  }

  if(strlen($fio) > 255){
    $errors = 'Длина поля "ФИО" > 255 символов';
  }
  elseif(count(explode(" ", $fio)) < 2 || !preg_match('/^([а-яa-zё]+-?[а-яa-zё]+)( [а-яa-zё]+-?[а-яa-zё]+){1,2}$/Diu', $fio)){
    $errors = 'Неверный формат ФИО';
  } 
  elseif(strlen($phone) != 11 || !preg_match('/^\d{11}$/', $phone)){
    $errors = 'Неверное значение поля "Телефон"';
  }
  elseif(strlen($email) > 255){
    $errors = 'Длина поля "email" > 255 символов';
  }
  elseif(!preg_match('/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/', $email)){
    $errors = 'Неверное значение поля "email"';
  }
  elseif(!is_numeric($birthday) || strtotime("now") < $birthday){
    $errors = 'Укажите корректно дату';
  }
  elseif($gender != "male" && $gender != "female"){
    $errors = 'Укажите пол';
  }
  elseif(count($like_lang) == 0){
    $errors = 'Укажите языки';
  }

  if ($errors != '') {
    errp($errors);
  }

  conn();
  $inQuery = implode(',', array_fill(0, count($like_lang), '?'));
  try {
    $dbLangs = $db->prepare("SELECT id, name FROM languages WHERE name IN ($inQuery)");
    foreach ($like_lang as $key => $value) {
      $dbLangs->bindValue(($key+1), $value);
    }
    $dbLangs->execute();
    $languages = $dbLangs->fetchAll(PDO::FETCH_ASSOC);
  }
  catch(PDOException $e){
    print('Error : ' . $e->getMessage());
    exit();
  }
  
  if($dbLangs->rowCount() != count($like_lang)){
    $errors = 'Неверно выбраны языки';
  }
  elseif(strlen($biography) > 65535){
    $errors = 'Длина поля "Биография" > 65 535 символов';
  }

  if ($errors != '') {
    errp($errors);
  }

  try {
    $stmt = $db->prepare("INSERT INTO form_data (fio, phone, email, birthday, gender, biography) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$fio, $phone, $email, $birthday, $gender, $biography]);
    $fid = $db->lastInsertId();
    $stmt1 = $db->prepare("INSERT INTO form_data_lang (id_form, id_lang) VALUES (?, ?)");
    foreach($languages as $row){
        $stmt1->execute([$fid, $row['id']]);
    }
  }
  catch(PDOException $e){
    print('Error : ' . $e->getMessage());
    exit();
  }

  //  stmt - это "дескриптор состояния".
  
  //  Именованные метки.
  //$stmt = $db->prepare("INSERT INTO test (label,color) VALUES (:label,:color)");
  //$stmt -> execute(['label'=>'perfect', 'color'=>'green']);
  
  //Еще вариант
  /*$stmt = $db->prepare("INSERT INTO users (firstname, lastname, email) VALUES (:firstname, :lastname, :email)");
  $stmt->bindParam(':firstname', $firstname);
  $stmt->bindParam(':lastname', $lastname);
  $stmt->bindParam(':email', $email);
  $firstname = "John";
  $lastname = "Smith";
  $email = "john@test.com";
  $stmt->execute();
  */

  // Делаем перенаправление.
  // Если запись не сохраняется, но ошибок не видно, то можно закомментировать эту строку чтобы увидеть ошибку.
  // Если ошибок при этом не видно, то необходимо настроить параметр display_errors для PHP.
  header('Location: ?save=1');
