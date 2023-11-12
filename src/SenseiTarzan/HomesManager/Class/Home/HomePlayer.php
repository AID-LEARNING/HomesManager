<?php

namespace SenseiTarzan\HomesManager\Class\Home;

use Exception;
use Generator;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use SenseiTarzan\HomesManager\Class\Exception\HomeNotFoundException;
use SenseiTarzan\HomesManager\Class\Exception\HomePositionInvalidException;
use SenseiTarzan\HomesManager\Class\Exception\HomeSaveException;
use SenseiTarzan\HomesManager\Class\Exception\MaxHomeException;
use SenseiTarzan\HomesManager\Component\HomeManager;
use SenseiTarzan\HomesManager\Main;
use SenseiTarzan\HomesManager\Utils\CustomKnownTranslationFactory;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;
use SOFe\AwaitGenerator\Await;
use Symfony\Component\Filesystem\Path;
use const SenseiTarzan\HomesManager\PLUGIN_DATA_PATH;

class HomePlayer
{
    /**
     * @var Config
     */
    private Config $dataHomes;
    /**
     * @var Home[]
     */
    private array $homes = [];

    private bool $isMove = false;

    private bool $isGoHome = false;

    public function __construct(private Player|string $player)
    {
        $this->dataHomes = new Config(Path::join(PLUGIN_DATA_PATH, "datas", strtolower($this->getPlayerName()) . ".json"));
        $this->loadHomes();
    }

    public function loadHomes(): void{
        foreach ($this->dataHomes->getAll() as $name => $information){
            $this->homes[strtolower($name)] = new Home($name,  $information['world'], new Vector3($information["x"],$information["y"], $information['z']));
        }
    }

    /**
     * @return bool
     */
    public function isMove(): bool
    {
        return $this->isMove;
    }

    /**
     * @param bool $isMove
     */
    public function setIsMove(bool $isMove = true): void
    {
        $this->isMove = $isMove;
    }

    /**
     * @return bool
     */
    public function isGoHome(): bool
    {
        return $this->isGoHome;
    }

    /**
     * @param bool $isGoHome
     */
    public function setIsGoHome(bool $isGoHome = true): void
    {
        $this->isGoHome = $isGoHome;
    }

    /**
     * no work if $this->player is not Player
     * @internal
     */
    public function addHome(string $name, Position $position): void
    {
        if (!($this->player instanceof Player)) return;
        Await::f2c(function () use ($name, $position) {
            yield from $this->hasMaxHome($this->player);
            return yield from $this->addHomePromise(Home::create($name, $position));
        }, function (Home $home) {
            $this->player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($this->player, CustomKnownTranslationFactory::add_home_player_sender($home->getName(), $home->getPosition())));
        }, [
            MaxHomeException::class => function (MaxHomeException $exception) {
                $this->player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($this->player, CustomKnownTranslationFactory::error_home_max($exception->getMessage())));
            },
            HomeSaveException::class => function (HomeSaveException $exception) {
                Main::getInstance()->getLogger()->error("[{$this->player->getName()}] " . $exception->getMessage());
            }
        ]);
    }

    private function hasMaxHome(Player $player): Generator
    {
        return Await::promise(function ($resolve, $reject) use ($player) {
            Await::g2c($this->getHomes(), function (array $homes) use ($player, $resolve, $reject) {
                if (count($homes) >= ($maxHome = HomeManager::getInstance()->getMaxHomeByPermissions($player))) {
                    $reject(new MaxHomeException(strval($maxHome)));
                    return;
                }
                $resolve();
            }, $reject);
        });
    }

    public function getHomes(): Generator
    {
        return Await::promise(function ($resolve){
            $resolve($this->homes);
        });
    }
    private function addHomePromise(Home $home): Generator{
        return Await::promise(function ($resolve,$reject) use ($home){

            try {

                $this->homes[$home->getId()] = $home;
                $this->dataHomes->set($home->getName(), $home->jsonSerialize());
                $this->dataHomes->save();
                $resolve($home);
            }catch (Exception){
                $reject(new HomeSaveException("Error save home " . $home->getName()));
                return;
            }
        });
    }

    private function replaceHomePromise(string $name, Position $position) :Generator{
        return Await::promise(function ($resolve,$reject) use ($name, $position){
            Await::g2c($this->getHome($name), function (Home $home) use ($resolve, $reject, $position){
                try {
                    $home->setPosition($position);
                    $this->dataHomes->set($home->getName(), $home->jsonSerialize());
                    $this->dataHomes->save();
                    $resolve($home);
                }catch (Exception){
                    $reject(new HomeSaveException("Error save home " . $home->getName()));
                    return;
                }
            }, $reject);

        });
    }


    public function replaceHome(string $name, Position $position): void
    {
        if (!isset($this->homes[strtolower($name)])) {
            $this->addHome($name, $position);
            return;
        }
        Await::g2c($this->replaceHomePromise($name, $position), function (Home $home) {
            $this->player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($this->player, CustomKnownTranslationFactory::replace_home_player_sender($home->getName(), $home->getPosition())));

        }, [
            HomeNotFoundException::class => function (HomeNotFoundException $exception) {
                $this->player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($this->player, CustomKnownTranslationFactory::error_home_no_exist($exception->getMessage())));
            },
            HomeSaveException::class => function (HomeSaveException $exception) {
                Main::getInstance()->getLogger()->error("[{$this->player->getName()}] " . $exception->getMessage());
            }
        ]);
    }
    public function getHome(string $name): Generator{
        return Await::promise(function ($resolve, $reject) use ($name){
            if (!isset($this->homes[$id = strtolower($name)])){
                $reject(new HomeNotFoundException($name));
                return;
            }
            $home = $this->homes[$id];
            if (!$home->getPosition()) {
                $reject(new HomePositionInvalidException($name));
                return;
            }
            $resolve($home);
        });
    }

    public function removeHome(string $name): Generator
    {


        return Await::promise(function ($resolve, $reject) use ($name) {
            if (!isset($this->homes[$id = strtolower($name)])) {
                $reject(new HomeNotFoundException($name));
                return;
            }
            try {

                $this->dataHomes->remove($name);
                $this->dataHomes->save();
                unset($this->homes[$id]);
                $resolve();

            } catch (Exception) {
                $reject(new HomeSaveException("Error remove home " . $name));
            }
        });
    }

    /**
     * @return Player| string
     */
    public function getPlayer(): Player|string
    {
        return $this->player;
    }

    /**
     * @return Player| string
     */
    public function getPlayerName(): Player|string
    {
        return $this->player instanceof  Player  ? $this->player->getName() : $this->player;
    }

}