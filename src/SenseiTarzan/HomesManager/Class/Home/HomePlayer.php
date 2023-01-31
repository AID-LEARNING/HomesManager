<?php

namespace SenseiTarzan\HomesManager\Class\Home;

use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use SenseiTarzan\HomesManager\Component\HomeManager;
use SenseiTarzan\HomesManager\Utils\CustomKnownTranslationFactory;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;
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

    public function __construct(private Player|string $player)
    {
        $this->dataHomes = new Config(Path::join(PLUGIN_DATA_PATH, "datas", strtolower($this->player->getName()) . ".json"));
        $this->loadHomes();
    }

    public function loadHomes(): void{
        foreach ($this->dataHomes->getAll() as $name => $information){
            $this->homes[strtolower($name)] = new Home($name,  $information['world'], new Vector3($information["x"],$information["y"], $information['z']));
        }
    }

    /**
     * no work if $this->player is not Player
     * @internal
     */
    public function addHome(string $name, Position $position): void{
        if (!($this->player instanceof Player)) return;
        if (count($this->homes) >= ($maxHome = HomeManager::getInstance()->getMaxHomeByPermissions($this->player))){
            $this->player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($this->player, CustomKnownTranslationFactory::error_home_max($maxHome)));
            return;
        }
        if (isset($this->homes[$id = strtolower($name)])){
            return;
        }
        $this->homes[$id] = $info = Home::create($name, $position);
        $this->dataHomes->set($name, $info->jsonSerialize());
        $this->dataHomes->save();
        $this->player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($this->player, CustomKnownTranslationFactory::add_home_player_sender($name, $position)));
    }

    public function replaceHome(string $name, Position $position): void
    {
        if (!isset($this->homes[$id = strtolower($name)])){
            $this->addHome($name, $position);
            return;
        }
        ($info = $this->homes[$id])->setPosition($position);
        $this->dataHomes->set($name, $info->jsonSerialize());
        $this->dataHomes->save();
        $this->player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($this->player, CustomKnownTranslationFactory::replace_home_player_sender($name, $position)));
    }

    public function getHomes(): array{
        return $this->homes;
    }

    public function getHome(string $name): false|Home{
        return $this->homes[strtolower($name)] ?? false;
    }

    public function removeHome(string $name): bool
    {
        if (!isset($this->homes[strtolower($name)])) return false;
        unset($this->homes[strtolower($name)]);
        $this->dataHomes->remove($name);
        $this->dataHomes->save();
        return true;
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