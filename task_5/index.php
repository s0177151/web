<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();
$log = !empty($_SESSION['login']);

function deleteCookies($cook, $vals = 0){
    setcookie($cook.'_error', '', 100000);
    if($vals) setcookie($cook.'_value', '', 100000);
}

$db;
function connectDatabase(){
    global $db;
    include('connection.php');
}

// Define validateEmpty function here
function validateEmpty($enName, $val){
    global $errors, $messages, $error, $values;
    if ($error)
        $error = empty($_COOKIE[$enName . '_error']);

    $errors[$enName] = !empty($_COOKIE[$enName . '_error']);
    $messages[$enName] = "<div class='messageError'>$val</div>";
    $values[$enName] = empty($_COOKIE[$enName . '_value']) ? '' : strip_tags($_COOKIE[$enName . '_value']);
    deleteCookies($enName);
    return;
}

function setValue($enName, $param)
{
    global $values;
    $values[$enName] = empty($param) ? '' : strip_tags($param);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $fullName = (!empty($_COOKIE['fullName_error']) ? $_COOKIE['fullName_error'] : '');
    $phone = (!empty($_COOKIE['phone_error']) ? $_COOKIE['phone_error'] : '');
    $email = (!empty($_COOKIE['email_error']) ? $_COOKIE['email_error'] : '');
    $birthday = (!empty($_COOKIE['birthday_error']) ? $_COOKIE['birthday_error'] : '');
    $gender = (!empty($_COOKIE['gender_error']) ? $_COOKIE['gender_error'] : '');
    $like_lang = (!empty($_COOKIE['like_lang_error']) ? $_COOKIE['like_lang_error'] : '');
    $biography = (!empty($_COOKIE['biography_error']) ? $_COOKIE['biography_error'] : '');
    $agreement = (!empty($_COOKIE['agreement_error']) ? $_COOKIE['agreement_error'] : '');

    $errors = array();
    $messages = array();
    $values = array();
    $error = true;

    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        setcookie('login', '', 100000);
        setcookie('password', '', 100000);
        $messages['success'] = 'Спасибо, результаты сохранены.';
        if (!empty($_COOKIE['password'])) {
            $messages['info'] = sprintf('Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong> и паролем <strong>%s</strong> для изменения данных.',
                strip_tags($_COOKIE['login']),
                strip_tags($_COOKIE['password']));
        }
    }

    validateEmpty('fullName', $fullName);
    validateEmpty('phone', $phone);
    validateEmpty('email', $email);
    validateEmpty('birthday', $birthday);
    validateEmpty('gender', $gender);
    validateEmpty('like_lang', $like_lang);
    validateEmpty('biography', $biography);
    validateEmpty('agreement', $agreement);

    $favoriteLanguagesSA = explode(',', $values['like_lang']);

    if ($error && !empty($_SESSION['login'])) {
        if (isset($_SESSION['user_id'])) {
            connectDatabase();
            try {
                $dbFD = $db->prepare("SELECT * FROM form_data WHERE user_id = ?");
                $dbFD->execute([$_SESSION['user_id']]);
                $fet = $dbFD->fetchAll(PDO::FETCH_ASSOC)[0];
                $form_id = $fet['id'];
                $_SESSION['form_id'] = $form_id;
                $dbL = $db->prepare("SELECT l.name FROM form_data_lang f
                                      LEFT JOIN languages l ON l.id = f.id_lang
                                      WHERE f.id_form = ?");
                $dbL->execute([$form_id]);
                $favoriteLanguagesSA = [];
                foreach ($dbL->fetchAll(PDO::FETCH_ASSOC) as $item) {
                    $favoriteLanguagesSA[] = $item['name'];
                }
                setValue('fullName', $fet['fullName']);
                setValue('phone', $fet['phone']);
                setValue('email', $fet['email']);
                setValue('birthday', date("Y-m-d", $fet['birthday']));
                setValue('gender', $fet['gender']);
                setValue('like_lang', $like_lang);
                setValue('biography', $fet['biography']);
                setValue('agreement', $fet['agreement']);
            } catch (PDOException $e) {
                print('Error : ' . $e->getMessage());
                exit();
            }
        } else {
            // Обработка ситуации, когда user_id нет в сессии
            echo "Ошибка: пользователь не авторизован.";
        }
    }

    include('form.php');
} else {
    $fullName = (!empty($_POST['fullName']) ? $_POST['fullName'] : '');
    $phone = (!empty($_POST['phone']) ? $_POST['phone'] : '');
    $email = (!empty($_POST['email']) ? $_POST['email'] : '');
    $birthday = (!empty($_POST['birthday']) ? $_POST['birthday'] : '');
    $gender = (!empty($_POST['gender']) ? $_POST['gender'] : '');
    $like_lang = (!empty($_POST['like_lang']) ? $_POST['like_lang'] : '');
    $biography = (!empty($_POST['biography']) ? $_POST['biography'] : '');
    $agreement = isset($_POST['agreement']) ? 1 : 0;

    if(isset($_POST['logout_form'])){
        deleteCookies('fullName', 1);
        deleteCookies('phone', 1);
        deleteCookies('email', 1);
        deleteCookies('birthday', 1);
        deleteCookies('gender', 1);
        deleteCookies('like_lang', 1);
        deleteCookies('biography', 1);
        deleteCookies('agreement', 1);
        session_destroy();
        header('Location: ./');
        exit();
    }

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

        if($cook == 'like_lang'){
            global $like_lang;
            $setVal = ($like_lang != '') ? implode(",", $like_lang) : '';
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
    if(!val_empty('like_lang', "Выберите хотя бы один язык", empty($like_lang))){
        $total = count($like_lang);
        val_empty('like_lang', "Выберите не больше 4 языков", $total > 4);
    }
    if(!val_empty('biography', "Заполните поле", empty($biography))){
        val_empty('biography', "Длина поля > 255 символов", strlen($biography) > 255);
    }
    val_empty('agreement', "Вы не приняли соглашение", empty($agreement));

    if (!$error) {
        if (!empty($_POST['save'])) {
            setcookie('fullName', $fullName, time() + 30 * 24 * 60 * 60); //сохраняем на месяц
            setcookie('phone', $phone, time() + 30 * 24 * 60 * 60); //сохраняем на месяц
            setcookie('email', $email, time() + 30 * 24 * 60 * 60); //сохраняем на месяц
            setcookie('birthday', $birthday, time() + 30 * 24 * 60 * 60); //сохраняем на месяц
            setcookie('gender', $gender, time() + 30 * 24 * 60 * 60); //сохраняем на месяц
            setcookie('like_lang', implode(",", $like_lang), time() + 30 * 24 * 60 * 60); //сохраняем на месяц
            setcookie('biography', $biography, time() + 30 * 24 * 60 * 60); //сохраняем на месяц
            setcookie('agreement', $agreement, time() + 30 * 24 * 60 * 60); //сохраняем на месяц
            setcookie('save', '1', time() + 60); //сохраняем на минуту
            header('Location: ./');
            exit();
        } else {
            connectDatabase();
            $login = substr(uniqid(), 0, 4).rand(10, 100);
            $password = rand(100, 1000).substr(uniqid(), 4, 10);
            setcookie('login', $login);
            setcookie('password', $password);
            $mpassword = md5($password);
            try {
                $stmt = $db->prepare("INSERT INTO users (login, password) VALUES (?, ?)");
                $stmt->execute([$login, $mpassword]);
                $user_id = $db->lastInsertId();
                $_SESSION['user_id'] = $user_id; // Добавил эту строку
                $dbFD = $db->prepare("INSERT INTO form_data (fullName, phone, email, birthday, gender, biography, agreement, user_id) VALUES (?,?,?,?,?,?,?,?)");
                $dbFD->execute([$fullName, $phone, $email, strtotime($birthday), $gender, $biography, $agreement, $user_id]);
                $form_id = $db->lastInsertId();
                $_SESSION['form_id'] = $form_id;
                if (!empty($like_lang)) {
                    foreach ($like_lang as $lang) {
                        $dbL = $db->prepare("INSERT INTO form_data_lang (id_form, id_lang) VALUES (?, (SELECT id FROM languages WHERE name = ?))");
                        $dbL->execute([$form_id, $lang]);
                    }
                }
                setcookie('save', '1', time() + 60); //сохраняем на минуту
                header('Location: ./');
                exit();
            } catch (PDOException $e) {
                print('Error : ' . $e->getMessage());
                exit();
            }
        }
    } else {
        include('form.php');
    }
}
?>
