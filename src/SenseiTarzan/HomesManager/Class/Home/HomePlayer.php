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
use WeakReference;
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

    /**
     * @param WeakReference<Player>|string $player
     */
    public function __construct(private readonly WeakReference|string $player)
    {
        $this->dataHomes = new Config(Path::join(PLUGIN_DATA_PATH, "datas", strtolower($this->getPlayerName()) . ".json"));
        $this->loadHomes();
    }

    public function loadHomes(): void{
        foreach ($this->dataHomes->getAll() as $name => $information){
            $this->homes[strtolower($name)] = new Home($name,  $information['world'], new Vector3($information["x"],$information["y"], $information['z']));
        }
    }

    public function getPlayer(): ?Player
    {
        return is_string($this->player) ? null : $this->player->get();
    }

    /**
     * no work if $this->player is not Player
     * @internal
     */
    public function addHome(string $name, Position $position): void{
        $player = $this->getPlayer();
        if(!$player)
            return;
        if (count($this->homes) >= ($maxHome = HomeManager::getInstance()->getMaxHomeByPermissions($player))){
            $player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::error_home_max($maxHome)));
            return;
        }
        if (isset($this->homes[$id = strtolower($name)]))
            return;
        $this->homes[$id] = $info = Home::create($name, $position);
        $this->dataHomes->set($name, $info->jsonSerialize());
        $player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::add_home_player_sender($name, $position)));
    }

    public function replaceHome(string $name, Position $position): void
    {
        $player = $this->getPlayer();
        if (!isset($this->homes[$id = strtolower($name)])){
            $this->addHome($name, $position);
            return;
        }
        ($info = $this->homes[$id])->setPosition($position);
        $this->dataHomes->set($name, $info->jsonSerialize());
        if($player)
            $player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::replace_home_player_sender($name, $position)));
    }

    public function getHomes(): array{
        return $this->homes;
    }

    public function existsHome(string $name): bool
    {
        return isset($this->homes[strtolower($name)]);
    }

    public function getHome(string $name): false|Home{
        return $this->homes[strtolower($name)] ?? false;
    }

    public function removeHome(string $name): bool
    {
        if (!isset($this->homes[strtolower($name)])) return false;
        unset($this->homes[strtolower($name)]);
        $this->dataHomes->remove($name);
        return true;
    }

    public function save(): void
    {
        if ($this->dataHomes->hasChanged())
            $this->dataHomes->save();
    }

    /**
     * @return Player| string
     */
    public function getPlayerName(): ?string
    {
        return is_string($this->player) ? $this->player : $this->getPlayer()?->getName() ?? "error";
    }

}