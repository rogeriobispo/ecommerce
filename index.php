<?php
require_once("vendor/autoload.php");
use \Slim\Slim;
use \roger\DB\Sql;
use \roger\TPL\Page;
$app = new Slim();
$app->config('debug', true);

$app->get('/', function (){
    $page = new Page();
    $page->setTpl("index");
});

$app->run();
?>