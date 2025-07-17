<?php

namespace NetherByte\NetherScoreboard;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;

class ScoreboardPlugin extends PluginBase implements Listener {
    /** @var Config */
    private $config;
    /** @var string */
    private $scoreboardTitle;
    /** @var array */
    private $scoreboardLines;
    /** @var int */
    private $updateInterval;
    /** @var string */
    private $purseFormat;

    public function onEnable(): void {
        @mkdir($this->getDataFolder() . "resources/");
        $configPath = $this->getDataFolder() . "resources/config.yml";
        if (!file_exists($configPath) || filesize($configPath) < 10) {
            // Use the latest config template
            file_put_contents($configPath, <<<YAML
scoreboard:
  title: "§l§bNether§eScoreboard"
  lines:
    - "§aPlayer: §f{name}"
    - "§aMoney: §6{money}"
    - "§aServer: §b{server_name}"
    - "§aOnline: §e{online}/{max_online}"
    - "§aPing: §d{ping}"
    - "§7----------------"
    - "§eNetherByte"
    - "§eTo"
    - "§eSubscribe"
update-interval: 20
purse-format: abbreviated # 'normal', 'abbreviated', or 'mixed' (mixed = normal up to 100 million, abbreviated above)

# Some variables you can also use:
#   {server_ip}
#   {server_port}
YAML
            );
        }
        $this->config = new Config($configPath, Config::YAML);
        $scoreboard = $this->config->get("scoreboard", []);
        $this->scoreboardTitle = $scoreboard["title"] ?? "§b§lYourServer";
        $this->scoreboardLines = $scoreboard["lines"] ?? [
            "§7Name: §f{name}",
            "§7Money: §a{money}",
            "§7Online: §b{online}",
            "§7Server IP: §f{server_ip}",
            "§7Port: §f{server_port}",
            "§7Website: §ewww.example.com",
        ];
        $this->updateInterval = (int)($this->config->get("update-interval", 40));
        $this->purseFormat = $this->config->get("purse-format", "abbreviated");
        $this->getLogger()->info("ScoreboardPlugin Enabled!");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        // Repeating task to update scoreboard
        $this->getScheduler()->scheduleRepeatingTask(
            new ClosureTask(function() {
                foreach ($this->getServer()->getOnlinePlayers() as $player) {
                    $this->sendScoreboard($player);
                }
            }),
            $this->updateInterval
        );
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $this->sendScoreboard($event->getPlayer());
    }

    public function sendScoreboard(Player $player): void {
        // Remove old scores
        $removePacket = new SetScorePacket();
        $removePacket->type = SetScorePacket::TYPE_REMOVE;
        $removePacket->entries = [];
        $score = count($this->scoreboardLines);
        for ($i = $score; $i > 0; $i--) {
            $entry = new ScorePacketEntry();
            $entry->objectiveName = "scoreboard";
            $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
            $entry->customName = ""; // Not needed for removal
            $entry->score = $i;
            $entry->scoreboardId = $i;
            $removePacket->entries[] = $entry;
        }
        $player->getNetworkSession()->sendDataPacket($removePacket);

        // Now send the new scoreboard
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = "sidebar";
        $pk->objectiveName = "scoreboard";
        $pk->displayName = $this->replaceVariables($player, $this->scoreboardTitle);
        $pk->criteriaName = "dummy";
        $pk->sortOrder = 0;
        $player->getNetworkSession()->sendDataPacket($pk);

        $entries = [];
        $lines = [];
        foreach ($this->scoreboardLines as $line) {
            $lines[] = $this->replaceVariables($player, $line);
        }
        $score = count($lines);
        foreach ($lines as $line) {
            $entry = new ScorePacketEntry();
            $entry->objectiveName = "scoreboard";
            $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
            $entry->customName = $line;
            $entry->score = $score--;
            $entry->scoreboardId = $entry->score;
            $entries[] = $entry;
        }
        $pk2 = new SetScorePacket();
        $pk2->type = SetScorePacket::TYPE_CHANGE;
        $pk2->entries = $entries;
        $player->getNetworkSession()->sendDataPacket($pk2);
    }

    private function replaceVariables(Player $player, string $text): string {
        $text = str_replace("{name}", $player->getName(), $text);
        $text = str_replace("{money}", $this->getMoney($player), $text);
        $text = str_replace("{online}", (string)count(Server::getInstance()->getOnlinePlayers()), $text);
        $text = str_replace("{max_online}", (string)Server::getInstance()->getMaxPlayers(), $text);
        $text = str_replace("{ping}", (string)$player->getNetworkSession()->getPing(), $text);
        $text = str_replace("{server_ip}", Server::getInstance()->getIp(), $text);
        $text = str_replace("{server_port}", (string)Server::getInstance()->getPort(), $text);
        return $text;
    }

    private function getMoney(Player $player): string {
        $eco = $this->getServer()->getPluginManager()->getPlugin("NetherEconomy");
        if ($eco !== null && method_exists($eco, "getPurse") && method_exists($eco, "formatShortNumber")) {
            $amount = $eco->getPurse($player->getName());
            if ($this->purseFormat === "abbreviated") {
                return $eco::formatShortNumber($amount);
            } elseif ($this->purseFormat === "mixed") {
                if ($amount < 100000000) {
                    return (string)$amount;
                } else {
                    return $eco::formatShortNumber($amount);
                }
            } else {
                return (string)$amount;
            }
        }
        return "0";
    }
} 