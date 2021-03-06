<?php
namespace App\Model;
use SON\Db\Table;
class SolicitarChamado extends Table{
  protected $table = "solicitacao_chamado";

  public function save($id_usuario,$id_servico,$id_local,$descricao,$tombo){
    $query = "Insert into ".$this->table." (id_cliente,id_servico,id_local,descricao,tombo) values (?,?,?,?,?)";
    $params = array($id_usuario, $id_servico, $id_local, $descricao, $tombo);
    $stmt = $this->db->prepare($query);
    if ($stmt->execute($params) && $stmt->rowCount() > 0) {
      return $this->db->lastInsertId();
    } else {
      return false;
    }
  }

  public function getChamadosByStatus($status){
    $stmt = $this->db->prepare("Select * from {$this->table} where status=:status");
    $stmt->bindParam(":status",$status);
    $stmt->execute();
    $res = $stmt->fetchAll();
    return $res;
  }

  public function getTicketRequestById($ticketId){
      $stmt = $this->db->prepare(
        "SELECT `sc`.`id` AS `id_solicitacao`, `u1`.`nome` AS `cliente`, `l`.`nome` AS `local`, ".
        "`sc`.`status`, `s`.`nome` AS `servico`, `sc`.`data_solicitacao`, `sc`.`descricao`, `sc`.`tombo`, ".
        "`sc`.`data_recusado`, `u2`.`nome` AS `recusante`, `sc`.`motivo_recusa`, `sc`.`id_cliente` ".
        "FROM `{$this->table}` AS `sc` ".
        "LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico` ".
        "LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `sc`.`id_cliente` ".
        "LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `sc`.`id_recusante` ".
        "LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local` ".
        "WHERE `sc`.`id` = :ticketId ".
        "ORDER BY `sc`.`data_solicitacao`"
      );
      $stmt->bindParam(":ticketId", $ticketId);
      $stmt->execute();
      $res = $stmt->fetch(\PDO::FETCH_ASSOC);
      return $res;
  }

  public function getUsersActiveTicketRequests($userId) {
      $stmt = $this->db->prepare(
          "SELECT `sc`.`id` AS `id_solicitacao`, `u`.`nome` AS `cliente`, `l`.`nome` AS `local`, `sc`.`id_cliente`,"
          ." `sc`.`status`, `s`.`nome` AS `servico`, `sc`.`data_solicitacao`, `sc`.`descricao`, `sc`.`tombo`"
          ." FROM `{$this->table}` AS `sc`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u` ON `u`.`id` = `sc`.`id_cliente`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `sc`.`id_cliente` = :userId AND `sc`.`status` = 'AGUARDANDO'"
          ." ORDER BY `sc`.`data_solicitacao`");
      $stmt->bindParam(":userId", $userId);
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $res;
  }

  public function getUsersInactiveTicketRequests($userId) {
    $stmt = $this->db->prepare(
      "SELECT `sc`.`id` AS `id_solicitacao`, `u`.`nome` AS `cliente`, `l`.`nome` AS `local`, `sc`.`id_cliente`,"
      ." `sc`.`status`, `s`.`nome` AS `servico`, `sc`.`data_solicitacao`, `sc`.`descricao`, `sc`.`tombo`"
      ." FROM `{$this->table}` AS `sc`"
      ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
      ." LEFT JOIN `usuarios` AS `u` ON `u`.`id` = `sc`.`id_cliente`"
      ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
      ." WHERE `sc`.`id_cliente` = :userId AND `sc`.`status` <> 'AGUARDANDO'"
      ." ORDER BY `sc`.`data_solicitacao` DESC");
      $stmt->bindParam(":userId", $userId);
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $res;
    }

  public function getActiveTicketRequests() {
    $stmt = $this->db->prepare(
        "SELECT `sc`.`id` AS `id_solicitacao`, `u`.`nome` AS `cliente`, `l`.`nome` AS `local`, `sc`.`id_cliente`,"
        ." `sc`.`status`, `s`.`nome` AS `servico`, `sc`.`data_solicitacao`, `sc`.`descricao`, `sc`.`tombo`"
        ." FROM `{$this->table}` AS `sc`"
        ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
        ." LEFT JOIN `usuarios` AS `u` ON `u`.`id` = `sc`.`id_cliente`"
        ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
        ." WHERE `sc`.`status` = 'AGUARDANDO'"
        ." ORDER BY `sc`.`data_solicitacao`");
    $stmt->execute();
    $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    return $res;
  }

  public function getInactiveTicketRequests() {
    $stmt = $this->db->prepare(
        "SELECT `sc`.`id` AS `id_solicitacao`, `u`.`nome` AS `cliente`, `l`.`nome` AS `local`, `sc`.`id_cliente`,"
        ." `sc`.`status`, `s`.`nome` AS `servico`, `sc`.`data_solicitacao`, `sc`.`descricao`, `sc`.`tombo`"
        ." FROM `{$this->table}` AS `sc`"
        ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
        ." LEFT JOIN `usuarios` AS `u` ON `u`.`id` = `sc`.`id_cliente`"
        ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
        ." WHERE `sc`.`status` <> 'AGUARDANDO'"
        ." ORDER BY `sc`.`data_solicitacao` DESC");
    $stmt->execute();
    $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    return $res;
  }

}
?>
