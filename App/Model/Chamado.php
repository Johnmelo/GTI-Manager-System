<?php
namespace App\Model;
use SON\Db\Table;
class Chamado extends Table{
  protected $table = "chamados";

  public function getChamadosByStatus($status){
    $stmt = $this->db->prepare("Select * from {$this->table} where status=:status");
    $stmt->bindParam(":status",$status);
    $stmt->execute();
    $res = $stmt->fetchAll();
    return $res;
  }

  public function getChamadosByColumn($columnName, $value){
    $stmt = $this->db->prepare("Select * from {$this->table} where {$columnName}=:value");
    $stmt->bindParam(":value",$value);
    $stmt->execute();
    $res = $stmt->fetchAll();
    return $res;
  }

  public function getUserOpenedRequests($userId) {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `u1`.`nome` AS `cliente`,"
          ." `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`, `c`.`status`, `sc`.`data_solicitacao`,"
          ." `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`,"
          ." `u3`.`nome` AS `tecnico_responsavel`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `usuarios` AS `u3` ON `u3`.`id` = `c`.`id_tecnico_responsavel`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`id_cliente_solicitante` =:userId"
          ." ORDER BY `c`.`data_abertura` ASC");
      $stmt->bindParam(":userId", $userId);
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $res;
  }

  public function save($service_id, $place_id, $request_id, $id_open_technician, $id_client, $deadline, $description){
    $query = "Insert into ".$this->table." (id_servico,id_local,id_solicitacao,id_tecnico_abertura,id_cliente_solicitante,prazo,descricao) values (?,?,?,?,?,?,?)";
    $params = array($service_id, $place_id, $request_id, $id_open_technician, $id_client, $deadline, $description);
    $stmt = $this->db->prepare($query);
    if ($stmt->execute($params) && $stmt->rowCount() > 0) {
      return true;
    } else {
      return false;
    }
  }

}
?>
