<?php

namespace SenseiTarzan\HomesManager\Listener;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use SenseiTarzan\ExtraEvent\Class\EventAttribute;
use SenseiTarzan\HomesManager\Component\HomePlayerManager;
use SenseiTarzan\HomesManager\Task\HomeCooldown;

class PlayerListener
{

    #[EventAttribute]
    public function onJoin(PlayerJoinEvent $event): void
    {
        HomePlayerManager::getInstance()->loadPlayer($event->getPlayer());
    }

    #[EventAttribute]
    public function onQuit(PlayerQuitEvent $event): void
    {
        HomePlayerManager::getInstance()->unloadPlayer($event->getPlayer());
    }

}