<?php

namespace SenseiTarzan\HomesManager\Commands\subCommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use SenseiTarzan\HomesManager\Class\Exception\HomeNotFoundException;
use SenseiTarzan\HomesManager\Class\Exception\HomeSaveException;
use SenseiTarzan\HomesManager\Component\HomePlayerManager;
use SenseiTarzan\HomesManager\Main;
use SenseiTarzan\HomesManager\Utils\CustomKnownTranslationFactory;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;
use SOFe\AwaitGenerator\Await;

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
        if (!($this->testPermissionSilent($sender) || $sender instanceof Player)) {
            return;
        }
        $homeId = $args["name"];
        $homePlayer = HomePlayerManager::getInstance()->getPlayer($sender);

        Await::g2c($homePlayer->removeHome($homeId), function () use ($sender, $homePlayer, $homeId) {
            $sender->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::remove_home_player_sender($homeId)));
        },
            [
                HomeNotFoundException::class => function (HomeNotFoundException $exception) use ($sender): void {
                    $sender->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($sender, CustomKnownTranslationFactory::error_home_no_exist($exception->getMessage())));
                },
                HomeSaveException::class => function (HomeSaveException $exception) use ($homePlayer): void {
                    Main::getInstance()->getLogger()->alert("[{$homePlayer->getPlayerName()}] {$exception->getMessage()}");
                }
            ]);

    }
}