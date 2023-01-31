<?php

namespace SenseiTarzan\HomesManager\Component;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use SenseiTarzan\HomesManager\Class\Home\HomePlayer;
use SenseiTarzan\HomesManager\Class\Sound\HomeSound;
use SenseiTarzan\HomesManager\Main;
use SenseiTarzan\HomesManager\Utils\CustomKnownTranslationFactory;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;

class HomeManager
{
    use SingletonTrait;

    private Config $config;
    private Main $plugin;
    /**
     * @var HomeSound[]
     */
    private array $sounds;
    /**
     * @var int[]
     */
    private array $sizeByPermission = [];

    public function __construct(Main $pl)
    {
        self::setInstance($this);
        $this->plugin = $pl;
        $this->config = $pl->getConfig();
        $this->loadSound();
        $this->sortMaxHome();
    }

    public function getSoundDeniedTeleportation(): HomeSound{
        return $this->sounds["denied"] ?? new HomeSound("note.bassattack");
    }
    public function getSoundClock(): HomeSound{
        return $this->sounds["clock"] ?? new HomeSound("note.harp");
    }

    public function getSoundSuccessTeleportation(): HomeSound{

        return $this->sounds["success"] ?? new HomeSound("note.pling");
    }

    public function loadSound(): void{
        foreach ($this->config->get("sound") as $name => $information){
            $this->sounds[strtolower($name)] = new HomeSound($information['name'], $information['volume'], $information['pitch']);
        }
    }

    private function sortMaxHome(): void
    {
        $sizeByPermissions = $this->config->get("size-home-permission", []);
        arsort($sizeByPermissions);
        $this->sizeByPermission = $sizeByPermissions;
    }

    public function getMaxHomeByPermissions(Player $player): int
    {
        foreach ($this->sizeByPermission as $permission => $maxHome) {
            if ($player->hasPermission($permission)) return $maxHome;
        }
        return $this->sizeByPermission[array_key_last($this->sizeByPermission)] ?? 5;
    }


    public function adminIndexUI(Player $player, HomePlayer $homePlayer): void
    {
        $ui = new SimpleForm(function (Player $player, ?string $homeId) use($homePlayer): void{
            if ($homeId === null) {
                return;
            }
            $this->adminHomeUI($player, $homePlayer, $homeId);
        });
        $ui->setTitle(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::title_home_list()));

        foreach ($homePlayer->getHomes() as $homeId => $home){
            $ui->addButton($home->getName(), label: $homeId);
        }
        $player->sendForm($ui);
    }

    public function adminHomeUI(Player $player, HomePlayer $homePlayer, string $homeId): void
    {
        $ui = new SimpleForm(function (Player $player, ?int $data) use ($homePlayer, $homeId): void {
            if ($data === null) {
                $this->adminIndexUI($player, $homePlayer);
                return;
            }
            if ($data === 1) {
                if (!$homePlayer->removeHome($homeId)) {
                    $player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::error_home_no_exist($$homeId)));
                    return;
                }
                $player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::remove_home_player_admin($homePlayer->getPlayerName(), $homeId)));
                return;
            }
            if (!($home = $homePlayer->getHome($homeId))) {
                $player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::error_home_no_exist($homeId)));
                return;
            }
            if (!($position = $home->getPosition())) {
                $player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::denied_teleportation_player_sender($home->getName())));

                return;
            }
            $player->teleport($position);
            $player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::teleport_home_player_admin($homePlayer->getPlayerName(),$home->getName())));
        });
        $ui->setTitle(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::title_home_select($homeId)));
        $ui->addButton(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::button_teleport_home_admin()));
        $ui->addButton(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::button_remove_home_admin()));
        $player->sendForm($ui);
    }

    public function getTimer(): int|false
    {
        return $this->config->get("timer", 3);
    }
}