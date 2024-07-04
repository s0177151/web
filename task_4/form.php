<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Задание 4</title>
</head>
<body class="bg-gray-900 h-screen flex items-center justify-center">
<div class="flex flex-col gap-2 border-2 border-white/10 rounded-xl text-white p-4 w-1/4">
    <form method="post" class="flex flex-col gap-2">
        <h3 class="text-3xl font-semibold text-center">Форма</h3>
        <div class="message"><?php if (isset($messages["success"])) {
                echo $messages["success"];
            } ?></div>
        <div class="flex flex-col gap-1"><p class="text-xs text-white/20">Фамилия, имя и отчество</p>
            <input class="rounded-lg p-1 border-2 border-white/10 bg-gray-900 text-sm <?php echo $errors[
            "fullName"
            ] != null
                ? "border border-red-600 rounded-lg"
                : ""; ?>"
                   value="<?php echo $values[
                   "fullName"
                   ]; ?>" type="text" name="fullName" placeholder="Введите фамилию, имя и отчество...">
            <div><?php echo $messages["fullName"]; ?></div>
        </div>
        <div class="flex flex-col gap-1"><p class="text-xs text-white/20">Номер мобильного телефона</p>
            <input class="rounded-lg p-1 border-2 border-white/10 bg-gray-900 text-sm <?php echo $errors[
            "phone"
            ] != null
                ? "border border-red-600 rounded-lg"
                : ""; ?>"
                   value="<?php echo $values[
                   "phone"
                   ]; ?>" type="tel" name="phone" placeholder="Введите номер телефона...">
            <div><?php echo $messages["phone"]; ?></div>
        </div>
        <div class="flex flex-col gap-1"><p class="text-xs text-white/20">Адрес электронной почты</p>
            <input class="rounded-lg p-1 border-2 border-white/10 bg-gray-900 text-sm <?php echo $errors[
            "email"
            ] != null
                ? "border border-red-600 rounded-lg"
                : ""; ?>"
                   value="<?php echo $values[
                   "email"
                   ]; ?>" type="email" name="email" placeholder="Введите адрес электронной почты...">
            <div><?php echo $messages["email"]; ?></div>
        </div>
        <div class="flex flex-col gap-1"><p class="text-xs text-white/20">Дата рождения</p>
            <input class="rounded-lg p-1 border-2 border-white/10 bg-gray-900 text-sm <?php echo $errors[
            "birthday"
            ] != null
                ? "border border-red-600 rounded-lg"
                : ""; ?>"
                   value="<?php if ($values["birthday"] > 100000) {
                       echo $values["birthday"];
                   } ?>" type="date"
                   name="birthday">
            <div><?php echo $messages["birthday"]; ?></div>
        </div>
        <div class="ent">
            <p class="text-xs text-white/20">Пол</p>
            <div class="flex gap-2 text-sm"><label>
                    <input type="radio" name="gender"
                           value="male" <?php if ($values["gender"] == "male") {
                        echo "checked";
                    } ?>>
                    <span class="<?php echo $errors["gender"] != null
                        ? "colred"
                        : ""; ?>">Мужской</span>
                </label>
                <br>
                <label>
                    <input type="radio" name="gender"
                           value="female" <?php if (
                        $values["gender"] == "female"
                    ) {
                        echo "checked";
                    } ?>>
                    <span class="<?php echo $errors["gender"] != null
                        ? "colred"
                        : ""; ?>">Женский</span>
                </label>
                <div><?php echo $messages["gender"]; ?></div>
            </div>
        </div>
        <div class="ent">
            <select class="rounded-lg p-1 border-2 border-white/10 bg-gray-900 w-full text-sm <?php echo $errors[
            "favoriteLanguages"
            ] != null
                ? "border border-red-600 rounded-lg"
                : ""; ?>"
                    name="favoriteLanguages[]"
                    id="favoriteLanguages" multiple="multiple">
                <option disabled class="text-xs">Любимый язык программирования</option>
                <option value="Pascal" <?php echo in_array(
                    "Pascal",
                    $favoriteLanguagesSA
                )
                    ? "selected"
                    : ""; ?>>Pascal
                </option>
                <option value="C" <?php echo in_array("C", $favoriteLanguagesSA)
                    ? "selected"
                    : ""; ?>>C</option>
                <option value="C++" <?php echo in_array("C++", $favoriteLanguagesSA)
                    ? "selected"
                    : ""; ?>>C++</option>
                <option value="JavaScript" <?php echo in_array(
                    "JavaScript",
                    $favoriteLanguagesSA
                )
                    ? "selected"
                    : ""; ?>>
                    JavaScript
                </option>
                <option value="PHP" <?php echo in_array("PHP", $favoriteLanguagesSA)
                    ? "selected"
                    : ""; ?>>PHP</option>
                <option value="Python" <?php echo in_array(
                    "Python",
                    $favoriteLanguagesSA
                )
                    ? "selected"
                    : ""; ?>>Python
                </option>
                <option value="Java" <?php echo in_array("Java", $favoriteLanguagesSA)
                    ? "selected"
                    : ""; ?>>Java</option>
                <option value="Haskel" <?php echo in_array(
                    "Haskel",
                    $favoriteLanguagesSA
                )
                    ? "selected"
                    : ""; ?>>Haskel
                </option>
                <option value="Clojure" <?php echo in_array(
                    "Clojure",
                    $favoriteLanguagesSA
                )
                    ? "selected"
                    : ""; ?>>Clojure
                </option>
                <option value="Prolog" <?php echo in_array(
                    "Prolog",
                    $favoriteLanguagesSA
                )
                    ? "selected"
                    : ""; ?>>Prolog
                </option>
                <option value="Scala" <?php echo in_array("Scala", $favoriteLanguagesSA)
                    ? "selected"
                    : ""; ?>>Scala</option>
            </select>
            <div><?php echo $messages["favoriteLanguages"]; ?></div>
        </div>
        <div>
            <textarea name="biography" placeholder="Биография"
                      class="<?php echo $errors["biography"] != null
                          ? "border border-red-600 rounded-lg"
                          : ""; ?> rounded-lg p-1 border-2 border-white/10 bg-gray-900 w-full text-sm"><?php echo $values[
                "biography"
                ]; ?></textarea>
            <div><?php echo $messages["biography"]; ?></div>
        </div>
        <div>
            <input type="checkbox" name="agreement"
                   id="agreement" <?php echo $values["agreement"] != null
                ? "checked"
                : ""; ?>>
            <label for="agreement" class="<?php echo $errors["agreement"] !=
            null
                ? "colred"
                : ""; ?> text-sm">С контрактом
                ознакомлен</label>
            <div><?php echo $messages["agreement"]; ?></div>
        </div>
        <button type="submit" class="p-2 rounded-lg bg-green-600 text-sm hover:bg-opacity-90 transition-colors hover:shadow-lg">Отправить</button>
    </form>
</div>
</body>
</html>