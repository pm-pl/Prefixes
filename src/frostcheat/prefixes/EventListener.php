<?php

namespace frostcheat\prefixes;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class EventListener implements Listener
{

    public function handleJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player instanceof Player) {
            $session = Prefixes::getInstance()->getSessionManager()->getSession((string)$player->getUniqueId());
            if ($session !== null) {
                $session->setPlayer($player);
            }
        }
    }
    public function handleLogin(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        $session = Prefixes::getInstance()->getSessionManager()->getSession((string)$player->getUniqueId());

        if ($session === null) {
            Prefixes::getInstance()->getSessionManager()->addSession((string)$player->getUniqueId(), [
                "name" => $player->getName(),
                "prefix" => null,
            ]);
        } else {
            if ($player->getName() !== $session->getName()) {
                $session->setName($player->getName());
            }
        }
    }

    public function handleChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();

        if (Prefixes::getInstance()->isCharge()) {
            $player->sendMessage(TextFormat::colorize(str_replace("%plugin-prefix%", Prefixes::getInstance()->getProvider()->getMessages()->get("plugin-prefix"), Prefixes::getInstance()->getProvider()->getMessages()->get("plugin-reload"))));
            $event->cancel();
            return;
        }

        $session = Prefixes::getInstance()->getSessionManager()->getSession((string)$player->getUniqueId());
        if ($session !== null) {
            if ((bool) Prefixes::getInstance()->getConfig()->getNested("rank-system-chat", false) === false) {
                $event->setFormatter($session->getChatFormatter());
            }
            if ($session->getPrefix() !== null) {
                $prefix = Prefixes::getInstance()->getPrefixManager()->getPrefix($session->getPrefix());
                if ($message === "%prefix%") {
                    $player->sendMessage(TextFormat::colorize(str_replace(["%prefix-format%", "%plugin-prefix%"], [$prefix->getFormat(), Prefixes::getInstance()->getProvider()->getMessages()->get("plugin-prefix")], Prefixes::getInstance()->getProvider()->getMessages()->get("player-prefix"))));
                    $event->cancel();
                }
            } else {
                if ($message === "%prefix%") {
                    $player->sendMessage(TextFormat::colorize(str_replace("%plugin-prefix%", Prefixes::getInstance()->getProvider()->getMessages()->get("plugin-prefix"), Prefixes::getInstance()->getProvider()->getMessages()->get("player-no-prefix"))));
                    $event->cancel();
                }
            }
        }
    }
}