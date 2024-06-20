<?php

namespace SenseiTarzan\HomesManager\Utils;

use pocketmine\player\Player;

class HomeCooldown
{


    private static array $playerInCoolDown = [];


    public static function playerInList(Player $player): bool{
        return in_array($player->getName(), self::$playerInCoolDown);
    }
    public static function removePlayerInList(Player $player): void
    {

        self::$playerInCoolDown = array_diff(self::$playerInCoolDown, [$player->getName()]);
    }
    public static function addPlayerInList(Player $player): void
    {

        self::$playerInCoolDown[] = $player->getName();
    }
}