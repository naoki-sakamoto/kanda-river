<?php
require_once(__DIR__ ."/../lib/AbstractModelTest.php");
require_once(LIBPATH . "model/player.php");
use app\model\player;

/**
 * @backupGlobals disabled
 */
class test_player extends AbstractModelTest {

  private $_player = null;

  /*
   * <Override>
   * 初期処理
   */
  protected function init() {
    // テーブルバックアップ
    $this->setBackupDataSet(array("player"));
    // テーブル初期値セット
    $dataSet = $this->createArrayDataSet(array(
      'player' => array(
        array(
          'id' => 16, 'password' => md5("matayoshi"), 'name' => "又吉克樹", 'email' => "matayoshi@dragons.jp", 'position' => "pitcher",
        ), array(
          'id' => 55, 'password' => md5("fukuda"), 'name' => "福田永将", 'email' => "fukuda@dragons.jp", 'position' => "infielder",
        ), array(
          'id' => 61, 'password' => md5("wakamatsu"), 'name' => "若松駿太", 'email' => "wakamatsu@dragons.jp", 'position' => "pitcher",
        ),
      ),
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
    $player = $this->_player->get(16);
    // オブジェクト取得を確認
    $this->assertNotFalse($player);
    $this->assertEquals(md5("matayoshi"), $player["password"]);
    $this->assertEquals("又吉克樹", $player["name"]);
    $this->assertEquals("matayoshi@dragons.jp", $player["email"]);
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
    $players = $this->_player->findByName("谷繁元信");
    $this->assertEquals(0, count($players));
  }

  /**
   * @test
   */
  public function findByName_該当あり() {
    $players = $this->_player->findByName("福田永将");
    $this->assertEquals(1, count($players));
    $this->assertEquals(55, $players[0]["id"]);
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
    $players = $this->_player->findByPosition("outfielder");
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
