<?php

namespace ecstsy\MartianTools\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\MartianTools\Loader;
use ecstsy\MartianTools\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class WarnCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission("martiantools.command.warn");

        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new TextArgument("reason", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $player = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : null;
        $reason = $args["reason"] ?? "No reason provided";
        $lang = Loader::getInstance()->getLanguageManager();
        $prefix = $lang->get("prefix");
    
        if ($player !== null) {
            $uuid = $player->getUniqueId()->toString();
    
            $warningsManager = Loader::getWarningsManager();
            $warningsManager->addWarning($uuid, $reason);
    
            $sender->sendMessage(C::colorize(str_replace(
                ["{player}", "{prefix}"],
                [$player->getName(), $prefix],
                $lang->getNested("warn.warned")
            )));
    
            if ($player instanceof Player) {
                $player->sendMessage(C::colorize(str_replace(
                    ["{reason}", "{prefix}"],
                    [$reason, $prefix],
                    $lang->getNested("warn.notify")
                )));
            }
    
        } else {
            $sender->sendMessage(C::colorize(str_replace(
                ["{prefix}", "{player}"],
                [$prefix, $args["name"]],
                $lang->get("not-found")
            )));
            return;
        }
    }
    

    public function getPermission(): string {
        return "martiantools.command.warn";
    }
}
