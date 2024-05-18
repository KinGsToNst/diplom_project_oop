<?php

function checking_user_existence($email){
    $host = 'localhost'; // имя сервера базы данных
    $dbname = 'diplom_project'; // имя базы данных
    $username = 'root'; // имя пользователя базы данных
    $pass = 'root'; // пароль пользователя базы данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);
    $sql="SELECT * FROM  users u LEFT JOIN roles r ON r.role_id=u.role_id  WHERE u.email=:email";

    $statement=$pdo->prepare($sql);
    $statement->execute(['email'=>$email]);
    $user=$statement->fetch(PDO::FETCH_ASSOC);

    return $user;
}
/*function add_user($email,$password){
    $host = 'localhost'; // имя сервера базы данных
    $dbname = 'diplom_project'; // имя базы данных
    $username = 'root'; // имя пользователя базы данных
    $pass = 'root'; // пароль пользователя базы данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);
    $hashed_password=password_hash($password,PASSWORD_DEFAULT);

    var_dump("все работает");
    $sql="INSERT INTO users(email,password) VALUES (:email,:password)";
    $statement=$pdo->prepare($sql);

    $statement->execute(['email'=>$email,'password'=>$hashed_password]);
    return $pdo->lastInsertId();
    // $new_user='Регистрация успешна';
    // $_SESSION['success']=$new_user;
    // header("Location:/page_login.php");
}*/
function register_user($email, $password) {


    $host = 'localhost'; // имя сервера базы данных
    $dbname = 'diplom_project'; // имя базы данных
    $username = 'root'; // имя пользователя базы данных
    $pass = 'root'; // пароль пользователя базы данных

    try {
        // Подключение к базе данных
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);
        // Установка режима обработки ошибок
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Хеширование пароля
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Подготовка и выполнение запроса
        if(empty($status_id)){
            // статус занят
            $status_id=3;
        }
        if(empty($role_id)){
            //при созданиии нового пользвателя у него роль пользователя
            $role_id=1;
        }
        $sql = "INSERT INTO users(email,password,role_id,status_id) VALUES (:email,:password,:role_id,:status_id)";
        $statement = $pdo->prepare($sql);
        $statement->execute(['email' => $email, 'password' => $hashed_password,'role_id' => $role_id,'status_id' => $status_id]);

        return $pdo->lastInsertId();
    } catch(PDOException $e) {
        // Обработка ошибок
        echo "Ошибка при выполнении запроса: " . $e->getMessage();
        return false;
    }
}


function create_user($email, $password, $user_name, $job_title, $phone, $address,$status_id,$image,$vk,$telegram,$instagram) {


    $host = 'localhost'; // имя сервера базы данных
    $dbname = 'diplom_project'; // имя базы данных
    $username = 'root'; // имя пользователя базы данных
    $pass = 'root'; // пароль пользователя базы данных

    try {
        // Подключение к базе данных
        if (!is_valid_email($email)) {
            set_flash_message("danger","Неккоректный мэйл");
            redirect_to("/create_user.php");
            exit;
        }
        // Проверяем пароль на пустоту
        if (empty($password)) {
            set_flash_message("danger","Пустой пароль");
            redirect_to("/create_user.php");
            exit;
        }
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);
        // Установка режима обработки ошибок
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Хеширование пароля
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if(empty($role_id)){
            $role_id=1;
        }
        if(empty($status_id)){
            $status_id=3;
        }
        if(empty($image)){
           $image_path='img/demo/avatars/avatar-m.png';
        }
        $image_path = upload_avatar($image);
        // Подготовка и выполнение запроса для добавления пользователя и его информации
        $sql = "INSERT INTO users(email, password, user_name, job_title, phone, address,image, role_id, status_id,vk,telegram,instagram) 
VALUES (:email, :password, :user_name, :job_title, :phone, :address, :image,:role_id, :status_id,:vk,:telegram,:instagram)";
        $statement = $pdo->prepare($sql);
        $statement->execute(['email' => $email,
            'password' => $hashed_password,
            'user_name' => $user_name,
            'job_title' => $job_title,
            'phone' => $phone,
            'address' => $address,
            'image' => $image_path,
            'role_id' => $role_id,
            'status_id' => $status_id,
            'vk' => $vk,
            'telegram' => $telegram,
            'instagram' => $instagram
        ]);



        return $pdo->lastInsertId(); // Возвращаем ID нового пользователя
    } catch(PDOException $e) {
        // Обработка ошибок
        echo "Ошибка при выполнении запроса: " . $e->getMessage();
        return false;
    }
}
function is_valid_email($email) {
    // Функция для проверки корректности email с использованием регулярного выражения
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
function get_edit_information($user_id){

    $host = 'localhost';
    $dbName = 'diplom_project';
    $user = 'root';
    $pass = 'root';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbName", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Подготавливаем SQL запрос для выборки информации о пользователе по его ID
        $sql = "SELECT user_name, job_title, phone, address FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        $stmt->execute(['id'=>$user_id]);


        // Получаем результат запроса в виде ассоциативного массива
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Возвращаем результат
        return $result;
    }catch (PDOException $e){
        set_flash_message("danger","{$e->getMessage()}");
        return null;
    }

}
function get_user_status(){
    $host = 'localhost';
    $dbname = 'diplom_project';
    $username = 'root';
    $pass = 'root';

    try {



        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);
        $query = "SELECT * FROM status";
        $stmt=$pdo->query($query);

        $results=$stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    } catch(PDOException $e) {
        // В случае ошибки выводим сообщение об ошибке
        echo "Ошибка: " . $e->getMessage();
        return null;
    }
}
function get_current_status($user_id){
    $host = 'localhost'; // имя сервера базы данных
    $dbname = 'diplom_project'; // имя базы данных
    $username = 'root'; // имя пользователя базы данных
    $pass = 'root'; // пароль пользователя базы данных

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);
        $sql = "SELECT status_id FROM users WHERE id = :id";

        $statement = $pdo->prepare($sql);
        $statement->execute(['id' => $user_id]);
        $current_status = $statement->fetch(PDO::FETCH_ASSOC);
        return  $current_status;
    } catch (PDOException $e) {
        // В случае ошибки выводим сообщение об ошибке
        echo "Ошибка: " . $e->getMessage();
        return null;
    }
}
function get_user_profile($user_id){
    $host = 'localhost'; // имя сервера базы данных
    $dbname = 'diplom_project'; // имя базы данных
    $username = 'root'; // имя пользователя базы данных
    $pass = 'root'; // пароль пользователя базы данных

    try{
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);
        $sql="SELECT * FROM  users  WHERE id=:id";

        $statement=$pdo->prepare($sql);
        $statement->execute(['id'=>$user_id]);
        $user=$statement->fetch(PDO::FETCH_ASSOC);
        return $user;

    } catch (PDOException $e){
        // Оставляем обработку ошибки на уровне, где вызвана функция
        return null;
    }
}
function set_user_status($user_id,$user_status){
    $host = 'localhost';
    $dbName = 'diplom_project';
    $user = 'root';
    $pass = 'root';
// Создаем объект PDO

    try {

        $pdo = new PDO("mysql:host=$host;dbname=$dbName", $user, $pass);

        $sql = "UPDATE users SET  status_id = :status_id WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':status_id', $user_status);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        if($stmt->rowCount()>0){
            set_flash_message('success','статус пользователя обновлена');
            redirect_to("/users.php");
            exit;
        }else{
            set_flash_message('danger','статус пользователе не обновлена');
            redirect_to("/users.php");
            exit;
        }

    }catch (PDOException $e){

        set_flash_message('danger',"{$e->getMessage()}");
    }
}
function update_information($user_id, $user_name, $job_title, $phone, $address){
    $host = 'localhost';
    $dbName = 'diplom_project';
    $user = 'root';
    $pass = 'root';
// Создаем объект PDO

    try {


    $pdo = new PDO("mysql:host=$host;dbname=$dbName", $user, $pass);
// Изменяем данные записи с ID 3 с использованием привязки параметров
        $sql = "UPDATE users SET user_name = :user_name, job_title = :job_title, phone = :phone, address = :address WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_name', $user_name);
        $stmt->bindParam(':job_title', $job_title);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    if($stmt->rowCount()>0){
        set_flash_message("success","Общая информация  пользователя {$user_name} обновлена");
        redirect_to("/users.php");
        exit;
    }else{
        set_flash_message('danger','Общая информация о пользователе не обновлена');
        redirect_to("/users.php");
        exit;
    }

    }catch (PDOException $e){

        set_flash_message('danger',"{$e->getMessage()}");
    }
}

function upload_avatar($image): bool|string
{
    $upload_dir = 'upload_image/'; // Директория для загрузки изображений

    // Получение расширения файла
    $image_extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

    // Генерация уникального имени файла
    $unique_name = uniqid() . '.' . $image_extension;

    // Полный путь для сохранения изображения
    $upload_path = $upload_dir . $unique_name;

    // Перемещение изображения в заданную директорию
    if (move_uploaded_file($image['tmp_name'], $upload_path)) {
        return 'upload_image/' . $unique_name; // Возвращаем путь к изображению с именем файла
    } else {
        // В случае ошибки загрузки возвращаем false
        return false;
    }
}

function get_current_avatar($user_id)
{
    $host = 'localhost';
    $dbName = 'diplom_project';
    $user = 'root';
    $pass = 'root';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbName", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Подготавливаем SQL запрос для выборки информации о пользователе по его ID
        $sql = "SELECT image FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        $stmt->execute(['id'=>$user_id]);


        // Получаем результат запроса в виде ассоциативного массива
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Возвращаем результат
        return $result;
    }catch (PDOException $e){
        set_flash_message("danger","{$e->getMessage()}");
        return null;
    }
}
function upload_avatar_profile($user_id, $image)
{
    $upload_dir = 'upload_image/'; // Директория для загрузки изображений

    // Получение расширения файла
    $image_extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

    // Генерация уникального имени файла
    $unique_name = uniqid() . '.' . $image_extension;

    // Полный путь для сохранения изображения
    $upload_path = $upload_dir . $unique_name;

    // Подключение к базе данных
    $host = 'localhost';
    $dbname = 'diplom_project';
    $username = 'root';
    $pass = 'root';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Получение старого пути к изображению
        $stmt_old_image = $pdo->prepare("SELECT image FROM users WHERE id = :user_id");
        $stmt_old_image->execute(['user_id' => $user_id]);
        $old_image = $stmt_old_image->fetchColumn();

        // Удаление старого изображения
        if (!empty($old_image) && file_exists($old_image)) {
            unlink($old_image);
        }

        // Обновление пути к изображению в базе данных
        $sql_update = "UPDATE users SET image = :image WHERE id = :user_id";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute(['image' => $upload_path, 'user_id' => $user_id]);

        // Перемещение нового изображения в заданную директорию
        if (move_uploaded_file($image['tmp_name'], $upload_path)) {
            set_flash_message('success','Изображение загружена');
            redirect_to("/users.php");
            return true; // Возвращаем true при успешной загрузке и обновлении
        } else {
            set_flash_message('danger','Изображение не загружена');
            redirect_to("/users.php");
            exit; // В случае ошибки загрузки возвращаем false
        }
    } catch (PDOException $e) {
        return false; // В случае ошибки подключения к базе данных возвращаем false
    }
}
function redirect_to($path)
{
    header("Location: {$path}");
}

function set_flash_message($name,$message)
{
    $_SESSION[$name]=$message;
}
function display_flash_message($name)
{
   if(!empty($_SESSION[$name])) {
       echo "<div class=\"alert alert-{$name} text-dark\" role=\"alert\">{$_SESSION[$name]}</div>";
       unset($_SESSION[$name]);
   }
}

function get_user_role($user_auth){

}
function login($email,$password){
    $host = 'localhost'; // имя сервера базы данных
    $dbname = 'diplom_project'; // имя базы данных
    $username = 'root'; // имя пользователя базы данных
    $pass = 'root'; // пароль пользователя базы данных

  try{
      $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);
      $sql="SELECT u.*,r.name as role_name FROM  users u LEFT JOIN roles r ON r.role_id=u.role_id WHERE email=:email";

      $statement=$pdo->prepare($sql);
      $statement->execute(['email'=>$email]);
      $user=$statement->fetch(PDO::FETCH_ASSOC);

      if($user){
          $password_verified=password_verify($password,$user['password']);
          if($password_verified){
              // Успешная аутентификация
              set_flash_message("success","Вы вошли в систему");

              $user_auth=[
                  "user_id"=>$user["id"],
                  "email"=>$user["email"],
                  "role_id"=>$user["role_id"],
                  "role"=>$user["role_name"]
              ];
              $_SESSION['$user_auth']=$user_auth;
              set_flash_message("success","Добро пожаловать {$user['role_name']} {$email} в админ панель");
              redirect_to("/users.php");
              exit;
          }else{
              set_flash_message("danger","неправильный пароль");
              redirect_to("/page_login.php");
              exit;
              //return false;
          }
      }else{
          //// Пользователь с таким email не найден
          set_flash_message("danger","пользователь с таким мейлом не найден");
          return false;
      }
  } catch (PDOException $e){
      echo "Ошибка: " . $e->getMessage();
      return false;
  }

}
function get_users(){
    $host = 'localhost';
    $dbname = 'diplom_project';
    $username = 'root';
    $pass = 'root';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);
    $query = "SELECT u.*,s.name as status FROM users u LEFT JOIN status s ON s.status_id=u.status_id";
    $stmt=$pdo->query($query);

    $results=$stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;
}

function is_logged(){
    if(!empty($_SESSION['$user_auth'])){
       return true;
    }
    return false;
}
function is_not_logged_in(){
  return !is_logged();
}

function get_authentificated_user(){
    //проверяем авторизоан ли и берем   пользователя из текущей сессии
if(is_logged()){
    return $_SESSION['$user_auth'];
}
return false;
}
//проверка текущего роль пользователя из сессии
function is_admin($user_auth){

if(is_logged()){
    //сравнивнение текущей роли пользователя с бд
    if($user_auth["role_id"]===2){
        return true;
    }
    return false;
}
}
//сравнение текущего пользователя со списком пользвателя если совпадает то выводит меню редактирование
function is_equal($user,$current_user): bool
{
//сравнение пользователя в списке users $user["id"]  c сессией $_SESSION['$user_auth'];
if($user["id"]==$current_user["user_id"]){
    return true;
}
return false;
}
function security_check($user_id){
    $host = 'localhost'; // имя сервера базы данных
    $dbname = 'diplom_project'; // имя базы данных
    $username = 'root'; // имя пользователя базы данных
    $pass = 'root'; // пароль пользователя базы данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);
    $sql="SELECT email FROM  users   WHERE id=:user_id";

    $statement=$pdo->prepare($sql);
    $statement->execute(['user_id'=>$user_id]);
    $user=$statement->fetch(PDO::FETCH_ASSOC);
return $user;
}

function update_security($user_id,$email,$password){
    $host = 'localhost'; // имя сервера базы данных
    $dbname = 'diplom_project'; // имя базы данных
    $username = 'root'; // имя пользователя базы данных
    $pass = 'root'; // пароль пользователя базы данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);


    // Проверка email
    if(validate_email($email,$user_id)){
        // Хеширование пароля
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Подготовленный запрос для обновления данных пользователя
        $sql = "UPDATE users SET email = :email, password = :password WHERE id = :user_id";

        $statement = $pdo->prepare($sql);

        // Выполнение запроса с передачей значений, включая захешированный пароль
        $result = $statement->execute([
            'email' => $email,
            'password' => $hashed_password,
            'user_id' => $user_id
        ]);

        // Проверка на успешное выполнение запроса
        if($result) {

            redirect_to("/users.php");
            exit;
        } else {
            return "Ошибка при обновлении данных пользователя.";
        }
    }


}

function validate_email($email,$user_id) {
    $host = 'localhost'; // имя сервера базы данных
    $dbname = 'diplom_project'; // имя базы данных
    $username = 'root'; // имя пользователя базы данных
    $pass = 'root'; // пароль пользователя базы данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);

    // Проверка на пустоту email
    if (empty($email)) {
        set_flash_message("danger", "Email не должен быть пустым.");
        redirect_to("/users.php");
        return false;
    }
    // Запрос для получения email текущего пользователя по его ID
    $sql = "SELECT email FROM users WHERE id = :user_id";
    $statement = $pdo->prepare($sql);
    $statement->execute(['user_id' => $user_id]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    // Если email текущего пользователя совпадает с предоставленным email, валидация проходит
    if ($user['email'] === $email) {
        set_flash_message("success","ваш текущии мэйл не изменен");
        return true;
    }

    // Проверка правильности формата email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash_message("danger", "Неправильный формат email.");
        redirect_to("/users.php");
        return false;
    }

    // Подготовленный запрос для проверки существования email в базе данных
    $sql = "SELECT COUNT(*) AS count FROM users WHERE email = :email";
    $statement = $pdo->prepare($sql);
    $statement->execute(['email' => $email]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    // Если такой email уже существует, возвращаем сообщение об ошибке
    if ($row['count'] > 0) {
        set_flash_message("danger", "Email уже используется.");
        redirect_to("/users.php");
        return false;
    }

    // Если проверка прошла успешно, возвращаем true
    set_flash_message("success", "Вы поменяли email.");
    return true;
}