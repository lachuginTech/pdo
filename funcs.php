<?php

function debug($data)
{
    echo '<pre>' . print_r($data, 1) . '</pre>';
}

function registration() : bool
{
    global $pdo;
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_SPECIAL_CHARS);
    $pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($login) || empty($pass)) {
        $_SESSION['errors'] = 'Заполните все поля';
        return false;
    }

    try {
        $pdo->beginTransaction();

        $res = $pdo->prepare("SELECT COUNT(*) FROM `users` WHERE `login` = ?");
        $res->execute([$login]);
        if ($res->fetchColumn() > 0) {
            $_SESSION['errors'] = 'Пользователь с таким именем уже существует';
            $pdo->rollBack();
            return false;
        }

        $passHash = password_hash($pass, PASSWORD_DEFAULT);
        $res = $pdo->prepare("INSERT INTO `users` (`login`, `pass`) VALUES (?, ?)");
        if ($res->execute([$login, $passHash])) {
            $pdo->commit();
            $_SESSION['success'] = 'Регистрация прошла успешно';
            return true;
        } else {
            $pdo->rollBack();
            $_SESSION['errors'] = 'Произошла ошибка при регистрации';
            return false;
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        $_SESSION['errors'] = 'Произошла ошибка при регистрации';
        return false;
    }
}


function auth() : bool
{
    global $pdo;
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_SPECIAL_CHARS);
    $pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($login) || empty($pass)) {
        $_SESSION['errors'] = 'Заполните все поля';
        return false;
    }



    try {
        $res = $pdo->prepare("SELECT * FROM `users` WHERE `login` = ?");
        $res->execute([$login]);
        if (!$user = $res->fetch()) {
            $_SESSION['errors'] = 'Пользователь не найден';
            return false;
        }


        if (!password_verify($pass, $user['pass'])) {
            $_SESSION['errors'] = 'Неверный пароль';
            return false;
        }
        $_SESSION['success'] = "Вы вошли в аккаунт: $login";
        $_SESSION['user']['name'] = $user['login'];
        $_SESSION['user']['id'] = $user['id'];
        return true;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['errors'] = 'Произошла ошибка при авторизации';
        return false;
    }
}

