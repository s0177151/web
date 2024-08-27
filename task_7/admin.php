<?php
  require('connection.php');

  $haveAdmin = checkAdmin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

  if (!$haveAdmin) {
    header('HTTP/1.1 401 Unanthorized');
    header('WWW-Authenticate: Basic realm="My site"');
    print('<h1>401 Требуется авторизация</h1>');
    
    exit();
  }

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="libs/bootstrap-4.0.0-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./styleAdmin.css">
    <script src="./libs/js/jquery-3.4.1.min.js"></script>
    <title>Задание 7 (админка)</title>
</head>
<body class="admin">

  <header>
    <div><a href="#data">Информация</a></div>
    <div><a href="#analize">Статистика</a></div>
</header>

  <div>
    <?php
        $csrf_token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_admin'] = $csrf_token;
    ?>
    <input type="hidden" name='csrf_token' id='csrf_token' value='<?php echo $csrf_token; ?>'>
  </div>

  <table id="data">
    <thead>
      <tr>
        <th>id</th>
        <th>ФИО</th>
        <th>Телефон</th>
        <th>Почта</th>
        <th>День рождения</th>
        <th>Пол</th>
        <th>Биография</th>
        <th>ЯП</th>
        <th></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php
        $dbFD = $db->query("SELECT * FROM form_data ORDER BY id DESC");
        while($row = $dbFD->fetch(PDO::FETCH_ASSOC)){
          echo '<tr data-id='.checkInput($row['id']).'>
                  <td>'.checkInput($row['id']).'</td>
                  <td>'.checkInput($row['fio']).'</td>
                  <td>'.checkInput($row['phone']).'</td>
                  <td>'.checkInput($row['email']).'</td>
                  <td>'.date("d.m.Y", checkInput($row['birthday'])).'</td>
                  <td>'.((checkInput($row['gender']) == "male") ? "Мужской" : "Женский").'</td>
                  <td class="wb">'.checkInput($row['biography']).'</td>
                  <td>';
          $dbl = $db->prepare("SELECT * FROM form_data_lang fd
                                LEFT JOIN languages l ON l.id = fd.id_lang
                                WHERE id_form = ?");
          $dbl->execute([$row['id']]);
          while($row1 = $dbl->fetch(PDO::FETCH_ASSOC)){
            echo $row1['name'].'<br>';
          }
          echo '</td>
                <td><a href="./index.php?uid='.$row['user_id'].'" target="_blank">Редактировать</a></td>
                <td><button class="remove">Удалить</button></td>
                <td colspan="10" class="form_del hid">Форма удалена</td>
              </tr>';
        }
      ?>


    </tbody>
  </table>

  <table class="analize" id="analize">
    <thead>
      <tr>
        <th>ЯП</th>
        <th>Пользователи</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $qu = $db->query("SELECT l.id, l.name, COUNT(id_form) as count FROM languages l 
                            LEFT JOIN form_data_lang fd ON fd.id_lang = l.id
                            GROUP by l.id");
        while($row = $qu->fetch(PDO::FETCH_ASSOC)){
          echo '<tr>
                  <td>'.$row['name'].'</td>
                  <td>'.$row['count'].'</td>
                </tr>';
        }
      ?>
    </tbody>
  </table>

  <script src="./core.js"></script>
</body>
</html>
