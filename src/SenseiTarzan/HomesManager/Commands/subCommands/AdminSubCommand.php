<?php

namespace SenseiTarzan\HomesManager\Commands\subCommands;

use CortexPE\Commando\args\TargetPlayerArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use SenseiTarzan\HomesManager\Component\HomeManager;
use SenseiTarzan\HomesManager\Component\HomePlayerManager;

class AdminSubCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission("admin.home.command.permissions");
        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->registerArgument(0,new TargetPlayerArgument(name: "target"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($this->testPermissionSilent($sender) || $sender instanceof Player)){
            return;
        }

        $target = Server::getInstance()->getPlayerExact($args['target']) ?? $args['target'];
        HomeManager::getInstance()->adminIndexUI($sender, HomePlayerManager::getInstance()->getPlayer($target));
    }
}