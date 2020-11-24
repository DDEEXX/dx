<?php

session_start();

include_once ('class/auth.class.php');

if($_GET['action'] == "out") { //если передана переменная action, «разавторизируем» пользователя
    auth::logout();
}
else {
    if (auth::login()) //вызываем функцию login, которая определяет, авторизирован пользователь или нет

    {
        $UID = $_SESSION['idUser']; //если пользователь авторизирован, присваиваем переменной $UID его id
        $admin = auth::is_admin($UID); //определяем, админ ли пользователь
    }
    else //если пользователь не авторизирован, проверяем, была ли нажата кнопка входа на сайт
    {
        if (isset($_POST['log_in'])) {
            $error = auth::enter(); //функция входа на сайт

            if (count($error) == 0) //если ошибки отсутствуют, авторизируем пользователя
            {
                $UID = $_SESSION['idUser'];
                $admin = auth::is_admin($UID);
            }
        }
    }
}
include_once ("dxMainPage.php");

