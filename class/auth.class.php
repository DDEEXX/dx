<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 29.10.20
 * Time: 10:07
 */

require_once(dirname(__FILE__) . "/sqlDataBase.class.php");

class auth
{

    private static function hash($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function logout() {
        session_start();
        $id = $_SESSION['idUser'];
        $user = DB::getUserId($id);
        if (isset($user)) {
            DB::userLastActive($user, 0, true);
        }
        unset($_SESSION['idUser']); //удалятся переменная сессии
        SetCookie("idUser", "", time() - 1);
        SetCookie("password","", time() - 1);

//        SetCookie("idUser", ""); //удаляются cookie с логином
//        SetCookie("password", ""); //удаляются cookie с паролем
        //header('index.php'); //перенаправление на главную страницу сайта }
        session_destroy();
        header('Location: http://'.$_SERVER['HTTP_HOST'].'/'); //перенаправление на главную страницу сайта }
    }

    private static function lastAct($id) {
        $tm = time();
        $user = DB::getUserId($id);
        if (isset($user)) {
            DB::userLastActive($user, $tm);
        }
    }

    public static function login()
    {

        return true;

        ini_set ("session.use_trans_sid", true);
        session_start();

        if (isset($_SESSION['idUser'])) {   //если сессия есть

            if(isset($_COOKIE['idUser']) && isset($_COOKIE['password'])) { //если cookie есть, обновляется время их жизни и возвращается true

                SetCookie("idUser", "", time() - 360000, '/');
                SetCookie("password","", time() - 360000, '/');

                SetCookie("idUser", $_COOKIE['idUser'], time() + 50000, '/');
                SetCookie("password", $_COOKIE['password'], time() + 50000, '/');

                $id = $_SESSION['idUser'];
                self::lastAct($id);
                return true;
            }
            else //иначе добавляются cookie с логином и паролем, чтобы после перезапуска браузера сессия не слетала
            {

                $user = DB::getUserId($_SESSION['idUser']);

                if (!is_null($user)) { //если получены данные пользователя

                    setcookie ("idUser", $user['ID'], time()+50000, '/');
                    $hash_pass = self::hash($user['Password']);
                    setcookie ("password", $hash_pass, time() + 50000, '/');

                    $id = $_SESSION['idUser'];
                    self::lastAct($id);
                    return true;

                }
                else {
                    return false;
                }
            }
        }
        else {//если сессии нет, проверяется существование cookie. Если они существуют, проверяется их валидность по базе данных
            if(isset($_COOKIE['idUser']) && isset($_COOKIE['password'])) {//если куки существуют

                $user = DB::getUserId($_COOKIE['idUser']);
                if (password_verify($user['Password'], $_COOKIE['password']))  {//если логин и пароль нашлись в базе данных

                    $_SESSION['idUser'] = $user['ID']; //записываем в сесиию id
                    $id = $_SESSION['idUser'];

                    self::lastAct($id);
                    return true;
                }
                else {//если данные из cookie не подошли, эти куки удаляются

                    SetCookie("idUser", "", time() - 360000, '/');
                    SetCookie("password", "", time() - 360000, '/');
                    return false;

                }
            }
            else {//если куки не существуют
                return false;
            }
        }
    }

    public static function is_admin() {
        return true;
    }

    public static function enter() {

        $error = array(); //массив для ошибок

        if ($_POST['auth_password'] != "") {//если поля заполнены


            $password = $_POST['auth_password'];
            $user = DB::getUserPassword($password);

            if (isset($user)) { //если юзер существует в базе данных

                    //пишутся логин и хэшированный пароль в cookie, также создаётся переменная сессии
                    setcookie("idUser", $user['ID'], time() + 50000, '/');
                    $hash_pass = self::hash($user['Password']);
                    setcookie("password", $hash_pass, time() + 50000, '/');
                    $_SESSION['idUser'] = $user['ID'];   //записываем в сессию id пользователя

                    $id = $_SESSION['idUser'];
                    self::lastAct($id);
                    return $error;
            }
            else {//если такого пользователя не найдено в базе данных
                $error[] = "Неверный пароль";
                return $error;
            }
        }
        else {
            $error[] = "Пароль не введен!";
            return $error;
        }
    }

}