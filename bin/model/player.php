<?php
namespace app\model;
require_once(LIBPATH . "mods/util.php");
require_once(LIBPATH . "mods/common.php");

class player extends \util {

  private $_common;

  /**
   * コンストラクタ
   * @param $_db_master
   * @param $_db_slave
   */
  public function __construct($_db_master, $_db_slave)
  {
    $this->set_db($_db_master, $_db_slave);
    $this->_common = new \common();
  }

  /**
   * IDで取得
   * @param int $id
   * @param player $player
   */
  public function get($id)
  {
    // SQLインジェクション対策
    if (!is_int($id)) {
      e("player.getPlayer", "Invalid parameter[{$id}]");
      return false;
    }

    $player = array();
    $this->getSingleResult("SELECT id, password, name, email, position FROM player WHERE id={$id}", $player);
    return $player;
  }

  /**
   * 名前で検索
   * @param string $name
   * @param array(player) $players
   */
  public function findByName($name)
  {
    // SQLインジェクション対策
    $name = $this->escapeString($name);

    $players = array();
    $this->getResultList("SELECT id, password, name, email, position FROM player WHERE name='{$name}'", $players);
    return $players;
  }

  /**
   * ポジションで検索
   * @param int $position
   * @param array(player) $players
   */
  public function findByPosition($position)
  {
    // SQLインジェクション対策
    $position = $this->escapeString($position);

    $players = array();
    $this->getResultList("SELECT id, password, name, email, position FROM player WHERE position='{$position}'", $players);
    return $players;
  }

}
