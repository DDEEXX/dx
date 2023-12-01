<?php

require_once(dirname(__FILE__) . '/../../class/managerUnits.class.php');

if ($_REQUEST['dev'] == 'loadData') {

    $output = <<<JAL
<script src="js2/power/jalousie.js"></script>
<div style="display: flex; justify-content:space-between; margin-top: 10px; width: 200px">
    <div class="power_jalousie_hall_control_vertical">
        <button class="power_jalousie_hall_1">
        </button>
        <button class="power_jalousie_hall_1">
        </button>
        <button class="power_jalousie_hall_1">
        </button>
    </div>
    <div class="power_jalousie_hall_control_vertical">
        <button class="power_jalousie_hall_1">
        </button>
        <button class="power_jalousie_hall_1">
        </button>
        <button class="power_jalousie_hall_1">
        </button>
    </div>
    <div class="power_jalousie_hall_control_vertical">
        <button class="power_jalousie_hall_1" label="jalousie_hall_1_right" value="{&quot;state&quot;:&quot;OPEN&quot;}">
        </button>
        <button class="power_jalousie_hall_1" label="jalousie_hall_1_right" value="{&quot;state&quot;:&quot;STOP&quot;}">
        </button>
        <button class="power_jalousie_hall_1" label="jalousie_hall_1_right" value="{&quot;state&quot;:&quot;CLOSE&quot;}">
        </button>
    </div>   
</div>
JAL;
    echo $output;

}

