<?php
// db.php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'conf';

$link = mysqli_connect($host, $user, $pass, $dbname);
if (!$link) {
	die('Ошибка подключения: ' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');
?>