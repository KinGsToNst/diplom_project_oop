<?php
class Connection{
    public static function make($config): ?PDO
    {
        try {
            $pdo = new PDO(
                "{$config['connection']};dbname={$config['database']};charset={$config['charset']}",
                $config['username'],
                $config['password']
            );
            // Установим атрибуты для бросания исключений при ошибках
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            // Проверим код ошибки, чтобы определить, была ли ошибка из-за неправильного пароля
            if ($e->getCode() == 1045) {
                // Выведем сообщение об ошибке пароля, выбросив исключение
                throw new Exception("Неправильный пароль для подключения к базе данных: " . $e->getMessage());
            } else {
                // Выведем общее сообщение об ошибке подключения, выбросив исключение
                throw new Exception("Ошибка подключения к базе данных: " . $e->getMessage());
            }
            // Возвращаем null, чтобы показать, что подключение не удалось
            throw new Exception("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }

}