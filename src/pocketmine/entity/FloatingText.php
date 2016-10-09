<?php

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\Player;
use pocketmine\utils\UUID;

class FloatingText extends Entity {
    const NETWORK_ID = 80;

    protected $title;
    protected $text;

    public function getName(): string {
        return "FloatingText";
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setText($text) {
        $this->text = $text;
    }

    public function spawnTo(Player $player) {
        $pk = new AddPlayerPacket();
        $pk->eid = $this->getId();
        $pk->uuid = UUID::fromRandom();
        $pk->type = self::NETWORK_ID;
        $pk->x = $this->x;
        $pk->y = $this->y;
        $pk->z = $this->z;
        $pk->speedX = 0;
        $pk->speedY = 0;
        $pk->speedZ = 0;
        $pk->yaw = 0;
        $pk->pitch = 0;
        $pk->item = ItemItem::get(ItemItem::AIR);
        //$pk->metadata = $this->dataProperties;
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 1 << Entity::DATA_FLAG_INVISIBLE],
            Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->title . ($this->text !== "" ? "\n" . $this->text : "")],
            Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
            Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1],
            Entity::DATA_LEAD_HOLDER => [Entity::DATA_TYPE_LONG, -1],
            Entity::DATA_LEAD => [Entity::DATA_TYPE_BYTE, 0]
        ];
        $player->dataPacket($pk);

        parent::spawnTo($player);
    }

    public function saveNBT() {}
}