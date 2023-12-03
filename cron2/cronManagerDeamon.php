<?php
set_time_limit(0);

$dir = '/var/www/html/dxhome/loop/';
$nameProcess = 'loopHeating.php';
$command = 'ps -e --format="cmd" | grep '.$nameProcess;

$output = shell_exec($command);
$processes = explode("\n", $output);

$launched = false;
foreach ($processes as $process) {
    if ($process === 'php '.$dir.$nameProcess) {
        $launched = true;
        break;
    }
}

if (!$launched) {
    exec('php '.$dir.$nameProcess.' &');
}

