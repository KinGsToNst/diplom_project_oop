<?php
class QueryBuilder{
    protected $pdo;
    public function __construct($pdo){
        $this->pdo=$pdo;
    }
    public function getAll($mainTable, $joinTable = null, $joinCondition = null) {
        $query = "SELECT {$mainTable}.*";
        if ($joinTable && $joinCondition) {
            $query .= ", {$joinTable}.name as status 
                       FROM {$mainTable} 
                       LEFT JOIN {$joinTable} ON {$joinCondition}";
        } else {
            $query .= " FROM {$mainTable}";
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public  function create($table, $data) {
        //уставливаю роль по умолчанию так как по заданию не было изменении ролей
                $data['role_id']=1;
            if(!isset($data['status_id'])){
            $data['status_id']=3;

            }
        if(array_key_exists('status_id',$data)){
            if(empty($data['status_id'])){
                $data['status_id']=3;
            }

        }

        if (array_key_exists('image', $data)) {

            $uploaded_image_path = $this->upload_avatar($data['image']);
            // Проверяем, что функция вернула путь к загруженному изображению
            if ($uploaded_image_path) {
                // Если загрузка прошла успешно, устанавливаем путь к загруженному файлу
                $data['image'] = $uploaded_image_path;
            } else {
                // Если возникла ошибка при загрузке изображения, устанавливаем путь к изображению по умолчанию
                $data['image'] = 'img/demo/avatars/avatar-m.png';
            }
        }




        if(array_key_exists('email',$data)){

            if(!$this->validate_email($data) && self::is_logged()){
                QueryBuilder::set_flash_message('danger','неккореткный мэйл');
                QueryBuilder::redirect_to('create_user.php');
                 exit();
            }

            if(!$this->validate_email($data) && self::is_not_logged_in()){
                QueryBuilder::set_flash_message('danger','неккоретный мэйл без входа');
                QueryBuilder::redirect_to('page_login.php');
                exit();
            }
        }
        /* хэширование  и проверка пароля*/
        if (array_key_exists('password', $data)) {
            $password = $data['password'];
            // Check if password is empty
            if (empty($password)) {
                throw new Exception("Пароль не должен быть пустым");
            }

            $hashed_password = $this->is_valid_password($password);
            // Перезаписываем хэшированный пароль в массив $data
            $data['password'] = $hashed_password;
        }

        $keys=implode(',',array_keys($data));
        $tags=":".implode(',:',array_keys($data));

        $sql = "INSERT INTO {$table} ({$keys}) VALUES ({$tags})"; // Используем подготовленный запрос

        $statement =$this->pdo->prepare($sql);

        return   $statement->execute($data);

    }
    public function is_valid_password($password): bool|string
    {

        // Check if password is empty
        if (empty($password) ) {
            // Возвращаем false, если пароль пустой
            return false;
        }

        if (strlen($password) < 3) {
            QueryBuilder::set_flash_message('danger', "Пароль должен быть не менее 3х символов");
            redirect_to('index.php');
        }

        // Возвращаем хэш пароля
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getOne($table, $data, $joins = [])
    {
        // Базовый SQL запрос для выборки данных из основной таблицы
        $sql = "SELECT {$table}.*, ";

        // Добавляем выборку полей из присоединённых таблиц
        if (!empty($joins)) {
            foreach ($joins as $join) {
                $sql .= "{$join['table']}.*, ";
            }
        }

        // Удаляем последнюю запятую
        $sql = rtrim($sql, ', ');

        // Добавляем выборку из указанной таблицы
        $sql .= " FROM {$table}";

        // Добавляем LEFT JOIN с таблицами из массива $joins
        if (!empty($joins)) {
            foreach ($joins as $join) {
                $sql .= " LEFT JOIN {$join['table']} ON {$join['condition']}";
            }
        }

        // Определяем, какое поле использовать для условия WHERE
        $whereField = isset($data['id']) && $data['id'] !== '' ? 'id' : 'email';

        // Добавляем условие WHERE для проверки передан ли email или id
        if ($whereField === 'email' && isset($data['email']) && $data['email'] !== '') {
            $sql .= " WHERE {$table}.email = :email";
        } elseif ($whereField === 'id' && isset($data['id']) && $data['id'] !== '') {
            $sql .= " WHERE {$table}.id = :id";
        }

        // Подготавливаем запрос
        $statement = $this->pdo->prepare($sql);

        // Привязываем параметры
        if ($whereField === 'email' && isset($data['email']) && $data['email'] !== '') {
            $statement->bindParam(':email', $data['email']);
        } elseif ($whereField === 'id' && isset($data['id']) && $data['id'] !== '') {
            $statement->bindParam(':id', $data['id']);
        }

        // Выполняем запрос
        $statement->execute();

        // Получаем данные
        $userData = $statement->fetch(PDO::FETCH_ASSOC);

        // Если данные найдены и есть пароль
        if (!empty($userData) && isset($data['password']) && $data['password'] !== '' && password_verify($data['password'], $userData['password'])) {
            // Проверяем аутентификацию в сессии
            if (empty($_SESSION['user_auth'])) {
                // Сохраняем данные аутентификации в сессии
                $user_auth = [
                    "user_id" => $userData["id"],
                    "email" => $userData["email"],
                    "role_id" => $userData["role_id"]
                ];
                $_SESSION['user_auth'] = $user_auth;
            } else {
                // Перенаправляем на страницу входа
                QueryBuilder::redirect_to('/page_login.php');
            }
        }

        return $userData;
    }

    //===Начало Сессия===//

    public static function is_logged(): bool
    {
        if(!empty($_SESSION['user_auth'])){
            return true;
        }
        return false;
    }
   public static function is_not_logged_in(){
        return !QueryBuilder::is_logged();
    }

    public static function get_authentificated_user(){
        //проверяем авторизоан ли и берем   пользователя из текущей сессии
        if(QueryBuilder::is_logged()){
            return $_SESSION['user_auth'];
        }
        return false;
    }
    public static function is_admin(){

        if(QueryBuilder::is_logged()){
            //сравнивнение текущей роли пользователя с бд
            if($_SESSION["user_auth"]["role_id"]===2){
                return true;
            }
            return false;
        }
    }

    public static function is_equal($user,$current_user): bool
    {
        //сравнение идентификатора пользователя в списке users $user["id"]  c текущей сессией $_SESSION['$user_auth'];
        if($user["id"]==$current_user["user_id"]){
            return true;
        }
        return false;
    }

    public static function Logout($getParams): void
    {
        if(isset($getParams['logout']) && $getParams['logout']) {
            // Если да, то разрушаем сессию
            session_destroy();
            // Перенаправляем пользователя на страницу входа или на другую страницу, куда вы хотите
            header("Location: page_login.php");
            exit;
        }
    }
//===Конец Сесссия===//

    public function update($table, $data) {
        // Проверка наличия идентификатора
        if(!array_key_exists('id', $data)) {
            QueryBuilder::set_flash_message('danger', 'Вы не передали идентификатор');
            redirect_to('index.php');
            exit();
        }

        // Проверка и обновление статуса, если он не был передан
        if(array_key_exists('status_id', $data) && empty($data['status_id'])) {
            $data['status_id'] = 3;
        }

        // Удаление старого изображения, если оно есть
        if (array_key_exists('old_image', $data) && file_exists($data['old_image'])) {
            unlink($data['old_image']);
        }

        // Загрузка нового изображения, если оно передано
        if (array_key_exists('image', $data)) {
            $uploaded_image_path = $this->upload_avatar($data['image']);
            if ($uploaded_image_path) {
                $data['image'] = $uploaded_image_path;
            } else {
                $data['image'] = 'img/demo/avatars/avatar-m.png';
            }
        }

        // Проверка валидности email, если он передан
        if(array_key_exists('email', $data)) {
            $email = $data['email'];
            if(!$this->validate_email($data)) {
                QueryBuilder::set_flash_message('danger', 'Невалидный email');
                redirect_to('index.php');
                exit();
            }
        }

        // Проверка пароля и верификация, если он передан
        if (array_key_exists('password', $data) && !empty($data['password'])) {
            $password = $data['password'];
            if (empty($password) && !QueryBuilder::is_logged()) {
                QueryBuilder::set_flash_message('danger', 'Пароль не должен быть пустым');
                QueryBuilder::redirect_to('index.php');
                exit();
            }

            $hashed_password = $this->is_valid_password($password);
            $data['password'] = $hashed_password;
        }else{
            unset($data['password']);
        }


        unset($data['old_image']);

        // Формирование части SQL-запроса для обновления данных
        $keys = array_keys($data);
        $string = '';
        foreach ($keys as $key) {
            //это нужно чтобы идентификатор -id не попадал в тело запроса
            if ($key !== 'id') {
                    $string .= $key . '=:' . $key . ', ';
                }
        }

        //Удаляем последнюю запятую
        $keysString = rtrim($string, ', ');


        $id = $data['id'];
        unset($data['id']);

        // Формирование SQL-запроса
        $sql = "UPDATE {$table} SET {$keysString} WHERE id=:id";
        $statement = $this->pdo->prepare($sql);
        $data['id'] = $id;
       return $statement->execute($data);

    }



    public function delete($table,$id){
        $sql="DELETE FROM {$table} WHERE id=:id";
        $statement=$this->pdo->prepare($sql);
        //$statement->bindValue(':id',$id);//принимает любое значение написанное вручную
        $statement->bindParam(':id',$id);// только переменные
       return $statement->execute();//если не используем bindParam или

    }
    public function checking_user_existence($table, $email, $joinTable = null, $joinCondition = null) {
        $sql = "SELECT * FROM {$table}";

        if ($joinTable && $joinCondition) {
            $sql .= " LEFT JOIN {$joinTable} ON {$joinCondition}";
        }

        $sql .= " WHERE email=:email";

        $statement = $this->pdo->prepare($sql);
        $statement->bindParam(':email', $email);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function validate_email($data) {
        // Проверка на пустоту email
        if(array_key_exists('email',$data)){
            if (empty($data['email'])) {
                QueryBuilder::set_flash_message("danger", "Email не должен быть пустым.");
                QueryBuilder::redirect_to("/index.php");
                return false;
            }
        }

        $user=$this->getOne('users',$data);


        // Если email текущего пользователя совпадает с предоставленным email, валидация проходит
        if ($user !== false && $user['email'] === $data['email']) {

            QueryBuilder::set_flash_message("success","ваш текущии мэйл не изменен");
            QueryBuilder::redirect_to('index.php');
            exit;
        }

        // Проверка правильности формата email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            QueryBuilder::set_flash_message("danger", "Неверный формат email.");
            QueryBuilder::redirect_to("/index.php");
            exit();
        }

        $sql = "SELECT COUNT(*) AS count FROM users WHERE email = :email";
        $statement =$this->pdo->prepare($sql);
        $statement->execute(['email' => $data['email']]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        // Если такой email уже существует, возвращаем сообщение об ошибке
        if ($row['count'] > 0 && QueryBuilder::is_logged()) {
            QueryBuilder::set_flash_message("danger", "Email уже используется.");
            QueryBuilder::redirect_to("/index.php");
            exit();
        }


        return true;
    }




    public static function set_flash_message($name,$message): void
    {
        $_SESSION[$name]=$message;
    }
    public static function display_flash_message($name): void
    {
        if(!empty($_SESSION[$name])) {
            echo "<div class=\"alert alert-{$name} text-dark\" role=\"alert\">{$_SESSION[$name]}</div>";
            unset($_SESSION[$name]);
        }
    }
   public function upload_avatar($image): bool|string {
        // Директория для загрузки изображений
        $upload_dir = 'upload_image/';

        // Если изображение не было загружено, устанавливаем значение по умолчанию
        if (empty($image['tmp_name'])) {
            return 'img/demo/avatars/avatar-m.png';
        }

        $image_extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

        // Генерация уникального имени файла
        $unique_name = uniqid() . '.' . $image_extension;


        $upload_path = $upload_dir . $unique_name;


        if (move_uploaded_file($image['tmp_name'], $upload_path)) {
            return 'upload_image/' . $unique_name; // Возвращаем путь к изображению с именем файла
        } else {
            // В случае ошибки загрузки возвращаем false
            return false;
        }
    }
   public static function redirect_to($path)
    {
        header("Location: {$path}");
    }


}