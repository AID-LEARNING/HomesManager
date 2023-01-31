<?php

namespace SenseiTarzan\HomesManager\Utils;

use pocketmine\lang\Translatable;
use pocketmine\world\Position;
use SenseiTarzan\HomesManager\Class\Home\Home;

class CustomKnownTranslationFactory
{

    public static function format_home_list_string(array $homes): string
    {
        return implode(", ", $homes);
    }

    /**
     * @param array $homes
     * @return Translatable
     */
    public static function list_home_player_sender(array $homes): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::LIST_HOME_PLAYER_SENDER, ["count" => count($homes), "homes" => self::format_home_list_string(array_map(function (Home $home){ return $home->getName(); }, $homes))]);
    }
    public static function add_home_player_sender(string $name, Position $position): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::ADD_HOME_PLAYER_SENDER, ["home" => $name, "x" => $position->getFloorX(),"y" =>  $position->getFloorY(), "z" => $position->getFloorZ(), "world" => $position->getWorld()->getDisplayName()]);
    }

    public static function replace_home_player_sender(string $name, Position $position): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::REPLACE_HOME_PLAYER_SENDER, ["home" => $name, "x" => $position->getFloorX(),"y" =>  $position->getFloorY(), "z" => $position->getFloorZ(), "world" => $position->getWorld()->getDisplayName()]);
    }
    public static function remove_home_player_sender(string $name): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::REMOVE_HOME_PLAYER_SENDER, ["home" => $name]);
    }

    public static function error_home_no_exist(string $home): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::ERROR_HOME_NO_EXIST, ["home" => $home]);
    }

    public static function remove_home_player_admin(string $name, string $home): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::REMOVE_HOME_PLAYER_ADMIN, ["player" => $name, "home" => $home]);
    }

    public static function teleport_home_player_admin(string $name, string $home): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::TELEPORT_HOME_PLAYER_ADMIN, ["player" => $name,"home" => $home]);
    }

    public static function success_teleportation_player_sender(string $name): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::SUCCESS_TELEPORTATION_PLAYER_SENDER, ["home" => $name]);
    }


    public static function denied_teleportation_player_sender(string $name): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::DENIED_TELEPORTATION_PLAYER_SENDER, ["home" => $name]);
    }

    public static function error_home_max(string $maxHome): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::ERROR_HOME_MAX, ["max" => $maxHome]);
    }

    public static function timer_clock_player_sender(int $timer): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::TIMER_CLOCK_PLAYER_SENDER, ["clock" => $timer]);
    }

    public static function success_clock_teleportation_player_sender(): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::SUCCESS_CLOCK_TELEPORTATION_PLAYER_SENDER, []);
    }

    public static function denied_clock_teleportation_player_sender(): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::DENIED_CLOCK_TELEPORTATION_PLAYER_SENDER, []);
    }

    public static function title_home_list(string $name): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::TITLE_HOME_LIST, ["player" => $name]);
    }

    public static function title_home_select(string $name): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::TITLE_HOME_SELECT, ["home" => $name]);
    }

    public static function button_teleport_home_admin(): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::BUTTON_TELEPORT_HOME_ADMIN, []);
    }

    public static function button_remove_home_admin(): Translatable
    {
        return new Translatable(CustomKnownTranslationKeys::BUTTON_REMOVE_HOME_ADMIN, []);
    }


}