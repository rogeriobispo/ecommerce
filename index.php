<?php
session_start();
require_once("vendor/autoload.php");
use \Slim\Slim;
use \roger\DB\Sql;
use \roger\TPL\Page;
use \roger\TPL\PageAdmin;
use \roger\Model\User;

$app = new Slim();
$app->config('debug', true);

$app->get('/', function (){
    $page = new Page();
    $page->setTpl("index");
});

$app->get('/admin', function (){

    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl('index');
});

$app->get('/admin/login', function(){
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl('login');
});

$app->post('/admin/login', function(){
    user::login($_POST["login"], $_POST["password"]);
    header("Location: /admin");
    exit;
});

$app->get('/admin/logout', function (){
    User::logout();
    header("Location: /admin/login");
    exit;
});

$app->get("/admin/users", function (){
    User::verifyLogin();
    $users = User::listAll();
    $page = new PageAdmin();
    $page->setTpl("users", array(
        "users"=>$users
    ));
});
$app->post("/admin/users/create", function (){
    User::verifyLogin();
    $user = new User();
    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
    $user->setData($_POST);
    $user->save();
    header("Location: /admin/users");
    exit();

});

$app->get("/admin/users/create", function (){
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl("users-create");
});

$app->get("/admin/users/:iduser/delete", function ($iduser){
    User::verifyLogin();
    $user = new User();
    $user->get((int)$iduser);
    $user->delete();
    header("Location: /admin/users");
    exit;

});

$app->get("/admin/users/:iduser", function ($iduser){
    User::verifyLogin();
    $user = new User();
    $user->get((int)$iduser);
    $page = new PageAdmin();
    $page->setTpl("users-update", array(
        "user"=>$user->getValues()
    ));
});

$app->post("/admin/users/:iduser", function ($iduser){
    User::verifyLogin();
    $user = new User();
    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
    $user->get((int)$iduser);
    $user->setData($_POST);
    $user->update();
    header("Location: /admin/users");
    exit;

});

$app->get("/admin/forgot", function() {
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("/admin/forgot");
});

$app->post("/admin/forgot", function(){
    $email = $_POST['email'];
    User::getForgot($email);
    header("Location: /admin/forgot/sent");
    exit;
});

$app->get("/admin/forgot/sent", function (){
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("/admin/forgot-sent");
});

$app->get("/admin/forgot/reset", function (){

    $user = User::validForgotDecript($_GET["code"]);
Error Processing Request
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("/admin/forgot-reset", array(
        "name"=>$user["desperson"],
        "code"=>$_GET["code"]
    ));
});

$app->post("/admin/forgot/reset", function(){
    $forgot = User::validForgotDecript($_POST["code"]);
    User::setForgotUsed($user["idrecovery"]);
    $user = new User();
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
       "cost"=>12
    ]);
    $user->get($forgot["iduser"]);
    $user->setPassword($password);
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("/admin/forgot-reset-success");

});
$app->run();
?>