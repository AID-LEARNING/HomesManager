<?php

namespace SenseiTarzan\HomesManager\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use Exception;
use pocketmine\command\CommandSender;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use SenseiTarzan\HomesManager\Class\Exception\HomeNotFoundException;
use SenseiTarzan\HomesManager\Class\Exception\HomePositionInvalidException;
use SenseiTarzan\HomesManager\Class\Home\Home;
use SenseiTarzan\HomesManager\Commands\subCommands\AdminSubCommand;
use SenseiTarzan\HomesManager\Commands\subCommands\removeHomeSubCommand;
use SenseiTarzan\HomesManager\Commands\subCommands\replaceHomeSubCommand;
use SenseiTarzan\HomesManager\Commands\subCommands\setHomeSubCommand;
use SenseiTarzan\HomesManager\Component\HomeManager;
use SenseiTarzan\HomesManager\Component\HomePlayerManager;
use SenseiTarzan\HomesManager\Main;
use SenseiTarzan\HomesManager\Utils\CustomKnownTranslationFactory;
use SenseiTarzan\HomesManager\Utils\HomeCooldown;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;
use SOFe\AwaitGenerator\Await;

class HomeCommand extends BaseCommand
{

    protected function prepare(): void
    {
        $this->setPermission("home.command.permissions");
        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->registerSubCommand(new AdminSubCommand($this->plugin, "admin"));
        $this->registerSubCommand(new setHomeSubCommand($this->plugin, "set", aliases: ["add"]));
        $this->registerSubCommand(new replaceHomeSubCommand($this->plugin, "replace"));
        $this->registerSubCommand(new removeHomeSubCommand($this->plugin, "remove", aliases: ["rm", "del", "delete"]));
        $this->registerArgument(0, new RawStringArgument("homeName", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($this->testPermissionSilent($sender) || $sender instanceof Player)) {
            return;
        }
        if (!isset($args["homeName"])) {
            Await::g2c(HomePlayerManager::getInstance()->getPlayer($sender)->getHomes(), function (array $homes) use ($sender) {
                $sender->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::list_home_player_sender($homes)));
            });
            return;
        }
        $homeId = $args["homeName"];

        Await::g2c(HomePlayerManager::getInstance()->getPlayer($sender)->getHome($homeId), function (Home $home) use ($sender): void {
            $timer = HomeManager::getInstance()->getTimer();
            if ($timer === false) {
                $sender->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::success_teleportation_player_sender($home->getName())));
                $sender->teleport($home->getPosition());
                return;
            }
            if (HomeCooldown::playerInList($sender)) {
                return;
            }

            $lastPosition = $sender->getPosition();

            Await::f2c(
            /**
             * @throws Exception
             */
                function () use ($sender, $lastPosition, $timer) {
                    HomeCooldown::addPlayerInList($sender);
                    for ($clock = $timer; $clock > 0; $clock--) {
                        if (!$sender->isConnected()) {
                            throw new Exception(message: "Player disconnected", code: 960);
                        }
                        if ($lastPosition->subtractVector($sender->getPosition())->lengthSquared() > 2) {
                            throw new Exception(message: "Player moved", code: 980);
                        }
                        $sender->sendActionBarMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::timer_clock_player_sender($clock)));
                        $sender->broadcastSound(HomeManager::getInstance()->getSoundClock(), [$sender]);
                        $sender->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 1000000, 10, false));
                        yield from Main::sleeper();
                    }
                    $sender->getEffects()->remove(VanillaEffects::BLINDNESS());
                }, function () use ($sender, $home) {
                HomeCooldown::removePlayerInList($sender);
                $sender->sendActionBarMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::success_clock_teleportation_player_sender()));
                $sender->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::success_teleportation_player_sender($home->getName())));
                $sender->broadcastSound(HomeManager::getInstance()->getSoundSuccessTeleportation(), [$sender]);
                $sender->teleport($home->getPosition());
            }, function (Exception $exception) use ($sender) {
                HomeCooldown::removePlayerInList($sender);
                if ($exception->getCode() === 960) return;
                $sender->broadcastSound(HomeManager::getInstance()->getSoundDeniedTeleportation(), [$sender]);
                $sender->sendActionBarMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::denied_clock_teleportation_player_sender()));
                $sender->getEffects()->remove(VanillaEffects::BLINDNESS());

            });
        }, [
            HomeNotFoundException::class => function (HomeNotFoundException $exception) use ($sender): void {
                $sender->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::error_home_no_exist($exception->getMessage())));

            },
            HomePositionInvalidException::class => function (HomePositionInvalidException $exception) use ($sender) {
                $sender->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::denied_teleportation_player_sender($exception->getMessage())));
            }
        ]);


    }
}