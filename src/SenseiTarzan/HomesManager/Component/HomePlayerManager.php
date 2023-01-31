<?php

namespace SenseiTarzan\HomesManager\Component;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use SenseiTarzan\HomesManager\Class\Home\HomePlayer;
use WeakMap;

class HomePlayerManager
{
    use SingletonTrait;
    private WeakMap $players;
    public function __construct()
    {
        $this->players = new WeakMap();
    }

    public function loadPlayer(Player $player): void{
         $this->players[$player] = new HomePlayer($player);
    }
    public function loadPlayerOffline(string $player): HomePlayer{
         return new HomePlayer($player);
    }

    public function getPlayer(Player|string $player): HomePlayer{
        return $this->players[$player->getName()] ??= $this->loadPlayerOffline($player);
    }

    public function unloadPlayer(Player $player): void{
        $this->players->offsetUnset($player);
    }

}