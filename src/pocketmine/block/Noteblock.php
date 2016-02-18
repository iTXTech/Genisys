<?php
/**
 * Author: PeratX
 * Time: 2015/12/25 16:46
 ]

 *
 * OpenGenisys Project
 */
namespace pocketmine\block;

use pocketmine\item\Tool;
use pocketmine\item\Item;
use pocketmine\level\sound\NoteblockSound;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Noteblock extends Solid implements ElectricalAppliance{
	protected $id = self::NOTEBLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness() {
		return 0.8;
	}

	public function getResistance(){
		return 4;
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	public function canBeActivated() : bool {
		return true;
	}

	public function getStrength(){
		if($this->meta < 24) $this->meta ++;
		else $this->meta = 0;
		$this->getLevel()->setBlock($this, $this);
		return $this->meta * 1;
	}

	public function getInstrument(){
		$below = $this->getSide(Vector3::SIDE_DOWN);
		switch($below->getId()){
			case self::WOODEN_PLANK:
			case self::NOTEBLOCK:
			case self::CRAFTING_TABLE:
				return NoteblockSound::INSTRUMENT_BASS;
			case self::SAND:
			case self::SANDSTONE:
			case self::SOUL_SAND:
				return NoteblockSound::INSTRUMENT_TABOUR;
			case self::GLASS:
			case self::GLASS_PANEL:
			case self::GLOWSTONE_BLOCK:
				return NoteblockSound::INSTRUMENT_CLICK;
			case self::COAL_ORE:
			case self::DIAMOND_ORE:
			case self::EMERALD_ORE:
			case self::GLOWING_REDSTONE_ORE:
			case self::GOLD_ORE:
			case self::IRON_ORE:
			case self::LAPIS_ORE:
			case self::LIT_REDSTONE_ORE:
			case self::NETHER_QUARTZ_ORE:
			case self::REDSTONE_ORE:
				return NoteblockSound::INSTRUMENT_BASS_DRUM;
			default:
				return NoteblockSound::INSTRUMENT_PIANO;
		}
	}

	public function onActivate(Item $item, Player $player = null){
		$up = $this->getSide(Vector3::SIDE_UP);
		if($up->getId() == 0){
			$this->getLevel()->addSound(new NoteblockSound($this, $this->getInstrument(), $this->getStrength()));
			return true;
		}else{
			return false;
		}
	}

	public function getName() : string{
		return "Noteblock";
	}
}