<?php

require('connection.php');

/**
 * Задача 6. Реализовать вход администратора с использованием
 * HTTP-авторизации для просмотра и удаления результатов.
 **/

// Пример HTTP-аутентификации.
// PHP хранит логин и пароль в суперглобальном массиве $_SERVER.
// Подробнее см. стр. 26 и 99 в учебном пособии Веб-программирование и веб-сервисы.

$haveAdmin = checkAdmin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

if (!$haveAdmin) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="My site"');
    print('<h1 class="text-xl font-bold text-red-600">401 Требуется авторизация</h1>');
    exit();
}

// *********
// Здесь нужно прочитать отправленные ранее пользователями данные и вывести в таблицу.
// Реализовать просмотр и удаление всех данных.
// *********
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script
  src="https://code.jquery.com/jquery-3.6.3.js"
  integrity="sha256-nQLuAZGRRcILA+6dMBOvcRh5Pe310sBpanc6+QBmyVM="
  crossorigin="anonymous">
</script>
    <title>Задание 6 (админка)</title>
</head>

<body class="bg-gray-100 text-gray-900">
<header class="bg-blue-500 text-white p-4 flex justify-between">
    <div><a href="#data" class="hover:underline">Информация</a></div>
    <div><a href="#analize" class="hover:underline">Статистика</a></div>
</header>

<div class="container mx-auto mt-8">
    <table id="data" class="min-w-full bg-white border border-gray-200">
        <thead>
        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
            <th class="py-3 px-4">id</th>
            <th class="py-3 px-4">ФИО</th>
            <th class="py-3 px-4">Телефон</th>
            <th class="py-3 px-4">Почта</th>
            <th class="py-3 px-4">День рождения</th>
            <th class="py-3 px-4">Пол</th>
            <th class="py-3 px-4">Биография</th>
            <th class="py-3 px-4">ЯП</th>
            <th class="py-3 px-4"></th>
            <th class="py-3 px-4"></th>
        </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
        <?php
        $dbFD = $db->query("SELECT * FROM form_data ORDER BY id DESC");
        while ($row = $dbFD->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr class="border-b border-gray-200 hover:bg-gray-100" data-id=' . $row['id'] . '><form action="./removeForm.php?id='. $row['id'] . '">
            
                            <td class="py-3 px-4">' . $row['id'] . $row['user_id'] . '</td>
                            <td class="py-3 px-4">' . $row['fullName'] . '</td>
                            <td class="py-3 px-4">' . $row['phone'] . '</td>
                            <td class="py-3 px-4">' . $row['email'] . '</td>
                            <td class="py-3 px-4">' . date("d.m.Y", $row['birthday']) . '</td>
                            <td class="py-3 px-4">' . (($row['gender'] == "male") ? "Мужской" : "Женский") . '</td>
                            <td class="py-3 px-4 whitespace-normal">' . $row['biography'] . '</td>
                            <td class="py-3 px-4">';
            $dbl = $db->prepare("SELECT * FROM form_data_lang fd
                                        LEFT JOIN languages l ON l.id = fd.id_lang
                                        WHERE id_form = ?");
            $dbl->execute([$row['id']]);
            while ($row1 = $dbl->fetch(PDO::FETCH_ASSOC)) {
                echo $row1['name'] . '<br>';
            }
            echo '</td>
                            <td class="py-3 px-4"><a href="./index.php?uid=' . $row['user_id'] . '" target="_blank" class="text-blue-500 hover:underline">Редактировать</a></td>
                            <td class="py-3 px-4"><button class="remove bg-red-500 text-white py-1 px-3 rounded hover:bg-red-700">Удалить</button></td></form>
                        </tr>';
        }
        ?>
        </tbody>
    </table>

    <table class="analize mt-8 min-w-full bg-white border border-gray-200" id="analize">
        <thead>
        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
            <th class="py-3 px-4">ЯП</th>
            <th class="py-3 px-4">Кол-во пользователей</th>
        </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
        <?php
        $qu = $db->query("SELECT l.id, l.name, COUNT(id_form) as count FROM languages l 
                                LEFT JOIN form_data_lang fd ON fd.id_lang = l.id
                                GROUP by l.id");
        while ($row = $qu->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-4">' . $row['name'] . '</td>
                            <td class="py-3 px-4">' . $row['count'] . '</td>
                          </tr>';
        }
        ?>
        </tbody>
    </table>
</div>

<script src="./core.js"></script>
</body>

</html>
