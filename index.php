<?php
require 'Slim/Slim.php';
require 'function.php';
$app = new Slim();
//index
$app->get("/:lesson/:teacher/:user/:is_public","lessons");
$app->post("/uploadPic","uploadPic");
$app->post("/uploadVoi","uploadVoi");
$app->post("/createlesson","createLesson");
$app->post("/homework","homework");
$app->post("/deletelesson","deleteLesson");
$app->post("/updategrade","updategrade");
$app->get("/:id_student/:id_lesson","get_grade");
$app->post("/test_json","get_info");
$app->post("/downloadPic","downloadPic");
$app->get("/download/:userId/:lessonName/:is_public","downloadPic_test");
//admin
$app->post("/admin/login","login");
$app->post("/admin/register","register");
$app->post("/admin/logout","logout");

$app->run();
?>
