<?php
$config=include __DIR__.'/../'.'config.php';
include 'database/QueryBuilder.php';
include 'database/Connection.php';
$connection=new Connection();
return new QueryBuilder(
    $connection::make($config['database'])
);

