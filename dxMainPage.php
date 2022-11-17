<?php
include_once ('class/auth.class.php');
session_start();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <?php
        header('Content-type: text/html; charset=utf-8')
    ?>
    <title>DX HOME</title>
    <script src="js2/jquery.js"></script>
    <script src="js2/jquery.timers.js"></script>
    <script src="js2/jquery-ui.js"></script>

    <link rel="stylesheet" type="text/css" href="css2/style_mmenu.css">
    <link rel="stylesheet" type="text/css" href="css2/temes/dx/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="css2/reset.css">
    <!-- <link rel="stylesheet" type="text/css" href="/css2/text.css">  -->
    <link rel="stylesheet" type="text/css" href="css2/960_12_col.css">
    <!-- <link rel="stylesheet" type="text/css" href="/css2/style_960_b.css">  -->
    <link rel="stylesheet" type="text/css" href="css2/icon.css">
    <link rel="stylesheet" type="text/css" href="css2/style_weather.css">
    <link rel="stylesheet" type="text/css" href="css2/style.css">

    <!--suppress JSJQueryEfficiency -->
    <script>
        $(function () {

            $.ajaxSetup({cache: false});

            $("nav#main #main-menu").menu({
                icons: { submenu: "12345" }
            });

            $("button.upDown").button({
                icons: {
                    primary: "ui-icon-upDown"
                },
                text: false
            });

            $("button").button().click(function (event) {
                    event.preventDefault();
                });

            //подсвечиваем выбранный пункт меню
            $('nav#main #main-menu').find('li:first').addClass('ui-menu-item-selected'); // при загрузке страницы сразу даем класс первой ссылке, то есть индексной странице
            $('nav#main #main-menu').find('a').each(function () { // проходим по нужным нам ссылками
                var location = window.location.href; // переменная с адресом страницы
                var link = this.href; // переменная с url ссылки
                if (location === link) {
                    $('nav#main #main-menu').find('li:first').removeClass('ui-menu-item-selected'); // сначала удаляем класс с индексной страницы
                    $(this).parent().addClass('ui-menu-item-selected'); // добавляем класс
                }
            });

            //$('nav#main #main-menu').removeClass('ui-widget-content'); // при загрузке страницы сразу даем класс первой ссылке, то есть индексной странице

            $( "nav#main #main-menu" ).menu({
                blur: function( event, ui ) {
                    ui.item.removeClass('ui-menu-item-focus');
                },
                focus: function( event, ui ) {
                    ui.item.addClass('ui-menu-item-focus');
                }
            });


        });
    </script>

</head>

<body>

<?php

$UID = $_SESSION['idUser'];

if (!$UID) {
    include_once 'login.php';
}
else {
    ?>

    <div class="container_12">
        <div class="grid_1">
            <nav id="main">
                <ul id="main-menu">
                    <li class="ui-corner-all">
                        <a href="dxMainPage.php?p=home">
                            <span class="ui-icon ui-icon-mmhome">1</span>
                        </a>
                     </li>
                    <li class="ui-corner-all">
                        <a href="dxMainPage.php?p=weather">
                            <span class="ui-icon ui-icon-mmtemp">2</span>
                        </a>
                    </li>
                    <li class="ui-corner-all">
                        <a href="dxMainPage.php?p=light">
                            <span class="ui-icon ui-icon-mmbulb" title="Освещение">3</span>
                        </a>
                    </li>
                    <li class="ui-corner-all"><a href="dxMainPage.php?p=power">
                            <span class="ui-icon ui-icon-mmpower" title="Управление">4</span></a>
                    </li>
                    <li class="ui-corner-all"><a href="dxMainPage.php?p=heater"><span
                                    class="ui-icon ui-icon-mmheater"
                                    title="Климат и отопление">5</span></a>
                    </li>
                    <li class="ui-corner-all"><a href="dxMainPage.php?p=cam"><span
                                    class="ui-icon ui-icon-mmip_camera">6</span></a>
                    </li>
                    <li class="ui-corner-all"><a href="dxMainPage.php?p=n7"><span class="ui-icon ui-icon-mmkey">7</span></a>
                    </li>
                    <li class="ui-corner-all"><a href="dxMainPage.php?p=properties"><span
                                    class="ui-icon ui-icon-mmpref">8</span></a>
                    </li>
                </ul>
            </nav>
        </div>

        <?php
        include_once 'rgMode.php';
        include_once 'dxPages.php';
        ?>

    </div>

    <?php
}
?>
</body>
</html>
