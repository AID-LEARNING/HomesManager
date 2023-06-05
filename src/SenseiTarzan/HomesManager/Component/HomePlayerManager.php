<?php

namespace SenseiTarzan\HomesManager\Component;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use SenseiTarzan\HomesManager\Class\Home\HomePlayer;
use SenseiTarzan\HomesManager\Utils\HomeCooldown;

class HomePlayerManager
{
    use SingletonTrait;
    private array $players = [];
    public function __construct()
    {
    }

    public function loadPlayer(Player $player): void{
         $this->players[strtolower($player->getName())] = new HomePlayer($player);
    }
    public function loadPlayerOffline(string $player): HomePlayer{
         return new HomePlayer($player);
    }

    public function getPlayer(Player|string $player): HomePlayer{
        $name= $player instanceof Player  ?$player->getName() : $player;
        return $this->players[strtolower($name)] ?? $this->loadPlayerOffline($name);
    }

    public function unloadPlayer(Player $player): void{
        unset($this->players[strtolower($player->getName())]);
        if (HomeCooldown::playerInList($player)){
            HomeCooldown::removePlayerInList($player);
            $player->getEffects()->remove(VanillaEffects::BLINDNESS());
        }
    }

}