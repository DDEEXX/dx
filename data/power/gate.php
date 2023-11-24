<?php

require_once(dirname(__FILE__) . '/../../class/managerUnits.class.php');

if ($_REQUEST['dev'] == 'loadData') {

    $output = <<<GATE
<script src="js2/power/gate.js"></script>
<div id="power_gate_control_group" style="margin-left: 20px; margin-top: 20px">
    <button id="power_gate_open" style="width: 104px; height: 72px">
        <span style="display: inline-block; width: 64px; height: 64px; background-image: url('img2/icon/gate_open.png')"></span>
    </button>
    <button id="power_gate_close" style="width: 104px; height: 72px">
        <span style="display: inline-block; width: 64px; height: 64px; background-image: url('img2/icon/gate_close.png')"></span>    
    </button>
</div>
GATE;
    echo $output;

}

