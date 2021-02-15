<?php

namespace OguzhanUmutlu\TheFloorIsLava;

use pocketmine\scheduler\Task;
use OguzhanUmutlu\TheFloorIsLava\Main;

class LavaTask extends Task {
  public function __construct(Main $plugin) {
    $this->p = $plugin;
  }
  public function onRun(int $tick) {
    $worlds = $this->p->worlds;
    foreach($worlds as $w) {
      $w = $this->p->getWoByName($w["name"]);
      if($w["high"] > 254) {
        $this->p->removeWoByName($w["name"]);
      } else {
        if($w["stop"] == false && $this->p->getServer()->getLevelByName($w["name"])) {
          $level = $this->p->getServer()->getLevelByName($w["name"]);
          $players = $level->getPlayers();
          $this->p->updateWoByName($w["name"], ["speedtime" => $w["speedtime"]+1]);
          if($w["speedtime"]+1 > $w["slowness"]-1) {
            $this->p->updateWoByName($w["name"], ["speedtime" => 0, "high" => $w["high"]+1]);
            $lavasize = (int)$this->p->config->get("lavasize");
            foreach($players as $p) {
              for($x=$p->getPosition()->getX()-$lavasize;$x<($p->getPosition()->getX()+$lavasize);$x++) {
                for($z=$p->getPosition()->getZ()-$lavasize;$z<($p->getPosition()->getZ()+$lavasize);$z++) {
                  for($y=0;$y<$w["high"];$y++) {
                    if($level->getBlockIdAt((int)$x, (int)$y, (int)$z) != 10 && $level->getBlockIdAt((int)$x, (int)$y, (int)$z) != 11) {
                      $level->setBlockIdAt((int)$x, (int)$y, (int)$z, 11);
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}
