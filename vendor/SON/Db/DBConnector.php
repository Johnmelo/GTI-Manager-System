<?php
namespace SON\Db;
use SON\Controller\Singleton;

class DBConnector extends Singleton {
  private $dbconn;

  private $host   = 'localhost';
  private $dbname = 'gtichamados';
  private $user   = 'root';
  private $passw  = '793549';
  private $chrst  = 'utf8mb4';

  public function __construct() {
    $this->dbconn = new \PDO(
      "mysql:host={$this->host};dbname={$this->dbname};charset={$this->chrst}",
      $this->user,
      $this->passw
    );
  }

  public function getConnection() {
    return $this->dbconn;
  }

  public function beginTransaction() {
    return $this->dbconn->beginTransaction();
  }

  public function commit() {
    return $this->dbconn->commit();
  }

  public function rollback() {
    return $this->dbconn->rollback();
  }
}
