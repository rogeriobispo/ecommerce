<?php
/**
 * Created by PhpStorm.
 * User: roger
 * Date: 09/12/18
 * Time: 13:02
 */
namespace roger;

class Model{

    private $values = [];
    public function __call($name, $args) {
        $method = strtolower(substr($name, 0, 3));
        $fieldName = strtolower(substr($name, 3, strlen($name)));

       // var_dump($method, $fieldName);

        switch ($method)
        {
            case "get":
                return $this->values[$fieldName];
                break;
            case "set":
                $this->values[$fieldName] = $args[0];
                break;

        }
    }

    public function setData($data = array())
    {
           foreach ($data as $key => $value){
               $this->{"set".$key}($value);
           }
    }

    public function getValues(){
        return $this->values;

    }
}