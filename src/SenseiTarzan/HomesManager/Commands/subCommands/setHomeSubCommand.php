<?php

namespace SenseiTarzan\HomesManager\Commands\subCommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use SenseiTarzan\HomesManager\Component\HomePlayerManager;
use SenseiTarzan\HomesManager\Utils\CustomKnownTranslationFactory;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;

class setHomeSubCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission("set.home.command.permissions");
        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->registerArgument(0, new RawStringArgument("name"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($this->testPermissionSilent($sender) || $sender instanceof Player)){
            return;
        }
        $homerPlayer = HomePlayerManager::getInstance()->getPlayer($sender);
        if ($homerPlayer->existsHome($args["name"])){
            $sender->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::exist_home_player_sender($args['name'])));
            return;
        }
        $homerPlayer->addHome($args["name"], $sender->getPosition());
    }
}