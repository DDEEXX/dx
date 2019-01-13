<?php
/** Опрос всех температурных датчиков и запись показаний в базу данных
 * Created by PhpStorm.
 * User: root
 * Date: 07.01.19
 * Time: 12:23
 */

if (file_exists("/opt/owfs/share/php/OWNet/ownet.php"))
    require "/opt/owfs/share/php/OWNet/ownet.php";
elseif (file_exists("/usr/share/php/OWNet/ownet.php"))
    require "/usr/share/php/OWNet/ownet.php";
elseif (file_exists("class/ownet.php"))
    require "class/ownet.php";
else
    die("File 'ownet.php' is not found.");



?>