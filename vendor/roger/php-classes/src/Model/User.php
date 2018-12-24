<?php
namespace roger\Model;
use MongoDB\Driver\Exception\ExecutionTimeoutException;
use roger\DB\Sql;
use roger\Mailer;
use roger\Model;

class User extends Model{
    const SESSION = "User";
    const SECRET= "CHAVE-SECURITY-K";

    public function setPassword($password){
        $sql = new Sql();
        $sql->query("UPDATE tb_users SET despassword = :password WHERE  iduser = :iduser", array(
            ":password"=>$password,
            ":iduser"=>$this->getiduser()
        ));
    }

    public static function setForgotused($idrecovery){
        $sql = new Sql();
        $query = "UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery";
        $sql->select($query, array(
           ":idrecovery"=>$idrecovery
        ));
    }
    public static function validForgotDecrypt($code){

        $idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code) , MCRYPT_MODE_ECB);
        $query = "SELECT * FROM tb_userspasswordrecoveries 
                INNER  JOIN tb_users b USING(iduser) 
                INNER JOIN tb_persons c USING(idperson) 
                WHERE a.idrecovery = :idrecovery AND a.dtrecovery IS NULL
                AND DATE_ADD(a.dtregister, INTERVAL 1 HOUR ) >=  NOW()";
        $sql = new Sql();
        $results = $sql->select($query, array(
            ":idrecovery"=>$idrecovery
        ));
        if (count($results) === 0){
            throw new \Exception("Não foi possivel recuperar a senha", 1);
        }else{
            return $results[0];
        }
    }

    public static function getForgot($email){

        $sql = new Sql();
        $query = "select * from tb_persons a INNER JOIN  tb_users b USING(idperson) where a.desemail = :email";
        $result = $sql->select($query, array(
            ":email"=>$email
        ));

        if(count($result) === 0){
            throw new \Execution("Não foi possivel recuperar a senha.", 1);
        }else{
            $data = $result[0];
            $result2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"]
            ));

            if(count($result2) === 0 ){
                throw new \Execution("Não foi possivel recuperar a senha.", 1);
            }else{
                $dataRecovery = $result2[0];
                $code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
                $link = "http://www.ecommerce.com.br/admin/forgot/reset?code=$code";
                $mailer =  new Mailer($data["desemail"], $data["desperson"], "Redefinir senha RogerCommerce", "/email/forgot", array(
                   ":name"=>$data["desperson"],
                   ":link"=>$link
                ));
                $mailer->send();
                return $data;
            }
        }
    }
    public function delete(){
        $sql = new Sql();
        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));
    }

    public function get($iduser){
        $sql = new Sql();
       $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=>$iduser
        ));
       $this->setData($results[0]);
    }

    public function update(){
        $sql = new Sql();
        $result = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",
            array(
                ":iduser"=>$this->getiduser(),
                ":desperson"=>$this->getdesperson(),
                ":deslogin"=>$this->getdeslogin(),
                ":despassword"=>$this->getdespassword(),
                ":desemail"=>$this->getdesemail(),
                ":nrphone"=>$this->getnrphone(),
                ":inadmin"=>$this->getinadmin()
            ));
        $this->setData($result[0]);

    }

    public function save(){
        $sql = new Sql();
        $result = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",
            array(":desperson"=>$this->getdesperson(),
                ":deslogin"=>$this->getdeslogin(),
                ":despassword"=>$this->getdespassword(),
                ":desemail"=>$this->getdesemail(),
                ":nrphone"=>$this->getnrphone(),
                ":inadmin"=>$this->getinadmin()));
            $this->setData($result[0]);
        
    }

    public static function listAll(){
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_users users 
                                 INNER JOIN tb_persons person 
                                 USING(idperson) 
                                 ORDER BY person.desperson");

    }

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