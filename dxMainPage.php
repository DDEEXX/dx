<!DOCTYPE html>
<html lang="ru">
<head>
    <?php header('Content-type: text/html; charset=utf-8') ?>
    <title>DX HOME</title>
    <script src="js2/jquery.js"></script>
    <script src="js2/jquery.timers.js"></script>
    <script src="js2/jquery-ui.js"></script>

    <link rel="stylesheet" type="text/css" href="css2/temes/dx/jquery-ui.css">

    <link rel="stylesheet" type="text/css" href="css2/reset.css">
    <!-- <link rel="stylesheet" type="text/css" href="/css2/text.css">  -->
    <link rel="stylesheet" type="text/css" href="css2/960_12_col.css">
    <link rel="stylesheet" type="text/css" href="css2/style_mmenu.css">
    <!-- <link rel="stylesheet" type="text/css" href="/css2/style_960_b.css">  -->
    <link rel="stylesheet" type="text/css" href="css2/style.css">
    <link rel="stylesheet" type="text/css" href="css2/icon.css">

    <!--suppress JSJQueryEfficiency -->
    <script>
        $(function () {

            $.ajaxSetup({cache: false});

            $("#mmenu").menu();

            $("button.upDown").button({
                icons: {
                    primary: "ui-icon-upDown"
                },
                text: false
            });

            $("button").button()
                .click(function (event) {
                    event.preventDefault();
                });

            //подсвечиваем выбранный пунк меню
            $('#mmenu').find('li:first').addClass('ui-state-selected'); // при загрузке страницы сразу даем класс первой ссылке, то есть индексной странице
            $('#mmenu').find('a').each(function () { // проходим по нужным нам ссылками
                var location = window.location.href; // переменная с адресом страницы
                var link = this.href; // переменная с url ссылки
                if (location === link) {
                    $('#mmenu').find('li:first').removeClass('ui-state-selected'); // сначала удаляем класс с индексной страницы
                    $(this).parent().addClass('ui-state-selected'); // добавляем класс
                }
            });

            $('#mmenu').removeClass('ui-widget-content'); // при загрузке страницы сразу даем класс первой ссылке, то есть индексной странице

        });
    </script>

</head>

<body>
<div class="container_12">
    <div class="grid_1">
        <ul id="mmenu" class="ui-corner-all">
            <li class="ui-corner-all"><a href="dxMainPage.php?p=home"><span class="ui-icon ui-icon-mmhome"
                                                                            title="Основная">1</span></a></li>
            <li class="ui-corner-all"><a href="dxMainPage.php?p=temp"><span class="ui-icon ui-icon-mmtemp"
                                                                            title="Температура">2</span></a></li>
            <li class="ui-corner-all"><a href="dxMainPage.php?p=light"><span class="ui-icon ui-icon-mmbulb"
                                                                             title="Освещение">3</span></a></li>
            <li class="ui-corner-all"><a href="dxMainPage.php?p=power"><span
                            class="ui-icon ui-icon-mmpower">4</span></a></li>
            <li class="ui-corner-all"><a href="dxMainPage.php?p=n5"><span class="ui-icon ui-icon-mmheater">5</span></a>
            </li>
            <li class="ui-corner-all"><a href="dxMainPage.php?p=cam"><span class="ui-icon ui-icon-mmip_camera">6</span></a>
            </li>
            <li class="ui-corner-all"><a href="dxMainPage.php?p=n7"><span class="ui-icon ui-icon-mmkey">7</span></a>
            </li>
            <li class="ui-corner-all"><a href="dxMainPage.php?p=n8"><span class="ui-icon ui-icon-mmpref">8</span></a>
            </li>
        </ul>
    </div>

    <?php
    include_once 'dxPages.php';
    ?>

</div>
</body>
</html>
