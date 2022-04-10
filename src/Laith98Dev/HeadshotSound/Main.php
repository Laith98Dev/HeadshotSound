<?php

namespace Laith98Dev\HeadshotSound;

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
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener{

    public function onEnable(): void{
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
                    $damager->sendMessage(TextFormat::YELLOW . "Nice Headshot");
                    $this->playSound($damager, "note.hat");
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