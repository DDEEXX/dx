<?php
//include_once('class/auth.class.php');
//session_start();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php
    //header('Content-type: text/html; charset=utf-8')
    ?>
    <title>DX HOME</title>
    <script type="text/javascript" src="js2/jquery.js"></script>
    <script src="js2/jquery.timers.js"></script>
    <script src="js2/jquery-ui.js"></script>
    <link rel="stylesheet" type="text/css" href="css2/temes/dx/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="css2/reset.css">
    <link rel="stylesheet" type="text/css" href="css2/style_mmenu.css">
    <!-- <link rel="stylesheet" type="text/css" href="/css2/text.css">
    <link rel="stylesheet" type="text/css" href="css2/960_12_col.css">
    <link rel="stylesheet" type="text/css" href="css2/860_3_col.css">
    <link rel="stylesheet" type="text/css" href="/css2/style_960_b.css">  -->
    <link rel="stylesheet" type="text/css" href="css2/grid.css">
    <link rel="stylesheet" type="text/css" href="css2/icon.css">
    <link rel="stylesheet" type="text/css" href="css2/style.css">
    <link href="js2/jPlayer/skin/dxhome/css/jplayer.blue.monday.min.css" rel="stylesheet" type="text/css" />

    <script>
        $(function () {
            $.ajaxSetup({cache: false});
            $("#mainMenu").menu({
                blur: function (event, ui) {
                    ui.item.removeClass('ui-menu-item-focus');
                },
                focus: function (event, ui) {
                    ui.item.addClass('ui-menu-item-focus');
                }
                //icons: {submenu: "12345"}
            });
            //подсвечиваем выбранный пункт меню
            $('#mainMenu').find('li:first').addClass('ui-menu-item-selected'); // при загрузке страницы сразу даем класс первой ссылке, то есть индексной странице
            const location = window.location.href; // переменная с адресом текущей страницы
            $('#mainMenu').find('a').each(function () { // проходим по нужным нам ссылками
                const link = this.href; // переменная с url ссылки пункта меню
                if (location.startsWith(link)) {
                    $('#mainMenu').find('li:first').removeClass('ui-menu-item-selected'); // сначала удаляем класс с индексной страницы
                    $(this).parent().addClass('ui-menu-item-selected'); // добавляем класс
                }
            });
        });
    </script>

</head>

<body>

<?php

$UID = isset($_SESSION['idUser']) ? $_SESSION['idUser'] : false;

if (!$UID) {
    include_once 'login.php';
} else {
    ?>

    <div class="container_13">
        <div class="grid_1">
            <ul id="mainMenu" class="navigation">
                <li>
                    <a href="index.php?p=home">
                        <span class="ui-icon ui-icon-mmhome" title="Главная страница">1</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?p=weather">
                        <span class="ui-icon ui-icon-mmtemp" title="Погода и температура">2</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?p=light">
                        <span class="ui-icon ui-icon-mmbulb" title="Освещение">3</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?p=power">
                        <span class="ui-icon ui-icon-mmpower" title="Управление">4</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?p=heater">
                        <span class="ui-icon ui-icon-mmheater" title="Климат и отопление">5</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?p=cam">
                        <span class="ui-icon ui-icon-mmip_camera"  title="Видео наблюдение">6</span></a>
                </li>
                <li>
                    <a href="index.php?p=n7">
                        <span class="ui-icon ui-icon-mmkey" title="Доступ">7</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?p=setup">
                        <span class="ui-icon ui-icon-mm_setup" title="Настройки">8</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?p=properties">
                        <span class="ui-icon ui-icon-mmpref" title="Прочее">9</span>
                    </a>
                </li>
            </ul>
        </div>

        <?php
        //include_once 'rgMode.php';
        include_once 'dxPages.php';
        ?>

    </div>

    <?php
}
?>
</body>
</html>
