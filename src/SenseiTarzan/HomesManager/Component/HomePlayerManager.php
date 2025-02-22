<?php

namespace SenseiTarzan\HomesManager\Component;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use SenseiTarzan\HomesManager\Class\Home\HomePlayer;
use SenseiTarzan\HomesManager\Task\HomeCooldown;
use WeakMap;

class HomePlayerManager
{
    use SingletonTrait;

    /**
     * @var array<string, HomePlayer>
     */
    private array $players = [];
    public function __construct()
    {
    }

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function loadPlayer(Player $player): void{
         $this->players[strtolower($player->getName())] = new HomePlayer(\WeakReference::create($player));
    }
    public function loadPlayerOffline(string $player): HomePlayer{
         return new HomePlayer($player);
    }

    public function getPlayer(Player|string $player): HomePlayer{
        $name= $player instanceof Player  ?$player->getName() : $player;
        return $this->players[strtolower($name)] ?? $this->loadPlayerOffline($name);
    }

    public function unloadPlayer(Player $player): void{
        if (isset($this->players[strtolower($player->getName())]))
            $this->players[strtolower($player->getName())]->save();
        unset($this->players[strtolower($player->getName())]);
        if (HomeCooldown::playerInList($player)){
            HomeCooldown::removePlayerInList($player);
            $effect = $player->getEffects()->get(VanillaEffects::BLINDNESS());
            if ($effect->getAmplifier() === 255)
                $player->getEffects()->remove(VanillaEffects::BLINDNESS());
        }
    }

}