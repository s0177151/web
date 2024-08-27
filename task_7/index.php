<?php
  header('Content-Type: text/html; charset=UTF-8');
  if(strpos($_SERVER['REQUEST_URI'], 'index.php') === false){
    header('Location: index.php');
    exit();
  }

  include('connection.php');

  $log = isset($_SESSION['login']);
  $adminLog = checkAdmin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
  $uid = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
  $getUid = isset($_GET['uid']) ? checkInput($_GET['uid']) : '';

  if($adminLog){
    if(preg_match('/^[0-9]+$/', $getUid)){
      $uid = $getUid;
      $log = true;
    }
  }
  
  function del_cook($cook, $vals = 0){
    setcookie($cook.'_error', '', 100000);
    if($vals == 1) setcookie($cook.'_value', '', 100000);
    if($vals == 2) setcookie($cook, '', 100000);
  }
  function del_cook_all($p = 0){
    del_cook('fio', $p);
    del_cook('phone', $p);
    del_cook('email', $p);
    del_cook('birthday', $p);
    del_cook('gender', $p);
    del_cook('like_lang', $p);
    del_cook('biography', $p);
    del_cook('oznakomlen', $p);
  }

  function set_cook($cook, $val, $dop_time = 1){
    setcookie($cook, $val, time() + 24 * 60 * 60 * $dop_time);
  }

  function user_exit(){
    del_cook_all(1);
    session_destroy();
    header('Location: index.php');
    exit();
  }

  if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if(($adminLog && isset($getUid)) || !$adminLog){
      $cookAdmin = (isset($_COOKIE['admin_value']) ? $_COOKIE['admin_value'] : '');
      if($cookAdmin == '1'){
        del_cook_all(1);
        del_cook('admin', 1);
      }
    }

    $csrf_error = (isset($_COOKIE['csrf_error']) ? checkInput($_COOKIE['csrf_error']) : '');
    $fio = (isset($_COOKIE['fio_error']) ? checkInput($_COOKIE['fio_error']) : '');
    $phone = (isset($_COOKIE['phone_error']) ? checkInput($_COOKIE['phone_error']) : '');
    $email = (isset($_COOKIE['email_error']) ? checkInput($_COOKIE['email_error']) : '');
    $birthday = (isset($_COOKIE['birthday_error']) ? checkInput($_COOKIE['birthday_error']) : '');
    $gender = (isset($_COOKIE['gender_error']) ? checkInput($_COOKIE['gender_error']) : '');
    $like_lang = (isset($_COOKIE['like_lang_error']) ? checkInput($_COOKIE['like_lang_error']) : '');
    $biography = (isset($_COOKIE['biography_error']) ? checkInput($_COOKIE['biography_error']) : '');
    $oznakomlen = (isset($_COOKIE['oznakomlen_error']) ? checkInput($_COOKIE['oznakomlen_error']) : '');

    $errors = array();
    $messages = array();
    $values = array();
    $error = true;
    
    function setVal($enName, $param){
      global $values;
      $values[$enName] = empty($param) ? '' : checkInput($param);
    }

    function val_empty($enName, $val){
      global $errors, $messages, $error, $values;
      if($error) 
        $error = empty($_COOKIE[$enName.'_error']);

      $errors[$enName] = isset($_COOKIE[$enName.'_error']);
      $messages[$enName] = "<div class='messageError'>$val</div>";
      $values[$enName] = empty($_COOKIE[$enName.'_value']) ? '' : checkInput($_COOKIE[$enName.'_value']);
      del_cook($enName);
      return;
    }

    if (isset($_COOKIE['csrf_error'])) {
      $messages['error'] = 'Не соответствие CSRF токена';
      del_cook('csrf');
    }
    if (isset($_COOKIE['save'])) {
      del_cook('save', 2);
      del_cook('login', 2);
      del_cook('password', 2);
      $messages['success'] = (!$log) ? 'Спасибо, данные сохранены' : 'Данные изменены';
      if (isset($_COOKIE['password'])) {
        $messages['info'] = sprintf('Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong>
          и паролем <strong>%s</strong> для изменения данных.',
          checkInput($_COOKIE['login']),
          checkInput($_COOKIE['password']));
      }
    }
    
    val_empty('fio', $fio);
    val_empty('phone', $phone);
    val_empty('email', $email);
    val_empty('birthday', $birthday);
    val_empty('gender', $gender);
    val_empty('like_lang', $like_lang);
    val_empty('biography', $biography);
    val_empty('oznakomlen', $oznakomlen);
    
    $like_langsa = explode(',', $values['like_lang']);
    
    if ($error && $log) {
      try {
        $dbFD = $db->prepare("SELECT * FROM form_data WHERE user_id = ?");
        $dbFD->execute([$uid]);
        if($dbFD->rowCount() > 0){
          $fet = $dbFD->fetchAll(PDO::FETCH_ASSOC)[0];
          $form_id = $fet['id'];
          $_SESSION['form_id'] = $form_id;
          $dbL = $db->prepare("SELECT l.name FROM form_data_lang f
                                LEFT JOIN languages l ON l.id = f.id_lang
                                WHERE f.id_form = ?");
          $dbL->execute([$form_id]);
          $like_langsa = [];
          foreach($dbL->fetchAll(PDO::FETCH_ASSOC) as $item){
            $like_langsa[] = $item['name'];
          }
          setVal('fio', $fet['fio']);
          setVal('phone', $fet['phone']);
          setVal('email', $fet['email']);
          setVal('birthday', date("Y-m-d", $fet['birthday']));
          setVal('gender', $fet['gender']);
          setVal('like_lang', $like_lang);
          setVal('biography', $fet['biography']);
          setVal('oznakomlen', $fet['oznakomlen']);
        }
        else{
          unset($_SESSION['user_id']);
          $log = false;
          unset($uid);
          $messages['error'] = 'Пользователь был удален';
          user_exit();
        }
      }
      catch(PDOException $e){
        print('Error : ' . $e->getMessage());
        exit();
      }
    }
    
    include('form.php');
  }
  else {
    $csrf_tokens = (isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '');
    $fio = (isset($_POST['fio']) ? $_POST['fio'] : '');
    $phone = (isset($_POST['phone']) ? $_POST['phone'] : '');
    $email = (isset($_POST['email']) ? $_POST['email'] : '');
    $birthday = (isset($_POST['birthday']) ? $_POST['birthday'] : '');
    $gender = (isset($_POST['gender']) ? $_POST['gender'] : '');
    $like_lang = (isset($_POST['like_lang']) ? $_POST['like_lang'] : '');
    $biography = (isset($_POST['biography']) ? $_POST['biography'] : '');
    $oznakomlen = (isset($_POST['oznakomlen']) ? $_POST['oznakomlen'] : '');

    if($_SESSION['csrf_token'] != $csrf_tokens){
      set_cook('csrf_error', '1');
      header('Location: index.php'.(($getUid != NULL) ? '?uid='.$uid : ''));
      exit();
    }

    if(isset($_POST['logout_form'])){
      if($adminLog && empty($_SESSION['login'])){
        header('Location: admin.php');
      }
      else{
        user_exit();
      }
      exit();
    }

    $phone1 = preg_replace('/\D/', '', $phone);

    function val_empty($cook, $comment, $usl){
      global $error;
      $res = false;
      $setVal = $_POST[$cook];
      if ($usl) {
        set_cook($cook.'_error', $comment);
        $error = true;
        $res = true;
      }
      
      if($cook == 'like_lang'){
        global $like_lang;
        $setVal = ($like_lang != '') ? implode(",", $like_lang) : '';
      }
      
      set_cook($cook.'_value', $setVal, 60);
      return $res;
    }
    
    if(!val_empty('fio', 'Заполните поле', empty($fio))){
      if(!val_empty('fio', 'Длина поля > 255 символов', strlen($fio) > 255)){
        val_empty('fio', 'Поле не соответствует требованиям: <i>Фамилия Имя (Отчество)</i>, кириллицей', !preg_match('/^([а-яёА-ЯЁ]+-?[а-яёА-ЯЁ]+)( [а-яёА-ЯЁ]+-?[а-яёА-ЯЁ]+){1,2}$/Diu', $fio));
      }
    }
    if(!val_empty('phone', 'Заполните поле', empty($phone))){
      if(!val_empty('phone', 'Длина поля некорректна', strlen($phone) != 11)){
        val_empty('phone', 'Поле должен содержать только цифры', ($phone != $phone1));
      }
    }
    if(!val_empty('email', 'Заполните поле', empty($email))){
      if(!val_empty('email', 'Длина поля > 255 символов', strlen($email) > 255)){
        val_empty('email', 'Поле не соответствует требованию example@mail.ru', !preg_match('/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/', $email));
      }
    }
    if(!val_empty('birthday', "Выберите дату рождения", empty($birthday))){
      val_empty('birthday', "Неверно введена дата рождения, дата больше настоящей", (strtotime("now") < strtotime($birthday)));
    }
    val_empty('gender', "Выберите пол", (empty($gender) || !preg_match('/^(male|female)$/', $gender)));
    if(!val_empty('like_lang', "Выберите хотя бы один язык", empty($like_lang))){
      try {
        $inQuery = implode(',', array_fill(0, count($like_lang), '?'));
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
      
      val_empty('like_lang', 'Неверно выбраны языки', $dbLangs->rowCount() != count($like_lang));
    }
    if(!val_empty('biography', 'Заполните поле', empty($biography))){
      val_empty('biography', 'Длина текста > 65 535 символов', strlen($biography) > 65535);
    }
    val_empty('oznakomlen', "Ознакомьтесь с контрактом", empty($oznakomlen));
    
    if ($error) {
      header('Location: index.php'.(($getUid != NULL) ? '?uid='.$uid : ''));
      exit();
    }
    else {
      del_cook_all();
    }
    
    if ($log) {
      $stmt = $db->prepare("UPDATE form_data SET fio = ?, phone = ?, email = ?, birthday = ?, gender = ?, biography = ? WHERE user_id = ?");
      $stmt->execute([$fio, $phone, $email, strtotime($birthday), $gender, $biography, $uid]);

      $stmt = $db->prepare("DELETE FROM form_data_lang WHERE id_form = ?");
      $stmt->execute([$_SESSION['form_id']]);

      $stmt1 = $db->prepare("INSERT INTO form_data_lang (id_form, id_lang) VALUES (?, ?)");
      foreach($languages as $row){
          $stmt1->execute([$_SESSION['form_id'], $row['id']]);
      }
      if($adminLog) 
        set_cook('admin_value', '1', 60);
    }
    else {
      $login = substr(uniqid(), 0, 4).rand(10, 100);
      $password = rand(100, 1000).substr(uniqid(), 4, 10);
      setcookie('login', $login);
      setcookie('password', $password);
      $mpassword = md5($password);
      try {
        $stmt = $db->prepare("INSERT INTO users (login, password) VALUES (?, ?)");
        $stmt->execute([$login, $mpassword]);
        $user_id = $db->lastInsertId();

        $stmt = $db->prepare("INSERT INTO form_data (user_id, fio, phone, email, birthday, gender, biography) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $fio, $phone, $email, strtotime($birthday), $gender, $biography]);
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
      set_cook('fio_value', $fio, 365);
      set_cook('phone_value', $phone, 365);
      set_cook('email_value', $email, 365);
      set_cook('birthday_value', $birthday, 365);
      set_cook('gender_value', $gender, 365);
      set_cook('like_value', $like, 365);
      set_cook('biography_value', $biography, 365);
      set_cook('oznakomlen_value', $oznakomlen, 365);
    }
    setcookie('save', '1');
    header('Location: index.php'.(($getUid != NULL) ? '?uid='.$uid : ''));
  }
?>