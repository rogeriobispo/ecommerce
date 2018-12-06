<?php
require_once("vendor/autoload.php");

$app = new \Slim\Slim();
$app->config('debug', true);

$app->get('/', function (){
    echo "ok";
    $sql = new roger\DB\Sql();
    $result = $sql->select("select * from tb_users");
    echo json_encode($result);
});

$app->run();
?>