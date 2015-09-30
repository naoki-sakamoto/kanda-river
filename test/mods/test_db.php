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
        array('id' => 16, 'password' => md5('matayoshi'), 'name' => "又吉 克樹", 'email' => "matayoshi@dragons.jp", 'position' => "pitcher"),
        array('id' => 22, 'password' => md5('ohno'), 'name' => "大野 雄大", 'email' => "ohno@dragons.jp", 'position' => "pitcher"),
        array('id' => 61, 'password' => md5('wakamatsu'), 'name' => "若松 駿太", 'email' => "wakamatsu@dragons.jp", 'position' => "pitcher"),
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
    $result = $this->_db->Query('SELECT * FROM player');
    $this->assertFalse($result);
  }

  /**
   * @test
   */
  public function Query_SQL不正() {
    $this->_db->connect(MASTER_DB_SERVER, MASTER_DB_NAME, MASTER_DB_USER, MASTER_DB_PASSWORD);
    $this->_db->set_charset("utf8");
    $result = $this->_db->Query('SELECT id,password,name,email,position,age FROM player');
    $this->assertFalse($result);
    $this->_db->close();
  }

  /**
   * @test
   */
  public function Query_正常実行() {
    $this->_db->connect(MASTER_DB_SERVER, MASTER_DB_NAME, MASTER_DB_USER, MASTER_DB_PASSWORD);
    $this->_db->set_charset("utf8");
    $result = $this->_db->Query('SELECT id,name FROM player WHERE id > 20 ORDER BY id');

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
    $result = $this->_db->QueryEx("SELECT id,player FROM player");
    $this->assertFalse($result);
  }

  /**
   * @test
   */
  public function QueryEx_SQL不正() {
    $this->_db->connect(MASTER_DB_SERVER, MASTER_DB_NAME, MASTER_DB_USER, MASTER_DB_PASSWORD);
    $this->_db->set_charset("utf8");
    $result = $this->_db->QueryEx("SELECT id,passwooooooood,name FROM player");
    $this->assertFalse($result);
    $this->_db->close();
  }

  /**
   * @test
   */
  public function QueryEx_正常実行() {
    $this->_db->connect(MASTER_DB_SERVER, MASTER_DB_NAME, MASTER_DB_USER, MASTER_DB_PASSWORD);
    $this->_db->set_charset("utf8");
    $result = $this->_db->QueryEx("SELECT id,name FROM player WHERE id > 20 ORDER BY id");

    // 配列取得確認
    $expect = array(
      array('id' => "22", 'name' => "大野 雄大"),
      array('id' => "61", 'name' => "若松 駿太"),
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
    $result = $this->_db->Query("INSERT INTO player(id,password,name,email) VALUES (34,'".md5('yamamoto')."','山本 昌','yamamoto@dragons.jp')");
    $this->assertTrue($result);

    // 挿入されたことを確認
    $expect = array(
      array('id' => "22", 'name' => "大野 雄大"),
      array('id' => "34", 'name' => "山本 昌"),
      array('id' => "61", 'name' => "若松 駿太"),
    );
    $data = $this->_db->QueryEx("SELECT id,name FROM player WHERE id > 20 ORDER BY id");
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
    $result = $this->_db->Query("UPDATE player SET email='ohno@yankees.us' WHERE id=22");
    $this->assertTrue($result);

    // 更新されたことを確認
    $data = $this->_db->QueryEx("SELECT email FROM player WHERE id=22");
    $this->assertSame(array(array('email' => "ohno@yankees.us")), $data);

    $this->_db->close();
  }

  /**
   * @test
   */
  public function Query_削除() {
    $this->_db->connect(MASTER_DB_SERVER, MASTER_DB_NAME, MASTER_DB_USER, MASTER_DB_PASSWORD);
    $this->_db->set_charset("utf8");

    // 正常終了を確認
    $result = $this->_db->Query("DELETE FROM player WHERE id=22");
    $this->assertTrue($result);

    // 削除されたことを確認
    $expect = array(
      array('id' => "61", 'name' => "若松 駿太"),
    );
    $data = $this->_db->QueryEx("SELECT id,name FROM player WHERE id > 20 ORDER BY id");
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
    $result = $this->_db->Query("UPDATE player SET email='matayoshi@giants.jp' WHERE id=16");
    $this->assertTrue($result);

    // 更新を確認
    $data = $this->_db->QueryEx("SELECT email FROM player WHERE id=16");
    $this->assertSame(array(array('email' => "matayoshi@giants.jp")), $data);

    // ロールバック
    $this->_db->rollback();

    // 更新前の状態に戻ったことを確認
    $data = $this->_db->QueryEx("SELECT email FROM player WHERE id=16");
    $this->assertSame(array(array('email' => "matayoshi@dragons.jp")), $data);

    // 更新→コミット→ロールバック
    $result = $this->_db->Query("UPDATE player SET email='matayoshi@tigers.jp' WHERE id=16");
    $this->assertTrue($result);
    $this->_db->commit();
    $this->_db->rollback();

    // 更新を確認
    $data = $this->_db->QueryEx("SELECT email FROM player WHERE id=16");
    $this->assertSame(array(array('email' => "matayoshi@tigers.jp")), $data);

    $this->_db->close();
  }

}
