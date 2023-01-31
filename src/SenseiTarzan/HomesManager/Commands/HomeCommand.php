<?php

namespace SenseiTarzan\HomesManager\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use SenseiTarzan\HomesManager\Commands\subCommands\AdminSubCommand;
use SenseiTarzan\HomesManager\Commands\subCommands\removeHomeSubCommand;
use SenseiTarzan\HomesManager\Commands\subCommands\replaceHomeSubCommand;
use SenseiTarzan\HomesManager\Commands\subCommands\setHomeSubCommand;
use SenseiTarzan\HomesManager\Component\HomeManager;
use SenseiTarzan\HomesManager\Component\HomePlayerManager;
use SenseiTarzan\HomesManager\Task\HomeCooldown;
use SenseiTarzan\HomesManager\Utils\CustomKnownTranslationFactory;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;

class HomeCommand extends BaseCommand
{

    protected function prepare(): void
    {
        $this->setPermission("home.command.permissions");
        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->registerSubCommand(new AdminSubCommand($this->plugin, "admin"));
        $this->registerSubCommand(new setHomeSubCommand($this->plugin,"set", aliases: ["add"]));
        $this->registerSubCommand(new replaceHomeSubCommand($this->plugin, "replace"));
        $this->registerSubCommand(new removeHomeSubCommand($this->plugin, "remove", aliases: ["rm", "del", "delete"]));
        $this->registerArgument(0, new RawStringArgument("homeName", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($this->testPermissionSilent($sender) || $sender instanceof Player)){
            return;
        }
        if (!isset($args["homeName"])){
            $sender->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::list_home_player_sender(HomePlayerManager::getInstance()->getPlayer($sender)->getHomes())));
            return;
        }
        $home = HomePlayerManager::getInstance()->getPlayer($sender)->getHome($args["homeName"]);
        if (!$home){
            $sender->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::error_home_no_exist($args["homeName"])));
            return;
        }
        if (HomeManager::getInstance()->getTimer() === false){
            $sender->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::success_teleportation_player_sender($home->getName())));
            if (!($position = $home->getPosition())) {
                $sender->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::denied_teleportation_player_sender($home->getName())));
                return;
            }
            $sender->teleport($position);
            return;
        }
        new HomeCooldown($sender,HomeManager::getInstance()->getTimer(),$home);
    }
}