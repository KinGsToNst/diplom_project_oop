<?php
require 'functions.php';
$db=include 'database/start.php';

function checking_user_existence($pdo,$email){
    $sql="SELECT * FROM  users u LEFT JOIN roles r ON r.role_id=u.role_id  WHERE u.email=:email";

    $statement=$pdo->prepare($sql);
    $statement->execute(['email'=>$email]);
    $user=$statement->fetch(PDO::FETCH_ASSOC);

    return $user;
}