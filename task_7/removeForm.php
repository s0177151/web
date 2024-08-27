<?php
    require('connection.php');

    function res($status, $val){
        exit(json_encode(array('status' => $status, 'value' => $val), JSON_UNESCAPED_UNICODE));
    }

    $login = $_SERVER['PHP_AUTH_USER'];
    $password = md5($_SERVER['PHP_AUTH_PW']);

    if(!checkAdmin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) res('error', "Вы не авторизованы");

    $id = checkInput($_POST['id']);
    $csrf_token = checkInput($_POST['csrf_token']);
    $csrf_token_admin = $_SESSION['csrf_token_admin'];
    if(!preg_match('/^[0-9]+$/', $id)) res('error', "Введите id");
    if($csrf_token != $csrf_token_admin) res('error', "Не соответствие CSRF токена");

    $stmt = $db->prepare("SELECT id FROM users WHERE login = ? and password = ?");
    $stmt->execute([$login, $password]);
    $its = $stmt->rowCount();

    if(!$its) res('error', "Неверный логин или пароль");

    
    $dbf = $db->prepare("SELECT * FROM form_data WHERE id = ?");
    $dbf->execute([$id]);
    $data = $dbf->fetch(PDO::FETCH_ASSOC);
    if($dbf->rowCount() != 0){
        $dbdel = $db->prepare("DELETE FROM form_data WHERE id = ?");
        $f1 = $dbdel->execute([$id]);
        $dbdel = $db->prepare("DELETE FROM form_data_lang WHERE id_form = ?");
        $f2 = $dbdel->execute([$id]);
        $dbdel = $db->prepare("DELETE FROM users WHERE id = ?");
        $f3 = $dbdel->execute([$data['user_id']]);
        
        ($f1 && $f2 && $f3) ? res('success', "Форма удалена") : res('error', "Ошибка удаления");
    }
    else{
        res('error', "Форма не найдена");
    }
?>