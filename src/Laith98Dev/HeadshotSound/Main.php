<?php
declare(strict_types = 1);

namespace Laith98Dev\HeadshotSound;

/*  
 *  A plugin for PocketMine-MP.
 *  
 *     _           _ _   _    ___   ___  _____             
 *    | |         (_) | | |  / _ \ / _ \|  __ \            
 *    | |     __ _ _| |_| |_| (_) | (_) | |  | | _____   __
 *    | |    / _` | | __| '_ \__, |> _ <| |  | |/ _ \ \ / /
 *    | |___| (_| | | |_| | | |/ /| (_) | |__| |  __/\ V / 
 *    |______\__,_|_|\__|_| |_/_/  \___/|_____/ \___| \_/  
 *    
 *    Copyright (C) 2022 Laith98Dev
 *  
 *    Youtube: Laith Youtuber
 *    Discord: Laith98Dev#0695
 *    Gihhub: Laith98Dev
 *    Email: help@laithdev.tk
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *     
 */

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Egg;
use pocketmine\entity\projectile\Snowball;
use pocketmine\event\EventPriority;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

final class Main extends PluginBase{

    private Config $cfg;

    protected function onEnable(): void{
        if(!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
        }
        $this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML, [
            "sound" => [
                "name" => "note.pling",
                "volume" => 150,
                "pitch" => 1
            ],
            "message" => TextFormat::YELLOW . "Nice Headshot"
        ]);

        $this->getServer()->getPluginManager()->registerEvent(EntityDamageByChildEntityEvent::class, function(EntityDamageByChildEntityEvent $event) : void{
            $child = $event->getChild();
            $damager = $child->getOwningEntity();
            if($damager instanceof Player && ($child instanceof Arrow || $child instanceof Snowball || $child instanceof Egg)){
                    $msg = $this->cfg->get("message");
                if($child->getPosition()->getY() >= ($damager->getPosition()->getY() + $damager->getEyeHeight())){
                    if($msg !== false && $msg !== ""){
                        $damager->sendMessage($msg);
                    }
                    $sound = $this->cfg->get("sound");
                    if($sound !== false && is_array($sound) && isset($sound["name"])){
                        $this->playSound($damager, $sound["name"], $sound["volume"] ?? 150, $sound["pitch"] ?? 1);
                    }
                }
            }
        }, EventPriority::NORMAL, $this);
    }

    private function playSound(Player $player, string $sound, int|floar $volume = 150, int|float $pitch = 1){
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
