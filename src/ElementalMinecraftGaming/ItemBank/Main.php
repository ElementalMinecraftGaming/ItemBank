<?php

namespace ElementalMinecraftGaming\ItemBank;
    
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\item\item;
use pocketmine\inventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener
 {
    
    public $db;

    public function onEnable() {
        $this->getLogger()->info("Created by MrDevCat -Discord- ");
        @mkdir($this->getDataFolder());

        $this->db = new \SQLite3($this->getDataFolder() . "BankItem.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS bank(player TEXT PRIMARY KEY, id INT, amount INT);");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (strtolower($command->getName()) == "storeitem") {
            if (!$sender->hasPermission("bank.item")) {
                $sender->sendMessage(TextFormat::RED . "No permissions!");
                return true;
            }
            if (!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "IN GAME ONLY!");
                return true;
            }
            if (!isset($args[0])) {
                $sender->sendMessage(TextFormat::RED . "No ID!");
                return true;
            }
            if (!isset($args[1])) {
                $sender->sendMessage(TextFormat::RED . "No amount!");
                return true;
            }
            if (!$sender->getInventory()->contains(Item::get($args[0]))) {
                $sender->sendMessage(TextFormat::RED . "No $args[0] in your inventory!");
                return true;
            }
            if (!$this->getBankExist($sender->getName(),$args[0])) {
            $amount = $args[1];
            $owner = $sender->getName();
            $id = $args[0];
            $sender->getInventory()->removeItem(Item::get($args[0], 0, $args[1]));
            $stmt = $this->db->prepare("INSERT OR REPLACE INTO bank (player, id, amount) VALUES (:player, :id, :amount);");
            $stmt->bindValue(":player", $owner);
            $stmt->bindValue(":id", $id);
            $stmt->bindValue(":amount", $amount);
            $stmt->execute();
            $sender->sendMessage(TextFormat::BLUE . "You have created an item account!");
            return true;
            }
            $amount = $args[1];
            $owner = $sender->getName();
            $id = $args[0];
            $add = $this->getBankAmount($owner,$args[0]);
            $sender->getInventory()->removeItem(Item::get($args[0], 0, $args[1]));
            $stmt = $this->db->prepare("INSERT OR REPLACE INTO bank (player, id, amount) VALUES (:player, :id, :amount);");
            $stmt->bindValue(":player", $owner);
            $stmt->bindValue(":id", $id);
            $stmt->bindValue(":amount", $add + $amount);
            $stmt->execute();
            $sender->sendMessage(TextFormat::BLUE . "You have added to your bank!");
            return false;
        }
        
        if (strtolower($command->getName()) == "retrieveitem") {
            if (!$sender->hasPermission("bankt.item")) {
                $sender->sendMessage(TextFormat::RED . "No permissions!");
                return true;
            }
            if (!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "IN GAME ONLY!");
                return true;
            }
            if (!isset($args[0])) {
                $sender->sendMessage(TextFormat::RED . "No ID!");
                return true;
            }
            if (!isset($args[1])) {
                $sender->sendMessage(TextFormat::RED . "No amount!");
                return true;
            }
            if (!$this->getBankExist($sender->getName(),$args[0]) == true) {
                $sender->sendMessage(TextFormat::RED . "No bank created!");
                return true;
            }
            if (!$this->getBankAmount($sender->getName(),$args[0]) <= $args[1]) {
                $sender->sendMessage(TextFormat::RED . "Not enough stored!");
                return true;
            }
            $amount = $args[1];
            $owner = $sender->getName();
            $id = $args[0];
            $stmt = $this->db->prepare("INSERT OR REPLACE INTO bank (player, id, amount) VALUES (:player, :id, :amount);");
            $stmt->bindValue(":player", $owner);
            $stmt->bindValue(":id", $id);
            $stmt->bindValue(":amount", $this->getBankAmount($owner,$id) - $amount);
            $stmt->execute();
            $sender->getInventory()->addItem(Item::get($args[0], 0, $args[1]));
            $sender->sendMessage(TextFormat::BLUE . "Success!");
            return false;
        }
        
        if (strtolower($command->getName()) == "bankitem") {
            if (!$sender->hasPermission("bankt.item")) {
                $sender->sendMessage(TextFormat::RED . "No permissions!");
                return true;
            }
            if (!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "IN GAME ONLY!");
                return true;
            }
            if (!isset($args[0])) {
                $sender->sendMessage(TextFormat::RED . "No ID!");
                return true;
            }
            if (!$this->getBankExist($sender->getName(),$args[0])) {
                $sender->sendMessage(TextFormat::RED . "No bank created!");
                return true;
            }
            $owner = $sender->getName();
            $id = $args[0];
            $amount = $this->getBankAmount($owner, $id);
            $sender->sendMessage(TextFormat::BLUE . "You have $amount of $id!");
            return false;
        }

        if (strtolower($command->getName()) == "bankhelp") {
            if (!$sender->hasPermission("bankt.item")) {
                $sender->sendMessage(TextFormat::RED . "No permissions!");
                return true;
            }
            if (!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "IN GAME ONLY!");
                return true;
            }
            $sender->sendMessage(TextFormat::BLUE . "\n/storei {id} {amount}\n/retrievei {id} {amount}\n/banki {id}\n/bankhelp!");
            return false;
        }
    }
    
    public function getBankAmount($owner,$id) {
        $check = $this->db->query("SELECT amount FROM bank WHERE player = '$owner' AND id = '$id';");
        $oof = $check->fetchArray(SQLITE3_ASSOC);
        return (int) $oof["amount"];
    }
    
    public function getBankExist($owner,$id) {
        $stuff = $owner;
        $stufff = $id;
        $result = $this->db->query("SELECT player, id FROM bank WHERE player ='$stuff' AND id = '$stufff';");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        return empty($array) == false;
    }

}
