<?php

namespace Laith98Dev\HeadshotSound;

/*  
 *  A plugin for PocketMine-MP.
 *  
 *	 _           _ _   _    ___   ___  _____             
 *	| |         (_) | | |  / _ \ / _ \|  __ \            
 *	| |     __ _ _| |_| |_| (_) | (_) | |  | | _____   __
 *	| |    / _` | | __| '_ \__, |> _ <| |  | |/ _ \ \ / /
 *	| |___| (_| | | |_| | | |/ /| (_) | |__| |  __/\ V / 
 *	|______\__,_|_|\__|_| |_/_/  \___/|_____/ \___| \_/  
 *	
 *	Copyright (C) 2021 Laith98Dev
 *  
 *	Youtube: Laith Youtuber
 *	Discord: Laith98Dev#0695
 *	Gihhub: Laith98Dev
 *	Email: help@laithdev.tk
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 	
 */

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Egg;
use pocketmine\entity\projectile\Snowball;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener{

    public Config $cfg;

    public function onEnable(): void{
        @mkdir($this->getDataFolder());

        $this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML, [
            "sound.name" => "note.pling",
            "message" => TextFormat::YELLOW . "Nice Headshot"
        ]);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDamage(EntityDamageEvent $event){
        $player = $event->getEntity();
        if($event->isCancelled())
            return;
        if($event instanceof EntityDamageByChildEntityEvent){
            $child = $event->getChild();
            $damager = $child->getOwningEntity();
            if($damager instanceof Player && ($child instanceof Arrow || $child instanceof Snowball || $child instanceof Egg)){
                if($child->getPosition()->getY() >= ($player->getPosition()->getY() + $player->getEyeHeight())){
                    if($this->cfg->exists("sound.name") && $this->cfg->exists("message")){
                        if(($msg = $this->cfg->get("message")) && $msg !== ""){
                            $damager->sendMessage($msg);
                        }
                        
                        if(($soundName = $this->cfg->get("sound.name")) && $msg !== ""){
                            $this->playSound($damager, $soundName);
                        }
                    }
                }
            }
        }
    }

    private function playSound(Player $player, string $sound, int $volume = 150, int $pitch = 1) {
        // Stop currently sound
        $packet = new StopSoundPacket();
		$packet->soundName = $sound;
		$packet->stopAll = true;
		$player->getNetworkSession()->sendDataPacket($packet);

		$packet = new PlaySoundPacket();
		$packet->soundName = $sound;   
		$packet->x = $player->getPosition()->getX();
		$packet->y = $player->getPosition()->getY();
		$packet->z = $player->getPosition()->getZ();
		$packet->volume = $volume;
		$packet->pitch = $pitch;
		$player->getNetworkSession()->sendDataPacket($packet);
	} 
}
