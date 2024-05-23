<?php
session_start();
$db=include  'database/start.php';
if(QueryBuilder::is_not_logged_in()){
    QueryBuilder::redirect_to("/page_login.php");
}


if (!empty($_GET["id"]) && is_numeric($_GET["id"])) {
    $user_id = $_GET["id"];


    try {
        $data=[
            'id'=>$_GET["id"]
            ];
        $users=$db->getOne('users',$data);
        $upload_dir = 'upload_image/';
        $noFoto='img/demo/avatars/avatar-m.png';
        if (file_exists($users['image'])) {
            if($users['image']==$noFoto){

            }else{
                unlink($users['image']);
            }

        }

       $db->delete('users',$user_id);
        //Если $user_id равен к текущей $_SESSION['$user_auth']['user_id'] сессии то удаляем, и перенаправляем на page_login.php
        if($user_id==$_SESSION['user_auth']['user_id']){
            QueryBuilder::set_flash_message('danger','Ваша Учетка удалена чтобы создать новую зарегистрируйтесь');
            QueryBuilder::redirect_to("/page_login.php");
            exit;
        }
        QueryBuilder::set_flash_message("success", "Пользователь успешно удален");
        QueryBuilder::redirect_to('/index.php');
        exit();
         // Важно завершить выполнение скрипта после перенаправления
    } catch (PDOException $e) {
        $_SESSION['danger'] = "Ошибка при удалении пользователя: " . $e->getMessage();
        header('Location: /index.php');
        exit(); // Важно завершить выполнение скрипта после перенаправления
    }
} else {
    QueryBuilder::set_flash_message('danger','неверный идентификатор');
      QueryBuilder::redirect_to(' /index.php');
    exit(); // Важно завершить выполнение скрипта после перенаправления
}