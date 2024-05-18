<?php
// Функция для установки соединения с базой данных
function connect_to_database($host, $dbname, $username, $pass) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);
        // Устанавливаем режим обработки ошибок

        return $pdo;
    } catch (PDOException $e) {
        // Обработка ошибок подключения к базе данных
        echo "Ошибка подключения к базе данных: " . $e->getMessage();
        die(); // Прерываем выполнение скрипта
    }
}

// Параметры подключения к базе данных
$host = 'localhost'; // имя сервера базы данных
$dbname = 'diplom_project'; // имя базы данных
$username = 'root'; // имя пользователя базы данных
$pass = ''; // пароль пользователя базы данных

// Устанавливаем соединение с базой данных
$pdo = connect_to_database($host, $dbname, $username, $pass);
