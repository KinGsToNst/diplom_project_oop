<?php
session_start();
$db=include  'database/start.php';
if(QueryBuilder::is_not_logged_in()){
    QueryBuilder::redirect_to("/page_login.php");
}
if($_GET) {
    QueryBuilder::Logout($_GET);
}



$users=$db->getAll('users','status','status.status_id = users.status_id');
include 'view/index.view.php';


