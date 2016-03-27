<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://mcper.cn
 *
 */

/*
* Copied from @beito123's FlowerPot plugin
 */

namespace pocketmine\utils;

class Color {

	const COLOR_DYE_BLACK = 0;//dye colors
	const COLOR_DYE_RED = 1;
	const COLOR_DYE_GREEN = 2;
	const COLOR_DYE_BROWN = 3;
	const COLOR_DYE_BLUE = 4;
	const COLOR_DYE_PURPLE = 5;
	const COLOR_DYE_CYAN = 6;
	const COLOR_DYE_LIGHT_GRAY = 7;
	const COLOR_DYE_GRAY = 8;
	const COLOR_DYE_PINK = 9;
	const COLOR_DYE_LIME = 10;
	const COLOR_DYE_YELLOW = 11;
	const COLOR_DYE_LIGHT_BLUE = 12;
	const COLOR_DYE_MAGENTA = 13;
	const COLOR_DYE_ORANGE = 14;
	const COLOR_DYE_WHITE = 15;

	private $red = 0;
	private $green = 0;
	private $blue = 0;

	/** @var \SplFixedArray */
	public static $dyeColors = null;

	public static function init(){
		if(self::$dyeColors === null){
			self::$dyeColors = new \SplFixedArray(256);//todo rewrite to dec and check color
			self::$dyeColors[self::COLOR_DYE_BLACK] = Color::getRGB(0x1e, 0x1b, 0x1b);
			self::$dyeColors[self::COLOR_DYE_RED] = Color::getRGB(0xb3, 0x31, 0x2c);
			self::$dyeColors[self::COLOR_DYE_BLUE] = Color::getRGB(0x25, 0x31, 0x92);
			self::$dyeColors[self::COLOR_DYE_BROWN] = Color::getRGB(0x51, 0x30, 0x1a);
			self::$dyeColors[self::COLOR_DYE_BLUE] = Color::getRGB(0x25, 0x31, 0x92);
			self::$dyeColors[self::COLOR_DYE_PURPLE] = Color::getRGB(0x7b, 0x2f, 0xbe);
			self::$dyeColors[self::COLOR_DYE_CYAN] = Color::getRGB(0x28, 0x76, 0x97);
			self::$dyeColors[self::COLOR_DYE_LIGHT_GRAY] = Color::getRGB(0x99, 0x99, 0x99);
			self::$dyeColors[self::COLOR_DYE_GRAY] = Color::getRGB(0x43, 0x43, 0x43);
			self::$dyeColors[self::COLOR_DYE_PINK] = Color::getRGB(0xd8, 0x81, 0x98);
			self::$dyeColors[self::COLOR_DYE_LIME] = Color::getRGB(0x41, 0xcd, 0x34);
			self::$dyeColors[self::COLOR_DYE_YELLOW] = Color::getRGB(0xde, 0xcf, 0x2a);
			self::$dyeColors[self::COLOR_DYE_LIGHT_BLUE] = Color::getRGB(0x66, 0x89, 0xd3);
			self::$dyeColors[self::COLOR_DYE_MAGENTA] = Color::getRGB(0xc3, 0x54, 0xcd);
			self::$dyeColors[self::COLOR_DYE_ORANGE] = Color::getRGB(0xeb, 0x88, 0x44);
			self::$dyeColors[self::COLOR_DYE_WHITE] = Color::getRGB(0xf0, 0xf0, 0xf0);
		}
	}

	public static function getRGB($r, $g, $b){
		return new Color((int) $r, (int) $g, (int) $b);
	}

	public static function averageColor(Color ...$colors){
		$tr = 0;//total red
		$tg = 0;//green
		$tb = 0;//blue
		$count = 0;
		foreach($colors as $c){
			$tr += $c->getRed();
			$tg += $c->getGreen();
			$tb += $c->getBlue();
			++$count;
		}
		return Color::getRGB($tr / $count, $tg / $count, $tb / $count);
	}

	public static function getDyeColor($id){
		if(isset(self::$dyeColors[$id])){
			return clone self::$dyeColors[$id];
		}
		return Color::getRGB(0, 0, 0);
	}

	public function __construct($r, $g, $b){
		$this->red = $r;
		$this->green = $g;
		$this->blue = $b;
	}

	public function getRed(){
		return (int) $this->red;
	}

	public function getBlue(){
		return (int) $this->blue;
	}

	public function getGreen(){
		return (int) $this->green;
	}

	public function getColorCode(){
		return ($this->red << 16 | $this->green << 8 | $this->blue) & 0xffffff;
	}

	public function __toString(){
		return "Color(red:" . $this->red . ", green:" . $this->green . ", blue:" . $this->blue . ")";
	}
}