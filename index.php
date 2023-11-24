<?php

//session_start();

include_once ('class/auth.class.php');

$UID = null;

$ip_client = auth::getRealIP();
if (auth::net_match($ip_client)) {
    $UID = 1;
}
else {
    if (isset($_GET['action']) && $_GET['action'] == 'out') { //если передана переменная action, выход
        auth::logout();
    } else {
        if (auth::login()) //вызываем функцию login, которая определяет, авторизирован пользователь или нет
        {
            $UID = isset($_SESSION['idUser']) ? $_SESSION['idUser'] : null; //если пользователь авторизирован, присваиваем переменной $UID его id
            //$admin = auth::is_admin($UID); //определяем, админ ли пользователь
        } else //если пользователь не авторизирован, проверяем, была ли нажата кнопка входа на сайт
        {
            if (isset($_POST['log_in'])) {
                $error = auth::enter(); //функция входа на сайт

                if (count($error) == 0) //если ошибки отсутствуют, авторизуем пользователя
                {
                    $UID = isset($_SESSION['idUser']) ? $_SESSION['idUser'] : null;
                    //$admin = auth::is_admin($UID);
                }
            }
        }
    }
}

include_once ('dxMainPage.php');

