<?php

function debug($data)
{
    echo '<pre>' . print_r($data, 1) . '</pre>';
}

function registration() : bool
{
    global $pdo;
    $login = !empty($_POST['login']) ? trim($_POST['login']) : '';
    $pass = !empty($_POST['pass']) ? trim($_POST['pass']) : '';

    if (empty($login) || empty($pass) ) {
        $_SESSION['error'] = 'Заполните все поля';
        return false;
    }

    $res = $pdo->prepare("SELECT COUNT(*) FROM `users` WHERE `login` = ?");
    $res->execute([$login]);
    if ($res->fetchColumn() > 0) {
        $_SESSION['error'] = 'Пользователь с таким именем уже существует';
        return false;
    }
}
