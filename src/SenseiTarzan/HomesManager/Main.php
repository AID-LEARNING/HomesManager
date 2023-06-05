<?php

namespace SenseiTarzan\HomesManager;

use CortexPE\Commando\PacketHooker;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\utils\SingletonTrait;
use SenseiTarzan\ExtraEvent\Component\EventLoader;
use SenseiTarzan\HomesManager\Commands\HomeCommand;
use SenseiTarzan\HomesManager\Component\HomeManager;
use SenseiTarzan\HomesManager\Component\HomePlayerManager;
use SenseiTarzan\HomesManager\Listener\PlayerListener;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;
use SenseiTarzan\Path\PathScanner;
use SOFe\AwaitGenerator\Await;
use Symfony\Component\Filesystem\Path;

class Main extends PluginBase
{

    use SingletonTrait;

    protected function onLoad(): void
    {
        self::setInstance($this);
        define("SenseiTarzan\\HomesManager\\PLUGIN_DATA_PATH", $this->getDataFolder());
        @mkdir(Path::join($this->getDataFolder(), "datas"));
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
        if (!PacketHooker::isRegistered()){
            PacketHooker::register($this);
        }
        EventLoader::loadEventWithClass($this, PlayerListener::class);
        $this->getServer()->getCommandMap()->register("senseitarzan", new HomeCommand($this,"home"));
        LanguageManager::getInstance()->loadCommands("home");
    }

    public static function sleeper(): \Generator{
        return Await::promise(function ($resolve, $reject) {
           $task = new class($resolve, $reject) extends Task{
                public function __construct(private $resolve, private $reject){}
               public function onRun(): void
               {
                     ($this->resolve)();
               }
               public function onCancel(): void
               {
                   ($this->reject)(new \Exception("Task cancelled", code: 950));
               }
           };
           Main::getInstance()->getScheduler()->scheduleDelayedTask($task, 20);
        });
    }

}