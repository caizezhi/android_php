<?php
require 'Slim/Slim.php';
require 'function.php';
$app = new Slim();
//index
$app->get("/:lesson/:teacher/:user/:is_puiblic","lessons");
$app->post("/uploadPicture","uploadPic");
$app->post("/uploadVoi","uploadVoi");
$app->post("/createlesson","createLesson");
$app->post("/homework","homework");
$app->post("/deletelesson","deleteLesson");

//admin
$app->post("/admin/login","login");
$app->post("/admin/register","register");
$app->post("/admin/logout","logout");

$app->run();
?>
