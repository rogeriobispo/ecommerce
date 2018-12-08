<?php
namespace roger\TPL;

class PageAdmin extends Page{
public function __construct(array $options = array(), string $tpl_dir = "/views/admin/")
{
    parent::__construct($options, $tpl_dir);
}
}
?>