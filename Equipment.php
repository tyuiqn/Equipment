<?php

/**
 * @name Equipment
 * @main Securti\equipment\Equipment
 * @author ["Securti"]
 * @version 0.1
 * @api 3.9.0
 * @description This plugin is made by Securti. :3
 */
 
 namespace Securti\equipment;
 
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\nbt\tag\StringTag;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

use pocketmine\Player;
use pocketmine\event\player\PlayerItemHeldEvent;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityArmorChangeEvent;

use securti\level\Level;

class Equipment extends PluginBase implements Listener {

  private $grade = ["§e★", "§e★★", "§e★★★", "§e★★★★", "§e★★★★★"];
  
  public static $instance;

  public static function getInstance(){

    return self::$instance;
  }
  public function onLoad(){
    
    self::$instance = $this;
  }
  
  public function onEnable(){

    $this->getServer()->getPluginManager()->registerEvents($this,$this);
    
    $a = new PluginCommand("장비", $this);
    $a->setPermission("op");
    $a->setUsage("/장비");
    $a->setDescription("장비 관리 명령어입니다");
    $this->getServer()->getCommandMap()->register($this->getDescription()->getName(), $a);
  }
  public function onCommand(CommandSender $sender, Command $command, string $label, array $array) :bool{
  
    $player = $sender;
    
    if(!$player instanceof Player) return true;
    
    $this->Add($player);
    
    return true;
  }
  public function getUI(DataPacketReceiveEvent $e){

    $pack = $e->getPacket();
    $player = $e->getPlayer();
    $name = strtolower($player->getName());
    
    $prefix = "§l§e[§f알림§e] §f";

    if($pack instanceof ModalFormResponsePacket and $pack->formId == 38187){

      $button = json_decode($pack->formData, true);
      
      if($button[0] == null or $button[1] == null or $button[2] == null or $button[4] == null or $button[5] == null or $button[6] == null or $button[7] == null or $button[8] == null or $button[9] == null){
      
        $player->sendMessage($prefix."모든 정보를 정확히 입력해주세요");
      }
      else{
      
        if(!is_numeric($button[2]) or !is_numeric($button[4]) or !is_numeric($button[5]) or !is_numeric($button[6]) or !is_numeric($button[7]) or !is_numeric($button[8]) or !is_numeric($button[9])){
        
          $player->sendMessage($prefix."일부 기능은 숫자로 입력해야합니다");
        }
        else{
        
          $inventory = $player->getInventory();
          $item = $inventory->getItemInHand();
          
          $item->setCustomName($button[0]);
          $lore = "§e- - - - - - - - - - -\n§r§f".$button[1];
          $lore .= "\n§r§e- - - - - - - - - - -\n§r§f레벨 제한 : ".(int) $button[2];
          $lore .= "\n§f장비 등급 : ".$this->grade[$button[3]]."\n§r§f강화 : §e+§f0"; 
          $lore .= "\n§e- - - §f장비 능력 §e- - -";
          $lore .= "\n§f공격 : ".(int) $button[4];
          $lore .= "\n방어 : ".(int) $button[5];
          $lore .= "\n체력 : ".(int) $button[6];
          $lore .= "\n크리 : ".(int) $button[7];
          $lore .= "\n행운 : ".(int) $button[8];
          $lore .= "\n회피 : ".(int) $button[9];
          
          $lore = str_replace("(줄바꿈)", "\n", $lore);
          
          $item->setLore([$lore]);
          
          $item->setNamedTagEntry(new StringTag("장비", "O"));
          $item->setNamedTagEntry(new StringTag("레벨", (int) $button[2]));
          $item->setNamedTagEntry(new StringTag("공격", (int) $button[4]));
          $item->setNamedTagEntry(new StringTag("방어", (int) $button[5]));
          $item->setNamedTagEntry(new StringTag("체력", (int) $button[6]));
          $item->setNamedTagEntry(new StringTag("크리", (int) $button[7]));
          $item->setNamedTagEntry(new StringTag("행운", (int) $button[8]));
          $item->setNamedTagEntry(new StringTag("회피", (int) $button[9]));
          
          $inventory->setItemInHand($item);
          
          $player->sendMessage($prefix."장비를 제작하였습니다");
        }
      }
    }
  }
  public function Add(Player $player){

    $prefix = "§l§e· §f";

    $encode = json_encode([

      "type" => "custom_form",   
      "title" => "§l§e[ §f장비 추가 §e]",    
      "content" => [
              [
                  "type" => "input",
                  "text" => $prefix."장비명을 적어주세요",
                  "default" => ""
              ],
              [
                  "type" => "input",
                  "text" => $prefix."장비 설명을 적어주세요\n(줄바꿈) - 텍스트를 1줄 내립니다",
                  "default" => ""
              ],
              [
                  "type" => "input",
                  "text" => $prefix."사용 제한 레벨을 적어주세요",
                  "default" => ""
              ],
              [
                  "type" => "dropdown",
                  "text" => $prefix."장비 등급을 골라주세요",
                  "options" => $this->grade
              ],
              [
                  "type" => "input",
                  "text" => $prefix."공격 능력치를 적어주세요",
                  "default" => ""
              ],
              [
                  "type" => "input",
                  "text" => $prefix."방어 능력치를 적어주세요",
                  "default" => ""
              ],
              [
                  "type" => "input",
                  "text" => $prefix."체력 능력치를 적어주세요",
                  "default" => ""
              ],
              [
                  "type" => "input",
                  "text" => $prefix."크리 능력치를 적어주세요",
                  "default" => ""
              ],
              [
                  "type" => "input",
                  "text" => $prefix."행운 능력치를 적어주세요",
                  "default" => ""
              ],
              [
                  "type" => "input",
                  "text" => $prefix."회피 능력치를 적어주세요",
                  "default" => ""
              ]
          ]
      ]);
    
    $pack = new ModalFormRequestPacket();
    $pack->formId = 38187;
    $pack->formData = $encode;
    $player->dataPacket($pack);
  }
  public function onHeld(PlayerItemHeldEvent $e){
  
    $player = $e->getPlayer();
    
    $prefix = "§l§e[§f알림§e] §f";
    
    $item = $e->getItem();
    
    if($item->getNamedTagEntry("장비") !== null){
    
      $instance = Level::getInstance();
      $level = $instance->getLevel($player);
     
      if($item->getNamedTagEntry("레벨")->getValue() > $level){
      
        $e->setCancelled();
        
        $player->sendMessage($prefix."해당 장비를 사용하기에 레벨이 부족합니다!");
      }
    }
  }
  public function onArmorChange(EntityArmorChangeEvent $e){
  
    $entity = $e->getEntity();
    
    $prefix = "§l§e[§f알림§e] §f";
    
    $item = $e->getNewItem();
    
    if($entity instanceof Player){
    
      if($item->getNamedTagEntry("장비") !== null){
    
        $instance = Level::getInstance();
        $level = $instance->getLevel($entity);
     
        if($item->getNamedTagEntry("레벨")->getValue() > $level){
      
          $e->setCancelled();
        
          $entity->sendMessage($prefix."해당 장비를 사용하기에 레벨이 부족합니다!");
          
          $entity->getInventory()->addItem($item);
        }
      }
    }
  }
}