<?php
namespace App\Model;
use SON\Db\Table;
use \App\Model\PasswordUtil;

class Usuario extends Table{
  protected $table = "usuarios";

  public function save($nome,$email,$login,$setor,$matricula){
    // Hash password derived from the default pattern
    $pass = ''.$login.''.$matricula;
    $pwhash = PasswordUtil::hash($pass);
    // Insert the user
    $query = "Insert into ".$this->table." (nome,email,login,setor,matricula,password_hash) values (?,?,?,?,?,?)";
    $params = array($nome, $email, $login, $setor, $matricula, $pwhash);
    $stmt = $this->db->prepare($query);
    if ($stmt->execute($params) && $stmt->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
  }

  public function findByLogin($login){
    $stmt = $this->db->prepare("Select * from {$this->table} where login=:login");
    $stmt->bindParam(":login",$login);
    $stmt->execute();
    $res = $stmt->fetch();
    return $res;
  }

  public function findByEmail($email){
    $stmt = $this->db->prepare("Select * from {$this->table} where email=:email");
    $stmt->bindParam(":email",$email);
    $stmt->execute();
    $res = $stmt->fetch();
    return $res;
  }

  public function findByRegistrationNumber($matricula){
    $stmt = $this->db->prepare("Select * from {$this->table} where matricula=:matricula");
    $stmt->bindParam(":matricula",$matricula);
    $stmt->execute();
    $res = $stmt->fetch();
    return $res;
  }

  public function findByRole() {
    // Each argument is a role. Example: findByRole("admin", "technician", "client");
    $args = \func_get_args();
    $querysWhereBuilding = [];
    if (\array_search('client', $args) !== false) {
      \array_push($querysWhereBuilding, "(`u_r`.`cliente` = 1 AND `u_r`.`tecnico` = 0 AND `u_r`.`gerente` = 0)");
    }
    if (\array_search('technician', $args) !== false) {
      \array_push($querysWhereBuilding, "(`u_r`.`cliente` = 0 AND `u_r`.`tecnico` = 1 AND `u_r`.`gerente` = 0)");
    }
    if (\array_search('admin', $args) !== false) {
      \array_push($querysWhereBuilding, "(`u_r`.`cliente` = 0 AND `u_r`.`tecnico` = 0 AND `u_r`.`gerente` = 1)");
    }
    $querysWhereSetting = \implode(" OR ", $querysWhereBuilding);

    $stmt = $this->db->prepare(
      "SELECT * FROM `usuarios` AS `u` ".
      "LEFT JOIN `usuarios_roles` AS `u_r` ON `u_r`.`id_usuario` = `u`.`id` ".
      "WHERE ".$querysWhereSetting
    );
    $stmt->execute();
    $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    return $res;
  }
}
?>
