<?php

namespace SenseiTarzan\HomesManager\Class\Home;

use JsonSerializable;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\Position;

class Home implements JsonSerializable
{
    public function __construct(private string $name, private string $worldName,private Vector3 $vector3)
    {
    }

    public static function create(string $name, Position $position): Home
    {
        return new self($name,$position->getWorld()->getFolderName(), $position->asVector3());
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getWorldName(): string
    {
        return $this->worldName;
    }

    /**
     * @param string $worldName
     */
    public function setWorldName(string $worldName): void
    {
        $this->worldName = $worldName;
    }

    /**
     * @return Vector3
     */
    public function getVector3(): Vector3
    {
        return $this->vector3;
    }

    /**
     * @param Vector3 $vector3
     */
    public function setVector3(Vector3 $vector3): void
    {
        $this->vector3 = $vector3;
    }

    public function getPosition(): false|Position{

        $world = Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName());
        if ($world === null) return false;
        return Position::fromObject($this->getVector3(), $world);
    }

    public function setPosition(Position $position): void{
        $this->setVector3($position->asVector3());
        $this->setWorldName($position->getWorld()->getFolderName());
    }



    public function jsonSerialize(): array
    {
        return ['x' =>  $this->getVector3()->getFloorY(), 'y' => $this->getVector3()->getFloorY(), 'z' => $this->getVector3()->getFloorZ(), 'world' => $this->getWorldName()];
    }
}