<?php

namespace OguzhanUmutlu\TheFloorIsLava;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use OguzhanUmutlu\TheFloorIsLava\LavaTask;

class Main extends PluginBase {
  public $worlds = [];

  public function onEnable(): void {
    $this->saveResource("config.yml");
    $this->config = new Config($this->getDataFolder()."config.yml");
    $this->getScheduler()->scheduleRepeatingTask(new LavaTask($this), 20);
  }
  public function getWoByName(string $name) {
    $result = null;
    foreach($this->worlds as $w) {
      if($w["name"] == $name) {
        $result = $w;
      }
    }
    return $result;
  }
  public function updateWoByName(string $name, array $new) {
    $es = [];
    foreach($this->worlds as $w) {
      if($w["name"] != $name) {
        array_push($es, $w);
      }
    }
    array_push($es, ["name" => ($this->getWoByName($name) ? $this->getWoByName($name)["name"] : ($new["name"] ?? $name)), "speedtime" => ($this->getWoByName($name) ? ($new["speedtime"] ?? $this->getWoByName($name)["speedtime"]) : ($new["speedtime"] ?? 0)), "stop" => ($this->getWoByName($name) ? ($new["stop"] ?? $this->getWoByName($name)["stop"]) : ($new["stop"] ?? false)), "slowness" => ($this->getWoByName($name) ? ($new["slowness"] ?? $this->getWoByName($name)["slowness"]) : ($new["slowness"] ?? $this->config->get("default-slowness"))), "high" => ($this->getWoByName($name) ? ($new["high"] ?? $this->getWoByName($name)["high"]) : ($new["high"] ?? 0))]);
    $this->worlds = $es;
    return $es;
  }
  public function removeWoByName(string $name) {
    $es = [];
    foreach($this->worlds as $w) {
      if($w["name"] != $name) {
        array_push($es, $w);
      }
    }
    $this->worlds = $es;
    return $this->worlds;
  }
  public function getWoList() {
    return $this->worlds;
  }
  public function onCommand(CommandSender $player, Command $command, $label, array $args): bool{
    if($command->getName() != "thefloorislava") {
      return true;
    }
    if(!$player->hasPermission("thefloorislava.cmd")) {
      $player->sendMessage("§c> You don't have permission to use this command.");
      return true;
    }
    if(count($args) == 0) {
      $player->sendMessage("§c> Usage: /tfil [start, set-slowness, stop, stop-all, reset, reset-all, set-high]");
      return true;
    }
    if(strtolower($args[0]) == "start") {
      if(count($args) == 1) {
        $player->sendMessage("§c> Usage: /tfil start [worldName]");
        return true;
      }
      if(!$this->getServer()->getLevelByName($args[1])) {
        $player->sendMessage("§c> World §4".$args[1]." §cnot found.");
      }
      $this->updateWoByName($args[1], ["stop" => false]);
      $player->sendMessage("§a> The floor is lava enabled in §2".$args[1]."§a.");
    } else if(strtolower($args[0]) == "set-slowness") {
      if(count($args) == 1) {
        $player->sendMessage("§c> Usage: /tfil set-slowness [worldName] [slowness(seconds)]");
        return true;
      }
      if(!$this->getServer()->getLevelByName($args[1])) {
        $player->sendMessage("§c> World §4".$args[1]." §cnot found.");
      }
      if(count($args) == 2) {
        $player->sendMessage("§c> Usage: /tfil set-slowness [worldName] [slowness(seconds)]");
        return true;
      }
      if(!is_numeric($args[2])) {
        $player->sendMessage("§c> §4".$args[2]."§c is not an integer.");
        return true;
      }
      $this->updateWoByName($args[1], ["slowness" => (int)$args[2]]);
      $player->sendMessage("§a> The floor is lava's slowness set to §2".$args[2]."§a in §2".$args[1]."§a.");
    } else if(strtolower($args[0]) == "stop") {
      if(count($args) == 1) {
        $player->sendMessage("§c> Usage: /tfil stop [worldName]");
        return true;
      }
      if(!$this->getServer()->getLevelByName($args[1])) {
        $player->sendMessage("§c> World §4".$args[1]." §cnot found.");
        return true;
      }
      $this->updateWoByName($args[1], ["stop" => true]);
      $player->sendMessage("§a> The floor is lava stopped in §2".$args[1]."§a.");
    } else if(strtolower($args[0]) == "stop-all") {
      $sec = [];
      foreach($this->worlds as $w) {
        if($w["stop"] == false) {
          array_push($sec, $w);
        }
      }
      foreach($sec as $w) {
        $this->updateWoByName($w["name"], ["stop" => true]);
        $player->sendMessage("§a> The floor is lava stopped in §2".$w["name"]."§a.");
      }
      if(count($sec) == 0) {
        $player->sendMessage("§c> There is no active the floor is lava world.");
      }
    } else if(strtolower($args[0]) == "reset") {//reset reset-all set-high
      if(count($args) == 1) {
        $player->sendMessage("§c> Usage: /tfil reset [worldName]");
        return true;
      }
      if(!$this->getServer()->getLevelByName($args[1])) {
        $player->sendMessage("§c> World §4".$args[1]." §cnot found.");
        return true;
      }
      $this->updateWoByName($args[1], ["high" => 0, "stop" => true]);
      $player->sendMessage("§a> The floor is lava reset and stopped in §2".$args[1]."§a.");
    } else if(strtolower($args[0]) == "reset-all") {
      foreach($this->worlds as $w) {
        $this->updateWoByName($w["name"], ["high" => 0, "stop" => true]);
        $player->sendMessage("§a> The floor is lava reset and stopped in §2".$w["name"]."§a.");
      }
      if(count($this->worlds) == 0) {
        $player->sendMessage("§c> There is no active the floor is lava world.");
        return true;
      }
    } else if(strtolower($args[0]) == "set-high") {
      if(count($args) == 1) {
        $player->sendMessage("§c> Usage: /tfil set-high [worldName] [high(Y coordinate)]");
        return true;
      }
      if(!$this->getServer()->getLevelByName($args[1])) {
        $player->sendMessage("§c> World §4".$args[1]." §cnot found.");
        return true;
      }
      if(count($args) == 2) {
        $player->sendMessage("§c> Usage: /tfil set-high [worldName] [high(Y coordinate)]");
        return true;
      }
      if(!is_numeric($args[2])) {
        $player->sendMessage("§c> §4".$args[2]."§c is not an integer.");
        return true;
      }
      $this->updateWoByName($args[1], ["high" => (int)$args[2]]);
      $player->sendMessage("§a> The floor is lava's high set to §2".$args[2]."§a in §2".$args[1]."§a.");
    } else {
      $this->getServer()->dispatchCommand($player, "tfil");
    }
    return true;
  }
}
