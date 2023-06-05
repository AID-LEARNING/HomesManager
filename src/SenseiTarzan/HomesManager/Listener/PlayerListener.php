<?php

namespace SenseiTarzan\HomesManager\Listener;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use SenseiTarzan\ExtraEvent\Class\EventAttribute;
use SenseiTarzan\HomesManager\Component\HomePlayerManager;

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