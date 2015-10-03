<?php
require_once(__DIR__ ."/../lib/AbstractModelTest.php");
require_once(LIBPATH . "model/player.php");
use app\model\player;

/**
 * @backupGlobals disabled
 */
class test_player_by_csv extends AbstractModelTest {

  private $_player = null;

  /*
   * <Override>
   * 初期処理
   */
  protected function init() {
    // テーブルバックアップ
    $this->setBackupDataSet(array("player"));
    // CSVデータセット取得
    $dataSet = $this->createCsvDataSet(array(
      "player" => __DIR__ . "/../data/player.csv"
    ));

    $this->setInitialDataSet($dataSet);

    $this->_player = new player($this->getDb(), $this->getDb());
  }

  /**
   * @test
   */
  public function get_該当なし() {
    $player = $this->_player->get(7);
    $this->assertFalse($player);
  }

  /**
   * @test
   */
  public function get_該当あり() {
    $player = $this->_player->get(22);
    // オブジェクト取得を確認
    $this->assertNotFalse($player);
    $this->assertEquals(md5("ohno"), $player["password"]);
    $this->assertEquals("大野雄大", $player["name"]);
    $this->assertEquals("ohno@dragons.jp", $player["email"]);
    $this->assertEquals("pitcher", $player["position"]);
  }

  /**
   * @test
   */
  public function get_SQLインジェクションチェック() {
    $player = $this->_player->get("0 or id != 0");
    $this->assertFalse($player);
  }

  /**
   * @test
   */
  public function findByName_該当なし() {
    $players = $this->_player->findByName("山本昌");
    $this->assertEquals(0, count($players));
  }

  /**
   * @test
   */
  public function findByName_該当あり() {
    $players = $this->_player->findByName("杉山翔大");
    $this->assertEquals(1, count($players));
    $this->assertEquals(45, $players[0]["id"]);
  }

  /**
   * @test
   */
  public function findByName_SQLインジェクションチェック() {
    $players = $this->_player->findByName("' or ''='");
    $this->assertEquals(0, count($players));
  }

  /**
   * @test
   */
  public function findByPosition_該当なし() {
    $players = $this->_player->findByPosition("infielder");
    $this->assertEquals(0, count($players));
  }

  /**
   * @test
   */
  public function findByPosition_該当あり() {
    $players = $this->_player->findByPosition("pitcher");
    $this->assertEquals(2, count($players));
  }

  /**
   * @test
   */
  public function findByPosition_SQLインジェクションチェック() {
    $players = $this->_player->findByPosition("' or ''='");
    $this->assertEquals(0, count($players));
  }


}
