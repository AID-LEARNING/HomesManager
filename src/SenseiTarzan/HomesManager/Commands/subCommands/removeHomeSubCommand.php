<?php

namespace SenseiTarzan\HomesManager\Commands\subCommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use SenseiTarzan\HomesManager\Component\HomePlayerManager;
use SenseiTarzan\HomesManager\Utils\CustomKnownTranslationFactory;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;

class removeHomeSubCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission("remove.home.command.permissions");
        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->registerArgument(0, new RawStringArgument("name"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($this->testPermissionSilent($sender) || $sender instanceof Player)){
            return;
        }
        if (!HomePlayerManager::getInstance()->getPlayer($sender)->removeHome($args["name"])){
            $sender->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::error_home_no_exist($args["name"])));
            return;
        }
        $sender->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::remove_home_player_sender($args["name"])));

    }
}