<?php

namespace SenseiTarzan\HomesManager\Listener;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use SenseiTarzan\ExtraEvent\Class\EventAttribute;
use SenseiTarzan\HomesManager\Class\Exception\PlayerMoveException;
use SenseiTarzan\HomesManager\Component\HomePlayerManager;

class PlayerListener
{

    #[EventAttribute]
    public function onJoin(PlayerJoinEvent $event): void
    {
        HomePlayerManager::getInstance()->loadPlayer($event->getPlayer());
    }

    #[EventAttribute]
    public function onMove(PlayerMoveEvent $event): void
    {
        $form = $event->getFrom();
        $to = $event->getTo();
        $homePlayer = HomePlayerManager::getInstance()->getPlayer($event->getPlayer());
        if ($homePlayer->isGoHome() && ceil($form->distance($to)) > 0) {
            $homePlayer->setIsMove();
        }
    }

    #[EventAttribute]
    public function onQuit(PlayerQuitEvent $event): void
    {
        HomePlayerManager::getInstance()->unloadPlayer($event->getPlayer());
    }

}