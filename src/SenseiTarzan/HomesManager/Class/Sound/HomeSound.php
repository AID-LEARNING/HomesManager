<?php

namespace SenseiTarzan\HomesManager\Class\Sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\world\sound\Sound;

class HomeSound implements Sound
{

    public function __construct(private string $song, private float $volume = 1.0, private float $pitch = 1.0)
    {
    }

    public function encode(Vector3 $pos): array
    {
        return [PlaySoundPacket::create($this->song,$pos->getX(),$pos->getY(),$pos->getZ(),$this->volume,$this->pitch)];
    }
}