<?php

namespace SenseiTarzan\HomesManager\Task;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;
use SenseiTarzan\HomesManager\Class\Home\Home;
use SenseiTarzan\HomesManager\Component\HomeManager;
use SenseiTarzan\HomesManager\Main;
use SenseiTarzan\HomesManager\Utils\CustomKnownTranslationFactory;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;

class HomeCooldown extends Task
{

    private Position $lastPosition;

    public static array $playerInCoolDown = [];

    public function __construct(private Player $player, private int|null $timer, private Home $home)
    {
        $this->timer ??= 3;
        if (self::playerInList($this->player)) return;
        self::addPlayerInList($this->player);
        $this->lastPosition = $this->player->getPosition();
        Main::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($this, $this->timer, 20);
    }

    public function onRun(): void
    {
        if (!$this->player->isConnected()) {
            $this->getHandler()?->cancel();
            return;
        }
        if ($this->lastPosition->subtractVector($this->player->getPosition())->lengthSquared() > 2) {
            $this->player->broadcastSound(HomeManager::getInstance()->getSoundDeniedTeleportation(), [$this->player]);
            $this->player->sendActionBarMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($this->player, CustomKnownTranslationFactory::denied_clock_teleportation_player_sender()));
            $this->player->getEffects()->remove(VanillaEffects::BLINDNESS());
            $this->getHandler()?->cancel();
            return;
        }
        if ($this->timer > 0) {
            $this->player->sendActionBarMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($this->player, CustomKnownTranslationFactory::timer_clock_player_sender($this->timer--)));
            $this->player->broadcastSound(HomeManager::getInstance()->getSoundClock(), [$this->player]);
            $this->player->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 1000000, 10, false));
            return;
        }

        $this->player->getEffects()->remove(VanillaEffects::BLINDNESS());
        if (!($position = $this->home->getPosition())) {
            $this->player->broadcastSound(HomeManager::getInstance()->getSoundDeniedTeleportation(), [$this->player]);
            $this->player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($this->player, CustomKnownTranslationFactory::denied_teleportation_player_sender($this->home->getName())));
            $this->getHandler()?->cancel();
            return;
        }
        $this->player->sendActionBarMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($this->player, CustomKnownTranslationFactory::success_clock_teleportation_player_sender()));
        $this->player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($this->player, CustomKnownTranslationFactory::success_teleportation_player_sender($this->home->getName())));
        $this->player->broadcastSound(HomeManager::getInstance()->getSoundSuccessTeleportation(), [$this->player]);
        $this->player->teleport($position);
        $this->getHandler()?->cancel();
    }



    public static function playerInList(Player $player): bool{
        return in_array($player->getName(), self::$playerInCoolDown);
    }
    public static function removePlayerInList(Player $player){

        self::$playerInCoolDown = array_diff(self::$playerInCoolDown, [$player->getName()]);
    }
    private static function addPlayerInList(Player $player){

        self::$playerInCoolDown[] = $player->getName();
    }
    public function onCancel(): void
    {
        self::removePlayerInList($this->player);
    }
}