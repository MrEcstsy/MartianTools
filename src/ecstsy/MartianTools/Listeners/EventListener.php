<?php

namespace ecstsy\MartianTools\Listeners;

use ecstsy\MartianTools\Player\PlayerManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

class EventListener implements Listener {

    public function onPlayerLogin(PlayerLoginEvent $event): void {
        $player = $event->getPlayer();

        if (PlayerManager::getInstance()->getSession($player) === null) {
            PlayerManager::getInstance()->createSession($player);
        }
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        
        PlayerManager::getInstance()->getSession($player)->setConnected(true);
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        
        PlayerManager::getInstance()->getSession($player)->setConnected(false);
    }
}