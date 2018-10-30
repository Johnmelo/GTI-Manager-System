<?php
namespace App\Model;
use SON\Db\Table;
class SolicitarAcesso extends Table{
  protected $table = "solicitacao_cadastro";

  public function getUnreviewedRequests() {
      $query =
      "SELECT `id`, `nome`, `email`, `login`, `setor`, `matricula`, `data_solicitacao` ".
      "FROM `solicitacao_cadastro` WHERE `status` = \"AGUARDANDO\" ".
      "ORDER BY `data_solicitacao`  ASC";
      $stmt = $this->db->prepare($query);
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $res;
  }

  public function save($nome,$email,$login,$setor,$matricula){
    $query = "Insert into ".$this->table." (nome,email,login,setor,matricula) values (?,?,?,?,?)";
    $params = array($nome, $email, $login, $setor, $matricula);
    $stmt = $this->db->prepare($query);
    if ($stmt->execute($params) && $stmt->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
  }

}
?>
