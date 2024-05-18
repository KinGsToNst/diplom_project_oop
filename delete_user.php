<?php
session_start();
require 'functions.php';
if(is_not_logged_in()){
    redirect_to("page_login.php");
}

$host = 'localhost'; // имя сервера базы данных
$dbname = 'diplom_project'; // имя базы данных
$username = 'root'; // имя пользователя базы данных
$pass = 'root'; // пароль пользователя базы данных
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);

if (!empty($_GET["id"]) && is_numeric($_GET["id"])) {
    $user_id = $_GET["id"];

    try {
        // Получение имени файла из базы данных
        $stmt = $pdo->prepare("SELECT image FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $image = $stmt->fetchColumn();

        // Путь к файлу изображению
        $upload_dir = 'upload_image/';
        $full_image_path =$image;

        // Удаление файла изображения, если он существует
        if (file_exists($full_image_path)) {
            unlink($full_image_path);
        }

        // Удаление записи из базы данных
        $query = "DELETE FROM users WHERE id = :id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $user_id);
        $statement->execute();

        //Если $user_id равен к текущей $_SESSION['$user_auth']['user_id'] сессии то удаляем, и перенаправляем на page_login.php
        if($user_id==$_SESSION['$user_auth']['user_id']){
            set_flash_message('danger','Ваша Учетка удалена чтобы создать новую зарегистрируйтесь');
            redirect_to("/page_login.php");
            exit;
        }

        set_flash_message("success", "Изображение успешно удалено");


        header('Location: /users.php');
        exit();
         // Важно завершить выполнение скрипта после перенаправления
    } catch (PDOException $e) {
        $_SESSION['danger'] = "Ошибка при удалении изображения: " . $e->getMessage();
        header('Location: /users.php');
        exit(); // Важно завершить выполнение скрипта после перенаправления
    }
} else {
    $_SESSION['danger'] = "Неверный идентификатор изображения";
    header('Location: /users.php');
    exit(); // Важно завершить выполнение скрипта после перенаправления
}