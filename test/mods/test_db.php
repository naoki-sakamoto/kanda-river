<?php
require_once(__DIR__ . "/../lib/AbstractModsTest.php");
require_once(__DIR__ . "/../../bin/mods/db.php");

/**
 * @backupGlobals disabled
 */
class test_db extends AbstractModsTest {

  private $_db = null;

  /*
   * <Override>
   * 初期設定
   */
  protected function init() {
    // テーブルバックアップ
    $this->setBackupDataSet(array("player"));
    // テーブル初期値セット
    $dataSet = $this->createArrayDataSet(array(
      'player' => array(
        array('id' => 16, 'password' => md5('matayoshi'), 'name' => "又吉 克樹", 'email' => "matayoshi@dragons.jp"),
        array('id' => 22, 'password' => md5('ohno'), 'name' => "大野 雄大", 'email' => "ohno@dragons.jp"),
        array('id' => 61, 'password' => md5('wakamatsu'), 'name' => "若松 駿太", 'email' => "wakamatsu@dragons.jp"),
      ),
    ));
    $this->setInitialDataSet($dataSet);

    $this->_db = new db();
  }

  /**
   * @ No test
   * IPアドレスが異なるとタイムアウトまで時間がかかるので除外
   */
  public function connect_サーバ名NG() {
    $Server = '111.111.111.111';
    $DbName = MASTER_DB_NAME;
    $User = MASTER_DB_USER;
    $Password = MASTER_DB_PASSWORD;
    print("\n[Server=$Server,DbName=$DbName,User=$User,Password=$Password]\n");

    $result = $this->_db->connect($Server, $DbName, $User, $Password);
    $this->assertFalse($result);
  }

  /**
   * @test
   */
  public function connect_データベース名NG() {
    $Server = MASTER_DB_SERVER;
    $DbName = 'invalid';
    $User = MASTER_DB_USER;
    $Password = MASTER_DB_PASSWORD;
    print("\n[Server=$Server,DbName=$DbName,User=$User,Password=$Password]\n");

    $result = $this->_db->connect($Server, $DbName, $User, $Password);
    $this->assertFalse($result);
  }

  /**
   * @test
   */
  public function connect_ユーザー名NG() {
    $Server = MASTER_DB_SERVER;
    $DbName = MASTER_DB_NAME;
    $User = 'invalid';
    $Password = MASTER_DB_PASSWORD;
    print("\n[Server=$Server,DbName=$DbName,User=$User,Password=$Password]\n");

    $result = $this->_db->connect($Server, $DbName, $User, $Password);
    $this->assertFalse($result);
  }

  /**
   * @test
   */
  public function connect_パスワードNG() {
    $Server = MASTER_DB_SERVER;
    $DbName = MASTER_DB_NAME;
    $User = MASTER_DB_USER;
    $Password = 'invalid';
    print("\n[Server=$Server,DbName=$DbName,User=$User,Password=$Password]\n");

    $result = $this->_db->connect($Server, $DbName, $User, $Password);
    $this->assertFalse($result);
  }

  /**
   * @test
   */
  public function connect_正常接続・解除() {
    $Server = MASTER_DB_SERVER;
    $DbName = MASTER_DB_NAME;
    $User = MASTER_DB_USER;
    $Password = MASTER_DB_PASSWORD;
    print("\n[Server=$Server,DbName=$DbName,User=$User,Password=$Password]\n");

    // 接続
    $result = $this->_db->connect($Server, $DbName, $User, $Password);
    $this->assertTrue($result);

    // 解除
    $result = $this->_db->close();
    $this->assertTrue($result);
  }

  /**
   * @test
   */
  public function close_接続しないで接続解除() {
    // 解除
    $result = $this->_db->close();
    $this->assertFalse($result);
  }

  /**
   * @test
   */
  public function Query_接続無しで実行() {
    $result = $this->_db->Query('SELECT player_id,player_name FROM player WHERE delete_flag=0');
    $this->assertFalse($result);
  }

  /**
   * @test
   */
  public function Query_SQL不正() {
    $this->_db->connect(MASTER_DB_SERVER, MASTER_DB_NAME, MASTER_DB_USER, MASTER_DB_PASSWORD);
    $this->_db->set_charset("utf8");
    $result = $this->_db->Query('SELECT _player_id,player_name FROM player WHERE delete_flag=0');
    $this->assertFalse($result);
    $this->_db->close();
  }

  /**
   * @test
   */
  public function Query_正常実行() {
    $this->_db->connect(MASTER_DB_SERVER, MASTER_DB_NAME, MASTER_DB_USER, MASTER_DB_PASSWORD);
    $this->_db->set_charset("utf8");
    $result = $this->_db->Query('SELECT player_id,player_name FROM player WHERE delete_flag=0 ORDER BY player_id');

    // 結果取得を確認
    $this->assertInstanceOf('mysqli_result', $result);
    // 行数を確認
    $this->assertEquals(2, $result->num_rows);

    $this->_db->close();
  }

  /**
   * @test
   */
  public function QueryEx_接続無しで実行() {
    $result = $this->_db->QueryEx("SELECT player_id,player_name FROM player WHERE delete_flag=0");
    $this->assertFalse($result);
  }

  /**
   * @test
   */
  public function QueryEx_SQL不正() {
    $this->_db->connect(MASTER_DB_SERVER, MASTER_DB_NAME, MASTER_DB_USER, MASTER_DB_PASSWORD);
    $this->_db->set_charset("utf8");
    $result = $this->_db->QueryEx("SELECT _player_id,player_name FROM player WHERE delete_flag=0");
    $this->assertFalse($result);
    $this->_db->close();
  }

  /**
   * @test
   */
  public function QueryEx_正常実行() {
    $this->_db->connect(MASTER_DB_SERVER, MASTER_DB_NAME, MASTER_DB_USER, MASTER_DB_PASSWORD);
    $this->_db->set_charset("utf8");
    $result = $this->_db->QueryEx("SELECT player_id,player_name FROM player WHERE delete_flag=0 ORDER BY player_id");

    // 配列取得確認
    $expect = array(
      array('player_id' => "16", 'player_name' => "又吉克樹"),
      array('player_id' => "55", 'player_name' => "福田永将"),
    );
    $this->assertSame($expect, $result);

    $this->_db->close();
  }

  /**
   * @test
   */
  public function Query_追加() {
    $this->_db->connect(MASTER_DB_SERVER, MASTER_DB_NAME, MASTER_DB_USER, MASTER_DB_PASSWORD);
    $this->_db->set_charset("utf8");

    // 正常終了を確認
    $result = $this->_db->Query("INSERT INTO player(player_id,player_name,delete_flag) VALUES (22,'大野雄大',0)");
    $this->assertTrue($result);

    // 挿入されたことを確認
    $expect = array(
      array('player_id' => "16", 'player_name' => "又吉克樹"),
      array('player_id' => "22", 'player_name' => "大野雄大"),
      array('player_id' => "55", 'player_name' => "福田永将"),
    );
    $data = $this->_db->QueryEx("SELECT player_id,player_name FROM player WHERE delete_flag=0 ORDER BY player_id");
    $this->assertSame($expect, $data);

    $this->_db->close();
  }

  /**
   * @test
   */
  public function Query_更新() {
    $this->_db->connect(MASTER_DB_SERVER, MASTER_DB_NAME, MASTER_DB_USER, MASTER_DB_PASSWORD);
    $this->_db->set_charset("utf8");

    // 正常終了を確認
    $result = $this->_db->Query("UPDATE player SET mail_address='butter@dragons.jp' WHERE player_id=16");
    $this->assertTrue($result);

    // 更新されたことを確認
    $data = $this->_db->QueryEx("SELECT mail_address FROM player WHERE player_id=16");
    $this->assertSame(array(array('mail_address' => "butter@dragons.jp")), $data);

    $this->_db->close();
  }

  /**
   * @test
   */
  public function Query_削除() {
    $this->_db->connect(MASTER_DB_SERVER, MASTER_DB_NAME, MASTER_DB_USER, MASTER_DB_PASSWORD);
    $this->_db->set_charset("utf8");

    // 正常終了を確認
    $result = $this->_db->Query("DELETE FROM player WHERE player_id=16");
    $this->assertTrue($result);

    // 削除されたことを確認
    $expect = array(
      array('player_id' => "55", 'player_name' => "福田永将"),
    );
    $data = $this->_db->QueryEx("SELECT player_id,player_name FROM player WHERE delete_flag=0 ORDER BY player_id");
    $this->assertSame($expect, $data);

    $this->_db->close();
  }

  /**
   * @test
   */
  public function トランザクションテスト() {
    $this->_db->connect(MASTER_DB_SERVER, MASTER_DB_NAME, MASTER_DB_USER, MASTER_DB_PASSWORD);
    $this->_db->set_charset("utf8");

    // 自動コミット無効化
    $this->_db->set_autocommit(false);
    // トランザクション開始
    $this->_db->beginTransaction();

    // 更新
    $result = $this->_db->Query("UPDATE player SET mail_address='butter@dragons.jp' WHERE player_id=16");
    $this->assertTrue($result);

    // 更新を確認
    $data = $this->_db->QueryEx("SELECT mail_address FROM player WHERE player_id=16");
    $this->assertSame(array(array('mail_address' => "butter@dragons.jp")), $data);

    // ロールバック
    $this->_db->rollback();

    // 更新前の状態に戻ったことを確認
    $data = $this->_db->QueryEx("SELECT mail_address FROM player WHERE player_id=16");
    $this->assertSame(array(array('mail_address' => "pitcher@dragons.jp")), $data);

    // 更新→コミット→ロールバック
    $result = $this->_db->Query("UPDATE player SET mail_address='coach@dragons.jp' WHERE player_id=16");
    $this->assertTrue($result);
    $this->_db->commit();
    $this->_db->rollback();

    // 更新を確認
    $data = $this->_db->QueryEx("SELECT mail_address FROM player WHERE player_id=16");
    $this->assertSame(array(array('mail_address' => "coach@dragons.jp")), $data);

    $this->_db->close();
  }

}
