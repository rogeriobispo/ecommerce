<?php
namespace roger\Model;
use roger\DB\Sql;
use roger\Model;

class User extends Model{
    const SESSION = "User";

    public static function verifyLogin($inadmin = true){
        if (
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
            ||
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
        ){
            header("Location: /admin/login");
            exit;
        }
    }

    public static function logout(){
        $_SESSION[User::SESSION] = NULL;
    }
    public static function login($login, $passwd){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN"=>$login
        ));

        if (count($results) === 0)
        {
           throw new \Exception("Usuário inexistente ou senha invalida");
        }

        $data = $results[0];
        if (password_verify($passwd, $data["despassword"]))
        {
            $user = new User();
            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValues();
            return $user;

        }else{
            throw new \Exception("Usuário inexistente ou senha invalida");
        }
    }
}
?>