<?php
session_start();
require_once 'functions.php';

if(is_not_logged_in()){
    redirect_to("page_login.php");
}

if (!empty($_GET["id"]) && is_numeric($_GET["id"])) {
 $user_id=$_GET["id"];

    $user_info=get_user_profile($user_id);

}else{
    if($_GET['logout']==true) {
        // Если да, то разрушаем сессию
        session_destroy();
        // Перенаправляем пользователя на страницу входа или на другую страницу, куда вы хотите
        header("Location: page_login.php");
        exit;
    }
    set_flash_message('danger','вы не передали id');
    redirect_to("users.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Профиль пользователя</title>
    <meta name="description" content="Chartist.html">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no, minimal-ui">
    <link id="vendorsbundle" rel="stylesheet" media="screen, print" href="css/vendors.bundle.css">
    <link id="appbundle" rel="stylesheet" media="screen, print" href="css/app.bundle.css">
    <link id="myskin" rel="stylesheet" media="screen, print" href="css/skins/skin-master.css">
    <link rel="stylesheet" media="screen, print" href="css/fa-solid.css">
    <link rel="stylesheet" media="screen, print" href="css/fa-brands.css">
    <link rel="stylesheet" media="screen, print" href="css/fa-regular.css">
</head>
    <body class="mod-bg-1 mod-nav-link">
    <?php require_once 'navbar.php';?>
        <main id="js-page-content" role="main" class="page-content mt-3">
            <div class="subheader">
                <h1 class="subheader-title">
                    <i class='subheader-icon fal fa-user'></i> <?=$user_info["user_name"];?>
                </h1>
            </div>
            <div class="row">
              <div class="col-lg-6 col-xl-6 m-auto">
                    <!-- profile summary -->
                    <div class="card mb-g rounded-top">
                        <div class="row no-gutters row-grid">
                            <div class="col-12">
                                <div class="d-flex flex-column align-items-center justify-content-center p-4">


                                    <?php if($user_info["image"]):?>
                                        <img src="<?=$user_info["image"];?>" class="rounded-circle shadow-2 img-thumbnail" alt="">
                                    <?php else:?>
                                        <img src="img/demo/avatars/avatar-admin-lg.png" class="rounded-circle shadow-2 img-thumbnail" alt="">
                                    <?php endif;?>

                                     <h5 class="mb-0 fw-700 text-center mt-3">
                                        <?=$user_info["user_name"];?>
                                        <small class="text-muted mb-0"><?=$user_info["job_title"];?></small>
                                    </h5>
                                    <div class="mt-4 text-center demo">

                                        <a href="<?php if (!empty($user_info["instagram"])) { echo $user_info["instagram"];} else { echo "javascript:void(0);";}?>" class="fs-xl" style="color:#C13584">
                                            <i class="fab fa-instagram"></i>
                                        </a>
                                        <a href="<?php if (!empty($user_info["vk"])) { echo $user_info["vk"];} else { echo "javascript:void(0);";}?>" class="fs-xl" style="color:#4680C2">
                                            <i class="fab fa-vk"></i>
                                        </a>
                                        <a href="<?php if (!empty($user_info["telegram"])) { echo $user_info["telegram"];} else { echo "javascript:void(0);";}?>" class="fs-xl" style="color:#0088cc">
                                            <i class="fab fa-telegram"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 text-center">
                                    <a href="<?=$user_info["phone"];?>" class="mt-1 d-block fs-sm fw-400 text-dark">
                                        <i class="fas fa-mobile-alt text-muted mr-2"></i> <?=$user_info["phone"];?></a>
                                    <a href="mailto:<?=$user_info["email"];?>" class="mt-1 d-block fs-sm fw-400 text-dark">
                                        <i class="fas fa-mouse-pointer text-muted mr-2"></i> <?=$user_info["email"];?></a>
                                    <address class="fs-sm fw-400 mt-4 text-muted">
                                        <i class="fas fa-map-pin mr-2"></i> <?=$user_info["address"];?>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>
               </div>
            </div>
        </main>
    </body>

    <script src="js/vendors.bundle.js"></script>
    <script src="js/app.bundle.js"></script>
    <script>

        $(document).ready(function()
        {

        });

    </script>
</html>