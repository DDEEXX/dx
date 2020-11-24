<div class="container_12">

    <script>
        $(function () {

            var $auth_p = $('#auth_p'),
                $auth_password = $('#auth_password');

            $auth_password.val("");

            $('.alarm_button').button().click(function () {

                var $this = $(this),
                    character = $this.attr("letter");

                if ($this.hasClass('alarm_button_delete')) {
                    var html = $auth_p.html(),
                        value = $auth_password.val();
                    $auth_p.html(html.substr(0, html.length - 2));
                    $auth_password.val(value.substr(0, value.length - 1));
                    return false;
                }

                 if ($this.hasClass('alarm_button_ok')) {
                     // var val = $auth_password.val();
                     // $.post("index.php", {log_in : "true", password : $auth_password.val()});
                     // location.reload(true);
                     return true;
                 }

                if ($auth_password.val().length < 5) {
                    $auth_p.html($auth_p.html() + '* ');
                    $auth_password.val($auth_password.val() + character);
                }

            });

        });
    </script>

    <div id="page_alarm" class="grid_11">
        <div class="grid_11 alpha ui-corner-all ui-widget-header" style="margin-top: 5px">
            <h2 style="margin-left:5px;font-size:150%;">Введите код для доступа</h2>
        </div>
        <div class="clear"></div>
        <div class="grid_11 alpha">
            <div class="grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:90px;border:0">
            </div>
            <div class="grid_9 ui-corner-all ui-state-default" style="margin-top:5px;height:90px;width:698px">
                <h2 id="auth_p" style="font-size:80px;text-align:center"></h2>
            </div>
            <div class="grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:90px;border:0">
            </div>
            <div class="clear"></div>

            <div class="grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <button class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="1">
                <h2 style="font-size:110px">1</h2>
            </button>
            <button class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="2">
                <h2 style="font-size:110px">2</h2>
            </button>
            <button class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="3">
                <h2 style="font-size:110px">3</h2>
            </button>
            <div class="grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <div class="clear"></div>

            <div class="grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <button class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="4">
                <h2 style="font-size:110px">4</h2>
            </button>
            <button class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="5">
                <h2 style="font-size:110px">5</h2>
            </button>
            <button class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="6">
                <h2 style="font-size:110px">6</h2>
            </button>
            <div class="grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <div class="clear"></div>

            <div class="grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <button class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="7">
                <h2 style="font-size:110px">7</h2>
            </button>
            <button class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="8">
                <h2 style="font-size:110px">8</h2>
            </button>
            <button class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="9">
                <h2 style="font-size:110px">9</h2>
            </button>
            <div class="grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <div class="clear"></div>

            <div class="grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <button class="grid_3 alarm_button alarm_button_delete ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px">
                <h2 style="font-size:110px;color:yellow"><</h2>
            </button>
            <button class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="0">
                <h2 style="font-size:110px">0</h2>
            </button>
            <form action="index.php" method="post">
                <input class="grid_3 alarm_button alarm_button_ok ui-corner-all ui-state-default" type="submit"value="OK"
                           style="margin-top:5px;height:130px;width:218px;font-size:110px;color:green;padding:0">
                <input id="auth_password" name="auth_password" type="hidden"/>
                <input name="log_in" value="true" type="hidden"/>
            </form>
            <div class="grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <div class="clear"></div>
        </div>
    </div>

</div>
