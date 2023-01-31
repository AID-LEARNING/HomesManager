<?php

namespace SenseiTarzan\HomesManager\Commands\subCommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use SenseiTarzan\HomesManager\Component\HomePlayerManager;

class replaceHomeSubCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission("replace.home.command.permissions");
        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->registerArgument(0, new RawStringArgument("name"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($this->testPermissionSilent($sender) || $sender instanceof Player)){
            return;
        }
        HomePlayerManager::getInstance()->getPlayer($sender)->replaceHome($args["name"], $sender->getPosition());
    }
}