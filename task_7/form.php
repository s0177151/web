<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

if (isset($_SESSION['login'])) {
    header('Location: ./');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

} else {
    require('connection.php');
    $csrf_tokens = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    if (isset($_POST['auth']) && $_SESSION['csrf_token_login'] != $csrf_tokens) {
        $error = 'Не соответствие CSRF токена';
    } else {
        $login = checkInput($_POST['login']);
        $password = md5(checkInput($_POST['password']));
        try {
            $stmt = $db->prepare("SELECT id FROM users WHERE login = ? and password = ?");
            $stmt->execute([$login, $password]);
            $its = $stmt->rowCount();
            if ($its) {
                $uid = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['id'];
                $_SESSION['login'] = $_POST['login'];
                $_SESSION['user_id'] = $uid;
                header('Location: ./');
            } else {
                $error = 'Неверный логин или пароль';
            }
        } catch (PDOException $e) {
            print('Error : ' . $e->getMessage());
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Задание 7</title>
</head>

<body class="bg-gray-900 h-screen flex items-center justify-center">
<div class="flex flex-col gap-2 border-2 border-white/10 rounded-xl text-white p-4 w-1/4">
    <form class="flex flex-col gap-2" action="" method="post">
        <?php
        $csrf_token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrf_token;
        ?>
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

        <h3 class="text-3xl font-semibold text-center">Форма</h3>
        <div class="message text-red-500"><?php echo $error; ?></div>
        <div class="message"><?php if (isset($messages['success'])) echo $messages['success']; ?></div>
        <div class="message text-blue-500"><?php if (isset($messages['info'])) echo $messages['info']; ?></div>
        <div class="message text-red-500"><?php if (isset($messages['error'])) echo $messages['error']; ?></div>

        <div class="flex flex-col gap-1">
            <p class="text-xs text-white/20">Фамилия, имя и отчество</p>
            <input class="rounded-lg p-1 border-2 border-white/10 bg-gray-900 text-sm w-full <?php echo ($errors['fullName'] != NULL) ? 'border-red-500' : ''; ?>" value="<?php echo $values['fullName']; ?>" type="text" name="fullName" placeholder="Введите фамилию, имя и отчество...">
            <div class="text-red-500"><?php echo $messages['fullName']?></div>
        </div>

        <div class="flex flex-col gap-1">
            <p class="text-xs text-white/20">Номер мобильного телефона</p>
            <input class="rounded-lg p-1 border-2 border-white/10 bg-gray-900 text-sm w-full <?php echo ($errors['phone'] != NULL) ? 'border-red-500' : ''; ?>" value="<?php echo $values['phone']; ?>" type="tel" name="phone" placeholder="Введите номер телефона...">
            <div class="text-red-500"><?php echo $messages['phone']?></div>
        </div>

        <div class="flex flex-col gap-1">
            <p class="text-xs text-white/20">Адрес электронной почты</p>
            <input class="rounded-lg p-1 border-2 border-white/10 bg-gray-900 text-sm w-full <?php echo ($errors['email'] != NULL) ? 'border-red-500' : ''; ?>" value="<?php echo $values['email']; ?>" type="email" name="email" placeholder="Введите адрес электронной почты...">
            <div class="text-red-500"><?php echo $messages['email']?></div>
        </div>

        <div class="flex flex-col gap-1">
            <p class="text-xs text-white/20">Дата рождения</p>
            <input class="rounded-lg p-1 border-2 border-white/10 bg-gray-900 text-sm w-full <?php echo ($errors['birthday'] != NULL) ? 'border-red-500' : ''; ?>" value="<?php if (strtotime($values['birthday']) != '') echo $values['birthday']; ?>" type="date" name="birthday">
            <div class="text-red-500"><?php echo $messages['birthday']?></div>
        </div>

        <div class="flex flex-col gap-1">
            <div class="text-xs text-white/20">Пол</div>
            <div class="flex gap-2 text-sm">
                <label>
                    <input type="radio" name="gender" value="male" <?php if($values['gender'] == 'male') echo 'checked'; ?>>
                    <span class="<?php echo ($errors['gender'] != NULL) ? 'text-red-500' : ''; ?>">Мужской</span>
                </label>
                <label>
                    <input type="radio" name="gender" value="female" <?php if($values['gender'] == 'female') echo 'checked'; ?>>
                    <span class="<?php echo ($errors['gender'] != NULL) ? 'text-red-500' : ''; ?>">Женский</span>
                </label>
            </div>
            <div class="text-red-500"><?php echo $messages['gender']?></div>
        </div>

        <div class="flex flex-col gap-1">
            <p class="text-xs text-white/20">Любимый язык программирования</p>
            <select class="rounded-lg p-1 border-2 border-white/10 bg-gray-900 text-sm w-full <?php echo ($errors['like_lang'] != NULL) ? 'border-red-500' : ''; ?>" name="like_lang[]" id="like_lang" multiple="multiple">
                <option disabled selected>Выберите языки</option>
                <option value="Pascal" <?php echo (in_array('Pascal', $like_langsa)) ? 'selected' : ''; ?>>Pascal</option>
                <option value="C" <?php echo (in_array('C', $like_langsa)) ? 'selected' : ''; ?>>C</option>
                <option value="C++" <?php echo (in_array('C++', $like_langsa)) ? 'selected' : ''; ?>>C++</option>
                <option value="JavaScript" <?php echo (in_array('JavaScript', $like_langsa)) ? 'selected' : ''; ?>>JavaScript</option>
                <option value="PHP" <?php echo (in_array('PHP', $like_langsa)) ? 'selected' : ''; ?>>PHP</option>
                <option value="Python" <?php echo (in_array('Python', $like_langsa)) ? 'selected' : ''; ?>>Python</option>
                <option value="Java" <?php echo (in_array('Java', $like_langsa)) ? 'selected' : ''; ?>>Java</option>
                <option value="Haskel" <?php echo (in_array('Haskel', $like_langsa)) ? 'selected' : ''; ?>>Haskel</option>
                <option value="Clojure" <?php echo (in_array('Clojure', $like_langsa)) ? 'selected' : ''; ?>>Clojure</option>
                <option value="Prolog" <?php echo (in_array('Prolog', $like_langsa)) ? 'selected' : ''; ?>>Prolog</option>
                <option value="Scala" <?php echo (in_array('Scala', $like_langsa)) ? 'selected' : ''; ?>>Scala</option>
            </select>
            <div class="text-red-500"><?php echo $messages['like_lang']?></div>
        </div>

        <div class="flex flex-col gap-1">
            <p class="text-xs text-white/20">Биография</p>
            <textarea name="biography" placeholder="Расскажите о себе..." class="rounded-lg p-1 border-2 border-white/10 bg-gray-900 text-sm w-full <?php echo ($errors['biography'] != NULL) ? 'border-red-500' : ''; ?>"><?php echo checkInput_decode($values['biography']); ?></textarea>
            <div class="text-red-500"><?php echo $messages['biography']?></div>
        </div>

        <div class="flex gap-1">
            <input type="checkbox" name="agreement" id="agreement" <?php echo ($values['agreement'] != NULL) ? 'checked' : ''; ?>>
            <label for="agreement" class="text-sm <?php echo ($errors['agreement'] != NULL) ? 'text-red-500' : ''; ?>">С контрактом ознакомлен (а)</label>
            <div class="text-red-500"><?php echo $messages['agreement']?></div>
        </div>

        <?php
        if($log) {
            echo '<button type="submit" class="p-2 rounded-lg bg-green-600 text-sm hover:bg-opacity-90 transition-colors hover:shadow-lg">Изменить</button>';
        } else {
            echo '<button type="submit" class="p-2 rounded-lg bg-green-600 text-sm hover:bg-opacity-90 transition-colors hover:shadow-lg">Отправить</button>';
        }
        ?>
    </form>
    <div class="mt-2 flex justify-between items-center w-full">
        <p class="text-sm opacity-50">Войдите в аккаунт</p>
        <?php
        if($log) {
            echo '<form method="post"><button type="submit" class="p-2 rounded-lg bg-red-600 text-sm hover:bg-opacity-90 transition-colors hover:shadow-lg" name="logout_form">Выйти</button></form>';
        } else {
            echo '<a href="login.php" class="p-2 rounded-lg bg-gray-600 text-sm hover:bg-opacity-90 transition-colors hover:shadow-lg" name="logout_form">Войти</a>';
        }
        ?>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(){
        setTimeout(function(){
            let el = document.querySelector('input[name=gender][checked]');
            if(el != null)
                el.click();
        }, 200)
    });
</script>
</body>

</html>
