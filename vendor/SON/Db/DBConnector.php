<?php
namespace SON\Db;
use SON\Controller\Singleton;

class DBConnector extends Singleton {
  private $dbconn;

  private $host   = 'localhost';
  private $dbname = 'gtichamados';
  private $user   = '';
  private $passw  = '';
  private $chrst  = 'utf8mb4';

  public function __construct() {
    try {
      $this->dbconn = new \PDO(
        "mysql:host={$this->host};dbname={$this->dbname};charset={$this->chrst}",
        $this->user,
        $this->passw
      );
      $this->dbconn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    } catch (\Exception $e) {
      throw new \Exception("Failed to connect with DB", 2002);
    }
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
