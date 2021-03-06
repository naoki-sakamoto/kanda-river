<?php

require_once(__DIR__ . "/../../vendor/autoload.php");
require_once(__DIR__ . "/../../data/config/conf.php");
require_once(__DIR__ . "/../../bin/config/define.php");

require_once(LIBPATH."mods/util.php");
require_once(LIBPATH."mods/putlog.php");
require_once(LIBPATH."mods/db.php");

/**
 * @backupGlobals disabled
 */
abstract class AbstractModelTest extends PHPUnit_Extensions_Database_TestCase {

  public $putlog;

  private $_conn = null;
  private $_dataSet = null;
  private $_existDataSet = false;
  private $_backupDataSet = null;
  private $_pdo = null;
  private $_db = null;

  /*
   * Initialize
   */
  abstract protected function init();

  /*
   * <Override>
   * Get PDO connection
   * @return PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
   */
	protected function getConnection() {
    if ($this->_conn !== null) return $this->_conn;
    $this->_pdo = new PDO(
      "mysql:host=".MASTER_DB_SERVER.";dbname=".MASTER_DB_NAME.";charset=utf8;".(defined('MASTER_DB_UNIX_SOCKET') ? "unix_socket=".MASTER_DB_UNIX_SOCKET.";" : ""),
      MASTER_DB_USER, MASTER_DB_PASSWORD,
      array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET `utf8`"));
    $this->_conn = $this->createDefaultDBConnection($this->_pdo, MASTER_DB_NAME);
		return $this->_conn;
	}

  /*
   * <Override>
   * Setup
   */
  protected function setUp() {
    $this->init();
    parent::setUp();
  }

  /*
   * <Override>
   * Get data set
   * @return PHPUnit_Extensions_Database_DataSet_DefaultDataSet
   */
  protected function getDataSet() {
    if (!$this->_dataSet) {
      $this->_dataSet = new PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
    }
    return $this->_dataSet;
  }

  /*
   * <Override>
   * Tear down
   */
  protected function tearDown() {
    $this->getDatabaseTester()->setTearDownOperation($this->getTearDownOperation());
    $this->getDatabaseTester()->setDataSet($this->getRestoreDataSet());
    $this->getDatabaseTester()->onTearDown();
  }

  /*
   * <Override>
   * Tear down operation
   */
  protected function getTearDownOperation() {
    return PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT();
  }

  /*
   * Create data set from CSV file
   * @param  array $files (table => filepath)
   * @param  string $filepath CSV filepath
   * @return PHPUnit_Extensions_Database_DataSet_CsvDataSet $dataset
   */
  protected function createCsvDataSet(array $files) {
    $dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet();
    foreach ($files as $table => $filepath) {
      $dataSet->addTable($table, $filepath);
    }
    return $dataSet;
  }

  /*
   * Set data set
   * @param  PHPUnit_Extensions_Database_DataSet_IDataSet $dataset
   */
  protected function setInitialDataSet(PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet) {
    $this->_dataSet = $dataSet;
  }

  /*
   * Backup data set
   * @param  $tables  array of tables for backup
   */
  protected function setBackupDataSet(array $tables) {
    $this->_backupDataSet = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
    foreach ($tables as $table) {
    	$this->_backupDataSet->addTable($table);
    	$this->_backupDataSet->getTableMetaData($table);
    }
	}

  /*
   * Restore data set
   * @return PHPUnit_Extensions_Database_DataSet_IDataSet
   */
  private function getRestoreDataSet() {
    return $this->_backupDataSet ? $this->_backupDataSet : new PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
	}

  /*
   * Get master db
   * @return db
   */
  protected function getDb() {
    if (!$this->_db) {
      $this->_db = new db();
      $this->_db->set_debug(true);
      $this->_db->connect(MASTER_DB_SERVER, MASTER_DB_NAME, MASTER_DB_USER, MASTER_DB_PASSWORD);
      $this->_db->set_charset("utf8");
    }
    return $this->_db;
  }
}

function d($tag,$txt,$Loc="") {
  $putlog = putlog::singleton();
  $putlog->d($tag,$txt,$Loc);
}
function dd($tag,$txt,$Loc="") {
  $putlog = putlog::singleton();
  $putlog->logput($tag,$txt,$Loc);
}
function e($tag,$txt,$Loc="") {
  $putlog = putlog::singleton();
  $putlog->e($tag,$txt,$Loc);
}
