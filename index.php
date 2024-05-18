<?php
require_once 'functions.php';
session_start();
if(is_not_logged_in()){
    redirect_to("page_login.php");
}else{
    redirect_to('users.php');
}
exit;