<?php

/**
 * Class sqlConfig - параметры подключения к базе данных
 */
class sqlConfig {
	const db_host = "localhost";
	const db_user = "root";
	const db_pwd = "12345";
	const db_name = "dexhome";
	const err_rep = 32759; // -1, 0, E_ALL, 32759 as (E_ALL ^ E_NOTICE), etc...
}
?>
