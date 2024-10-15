<?php

function debug($data)
{
    echo '<pre>' . print_r($data, 1) . '</pre>';
}

function registration() : bool
{
    global $pdo;
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
    $pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_STRING);

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

