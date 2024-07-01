<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="libs/bootstrap-4.0.0-dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #7be3f5; 
        }
        .pform {
            background-color: #c5e1ee; 
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        header {
            background-color: #7dd6ff; 
            padding: 10px 0;
            text-align: center;
        }
        footer {
            background-color: #7dd6ff; 
            padding: 10px 0;
            text-align: center;
        }
    </style>
    <link rel="stylesheet" href="style8.css">
    <script src="libs/jquery-3.4.1.min.js"></script>
    <title>Task</title>
</head>
<body>
    <header>
        <h1>Веб</h1>
    </header>
    <div class="pform">
        <form action="" method="post">
            <h3>Форма</h3>
            <div>
                <input class="w100" type="text" name="fio" placeholder="ФИО">
            </div>
            <div>
                <input class="w100" type="tel" name="phone" placeholder="Телефон">
            </div>
            <div>
                <input class="w100" type="email" name="email" placeholder="email">
            </div>
            <div>
                <input class="w100" type="date" name="birthday">
            </div>
            <div class="ent">
                <div>Пол:</div>
                <label>
                    <input type="radio" name="gender" value="male">
                    Мужской
                </label>
                <br>
                <label>
                    <input type="radio" name="gender" value="female">
                    Женский
                </label>
            </div>
            <div class="ent">
                <select class="w100" name="like_lang[]" id="like_lang" multiple="multiple">
                    <option disabled selected>Любимый язык программирования</option>
                    <option value="Pascal">Pascal</option>
                    <option value="C">C</option>
                    <option value="C++">C++</option>
                    <option value="JavaScript">JavaScript</option>
                    <option value="PHP">PHP</option>
                    <option value="Python">Python</option>
                    <option value="Java">Java</option>
                    <option value="Haskel">Haskel</option>
                    <option value="Clojure">Clojure</option>
                    <option value="Prolog">Prolog</option>
                    <option value="Scala">Scala</option>
                </select>
            </div>
            <div>
                <textarea name="biography" placeholder="Биография"></textarea>
            </div>
            <div>
                <input type="checkbox" name="oznakomlen" id="oznakomlen">
                <label for="oznakomlen">С контрактом ознакомлен (а)</label>
            </div>
            <button type="submit">Отправить</button>
        </form>
  </div>
  <footer>
    <p>Рыдзелев</p>
</footer>
</body>
</html>