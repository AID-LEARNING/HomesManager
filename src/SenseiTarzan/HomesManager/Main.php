<?php

namespace SenseiTarzan\HomesManager;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use SenseiTarzan\ExtraEvent\Component\EventLoader;
use SenseiTarzan\HomesManager\Commands\HomeCommand;
use SenseiTarzan\HomesManager\Component\HomeManager;
use SenseiTarzan\HomesManager\Component\HomePlayerManager;
use SenseiTarzan\HomesManager\Listener\PlayerListener;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;
use SenseiTarzan\Path\PathScanner;
use Symfony\Component\Filesystem\Path;

class Main extends PluginBase
{

    use SingletonTrait;

    protected function onLoad(): void
    {
        self::setInstance($this);
        define("SenseiTarzan\\HomesManager\\PLUGIN_DATA_PATH", $this->getDataFolder());
        if (!file_exists(Path::join($this->getDataFolder(), "config.yml"))) {
            foreach (PathScanner::scanDirectoryGenerator($search = Path::join(dirname(__DIR__,3) , "resources")) as $file){
                @$this->saveResource(str_replace($search, "", $file));
            }
        }
        new HomeManager($this);
        new HomePlayerManager();
        new LanguageManager($this);
    }

    protected function onEnable(): void
    {
        EventLoader::loadEventWithClass($this, PlayerListener::class);
        $this->getServer()->getCommandMap()->register("senseitarzan", new HomeCommand($this,"home"));
        LanguageManager::getInstance()->loadCommands("home");
    }

}