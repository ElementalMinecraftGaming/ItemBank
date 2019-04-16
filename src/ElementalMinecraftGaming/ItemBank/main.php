<?php

namespace ElementalMinecraftGaming\ItemBank;
    
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
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
            }
            if (!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "IN GAME ONLY!");
            }
            if (!isset($args[0])) {
                $sender->sendMessage(TextFormat::RED . "No ID!");
            }
            if (!isset($args[1])) {
                $sender->sendMessage(TextFormat::RED . "No amount!");
            }
            $amount = $args[1];
            $owner = strtolower($sender);
            $id = $args[0];
            $check = $this->getBankAmount($owner,$id);
            $umm = $this->getBankExist($owner,$id);
            if (!$check = 0 or !$umm == true) {
            $stmt = $this->db->prepare("INSERT OR REPLACE INTO bank (player, id, amount) VALUES (:player, :id, :amount);");
            $stmt->bindValue(":player", $owner);
            $stmt->bindValue(":id", $id);
            $stmt->bindValue(":amount", $amount);
            $stmt->execute();
            return true;
            }
            $stmt = $this->db->prepare("INSERT OR REPLACE INTO bank (player, id, amount) VALUES (:player, :id, :amount);");
            $stmt->bindValue(":player", $owner);
            $stmt->bindValue(":id", $id);
            $stmt->bindValue(":amount", $this->getBankAmount($sender,$id) + $amount);
            $stmt->execute();
        }
        
        if (strtolower($command->getName()) == "retrieveitem") {
            if (!$sender->hasPermission("bankt.item")) {
                $sender->sendMessage(TextFormat::RED . "No permissions!");
            }
            if (!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "IN GAME ONLY!");
            }
            if (!isset($args[0])) {
                $sender->sendMessage(TextFormat::RED . "No ID!");
            }
            if (!isset($args[1])) {
                $sender->sendMessage(TextFormat::RED . "No amount!");
            }
            $amount = $args[1];
            $owner = strtolower($sender);
            $id = $args[0];
            $umm = $this->getBankExist($owner,$id);
            if (!$umm == true) {
                $sender->sendMessage(TextFormat::RED . "No bank created!");
            }
            $check = $this->getBankAmount($owner,$id);
            if (!$check = $amount) {
                $sender->sendMessage(TextFormat::RED . "Not enough stored!");
            }
            $stmt = $this->db->prepare("INSERT OR REPLACE INTO bank (player, id, amount) VALUES (:player, :id, :amount);");
            $stmt->bindValue(":player", $owner);
            $stmt->bindValue(":id", $id);
            $stmt->bindValue(":amount", $this->getBankAmount($sender,$id) - $amount);
            $stmt->execute();
            
        }
    }
    
    public function getBankAmount($owner,$id) {
        $check = $this->db->query("SELECT amount FROM bank WHERE player = '$owner' AND id = '$id';");
        $oof = $check->fetchArray(SQLITE3_ASSOC);
        return (int) $oof["amount"];
    }
    
    public function getBankExist($owner,$id) {
        $stuff = strtolower($owner);
        $stufff = strtolower($id);
        $result = $this->db->query("SELECT player, id FROM bank WHERE player ='$stuff' AND id = '$stufff';");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        return empty($array) == false;
    }

}
