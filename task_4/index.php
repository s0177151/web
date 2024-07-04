<?php
header('Content-Type: text/html; charset=UTF-8');

function deleteCookies($cookie){
    setcookie($cookie.'_error', '', time() - 30 * 24 * 60 * 60);
}

$db;

function conn(){
    global $db;
    include('connection.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $fullName = (!empty($_COOKIE['fullName_error']) ? $_COOKIE['fullName_error'] : '');
    $phone = (!empty($_COOKIE['phone_error']) ? $_COOKIE['phone_error'] : '');
    $email = (!empty($_COOKIE['email_error']) ? $_COOKIE['email_error'] : '');
    $birthday = (!empty($_COOKIE['birthday_error']) ? $_COOKIE['birthday_error'] : '');
    $gender = (!empty($_COOKIE['gender_error']) ? $_COOKIE['gender_error'] : '');
    $favoriteLanguages = (!empty($_COOKIE['favoriteLanguages_error']) ? $_COOKIE['favoriteLanguages_error'] : '');
    $biography = (!empty($_COOKIE['biography_error']) ? $_COOKIE['biography_error'] : '');
    $agreement = (!empty($_COOKIE['agreement_error']) ? $_COOKIE['agreement_error'] : '');

    $errors = array();
    $messages = array();
    $values = array();

    function validateEmpty($enName, $val){
        global $errors, $values, $messages;

        $errors[$enName] = !empty($_COOKIE[$enName.'_error']);
        $messages[$enName] = "<div class='text-red-600 text-sm'>$val</div>";
        $values[$enName] = empty($_COOKIE[$enName.'_value']) ? '' : $_COOKIE[$enName.'_value'];
        deleteCookies($enName);
        return;
    }

    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        // Если есть параметр save, то выводим сообщение пользователю.
        $messages['success'] = '<div class="text-green-600 text-sm">Спасибо, данные сохранены.</div>';
    }

    validateEmpty('fullName', $fullName);
    validateEmpty('phone', $phone);
    validateEmpty('email', $email);
    validateEmpty('birthday', $birthday);
    validateEmpty('gender', $gender);
    validateEmpty('favoriteLanguages', $favoriteLanguages);
    validateEmpty('biography', $biography);
    validateEmpty('agreement', $agreement);

    $favoriteLanguagesSA = explode(',', $values['favoriteLanguages']);

    include('form.php');
}
else{
    $fullName = (!empty($_POST['fullName']) ? $_POST['fullName'] : '');
    $phone = (!empty($_POST['phone']) ? $_POST['phone'] : '');
    $email = (!empty($_POST['email']) ? $_POST['email'] : '');
    $birthday = (!empty($_POST['birthday']) ? $_POST['birthday'] : '');
    $gender = (!empty($_POST['gender']) ? $_POST['gender'] : '');
    $favoriteLanguages = (!empty($_POST['favoriteLanguages']) ? $_POST['favoriteLanguages'] : '');
    $biography = (!empty($_POST['biography']) ? $_POST['biography'] : '');
    $agreement = (!empty($_POST['agreement']) ? $_POST['agreement'] : '');
    $error = false;

    $phone1 = preg_replace('/\D/', '', $phone);

    function val_empty($cook, $comment, $usl){
        global $error;
        $res = false;
        $setVal = $_POST[$cook];
        if ($usl) {
            setcookie($cook.'_error', $comment, time() + 24 * 60 * 60); //сохраняем на сутки
            $error = true;
            $res = true;
        }

        if($cook == 'favoriteLanguages'){
            global $favoriteLanguages;
            $setVal = ($favoriteLanguages != '') ? implode(",", $favoriteLanguages) : '';
        }

        setcookie($cook.'_value', $setVal, time() + 30 * 24 * 60 * 60); //сохраняем на месяц
        return $res;
    }

    if(!val_empty('fullName', 'Заполните поле', empty($fullName))){
        if(!val_empty('fullName', 'Длина поля > 255 символов', strlen($fullName) > 255)){
            val_empty('fullName', 'Поле не соответствует требованиям: <i>Фамилия Имя (Отчество)</i>, кириллицей', !preg_match('/^([а-яёА-ЯЁ]+-?[а-яёА-ЯЁ]+)( [а-яёА-ЯЁ]+-?[а-яёА-ЯЁ]+){1,2}$/Diu', $fullName));
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
    if(!val_empty('favoriteLanguages', "Выберите хотя бы один язык", empty($favoriteLanguages))){
        conn();
        try {
            $inQuery = implode(',', array_fill(0, count($favoriteLanguages), '?'));
            $dbLangs = $db->prepare("SELECT id, name FROM languages WHERE name IN ($inQuery)");
            foreach ($favoriteLanguages as $key => $value) {
                $dbLangs->bindValue(($key+1), $value);
            }
            $dbLangs->execute();
            $languages = $dbLangs->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e){
            print('Error : ' . $e->getMessage());
            exit();
        }

        val_empty('favoriteLanguages', 'Неверно выбраны языки', $dbLangs->rowCount() != count($favoriteLanguages));
    }
    if(!val_empty('biography', 'Заполните поле', empty($biography))){
        val_empty('biography', 'Длина текста > 65 535 символов', strlen($biography) > 65535);
    }
    val_empty('agreement', "Ознакомьтесь с контрактом", empty($agreement));

    if ($error) {
        // При наличии ошибок перезагружаем страницу и завершаем работу скрипта.
        header('Location: index.php');
        exit();
    }
    else {
        // Удаляем Cookies с признаками ошибок.
        deleteCookies('fullName');
        deleteCookies('phone');
        deleteCookies('email');
        deleteCookies('birthday');
        deleteCookies('gender');
        deleteCookies('favoriteLanguages');
        deleteCookies('biography');
        deleteCookies('agreement');
    }

    try {
        $stmt = $db->prepare("INSERT INTO form_data (fullName, phone, email, birthday, gender, biography) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fullName, $phone, $email, strtotime($birthday), $gender, $biography]);
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
    setcookie('fullName_value', $fullName, time() + 24 * 60 * 60 * 365);
    setcookie('phone_value', $phone, time() + 24 * 60 * 60 * 365);
    setcookie('email_value', $email, time() + 24 * 60 * 60 * 365);
    setcookie('birthday_value', $birthday, time() + 24 * 60 * 60 * 365);
    setcookie('gender_value', $gender, time() + 24 * 60 * 60 * 365);
    setcookie('favoriteLanguages_value', implode(',', $favoriteLanguages), time() + 24 * 60 * 60 * 365);
    setcookie('biography_value', $biography, time() + 24 * 60 * 60 * 365);
    setcookie('agreement_value', $agreement, time() + 24 * 60 * 60 * 365);
    setcookie('save', '1', time() + 24 * 60 * 60);
    header('Location: index.php');
    exit();
}
?>
