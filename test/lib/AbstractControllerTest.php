<?php
require_once(__DIR__ . "/../../vendor/autoload.php");
require_once(__DIR__ . "/../../data/config/conf.php");
require_once(__DIR__ . "/../../bin/config/define.php");

/**
 * @backupGlobals disabled
 */
abstract class AbstractControllerTest extends PHPUnit_Extensions_Database_TestCase {

  private $_conn = null;
  private $_dataSet = null;
  private $_existDataSet = false;
  private $_backupDataSet = null;
  private $_pdo = null;
  private $_app = null;

  /*
   * Initialize
   */
  abstract protected function init();

  /*
   * <Override>
   * Get PDO connection
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
    @session_start();
    chdir(__DIR__ . "/../../app/bin");
  }

  /*
   * <Override>
   * Get data set
   */
  protected function getDataSet() {
    if (!$this->_existDataSet) {
      $this->_dataSet = new PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
      $this->_existDataSet = true;
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
   * Set data set
   */
  protected function setInitialDataSet(PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet) {
    $this->_dataSet = $dataSet;
    $this->_existDataSet = true;
  }

  /*
   * Backup data set
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
   */
  private function getRestoreDataSet() {
    return $this->_backupDataSet ? $this->_backupDataSet : new PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
	}

  /*
   * Get app instance
   */
  protected function getApp() {
    if ($this->_app) return $this->_app;
    require_once("app.php");
    $this->_app = new app();
    return $this->_app;
  }

  /*
   * Execute app
   */
  protected function execute() {
    $app = $this->getApp();
    if (!$app) {
      print("Cannot get app instance.\n");
      return false;
    }
    return true;
  }
}
