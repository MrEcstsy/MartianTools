<?php

namespace ecstsy\MartianTools\Commands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\MartianTools\Loader;
use ecstsy\MartianTools\Player\Warnings\PlayerWarnings;
use ecstsy\MartianTools\Utils\Utils;
use pocketmine\command\CommandSender;

class RemoveWarningCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new IntegerArgument("id", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $player = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : null;
        $lang = Loader::getLanguageManager();
        $prefix = $lang->get("prefix");

        if ($player !== null) {
            $uuid = $player->getUniqueId()->toString();
            $warningsManager = Loader::getWarningsManager();
            $warnings = $warningsManager->getWarnings($uuid);
            $warningId = $args["id"];

            if (!empty($warnings)) {
                $warningToRemove = null;

                foreach ($warnings as $key => $warning) {
                    if ($warning->getId() === $warningId) {
                        $warningToRemove = $warning;
                        break;
                    }
                }

                if ($warningToRemove !== null) {
                    $warningsManager->deleteWarning($warningToRemove);

                    $successMessage = str_replace(
                        ["{player}", "{id}"],
                        [$player->getName(), $warningId],
                        $lang->getNested("warnings.removed")
                    );
                    $sender->sendMessage(str_replace("{prefix}", $prefix, $successMessage));
                } else {
                    $warningNotFoundMessage = str_replace(
                        ["{player}", "{id}"],
                        [$player->getName(), $warningId],
                        $lang->getNested("warnings.not-found")
                    );
                    $sender->sendMessage(str_replace("{prefix}", $prefix, $warningNotFoundMessage));
                }
            } else {
                $noWarningsMessage = str_replace("{player}", $player->getName(), $lang->getNested("warnings.no-warnings"));
                $sender->sendMessage(str_replace("{prefix}", $prefix, $noWarningsMessage));
            }
        } else {
            $playerNotFoundMessage = str_replace("{player}", $args["name"], $lang->get("not-found"));
            $sender->sendMessage(str_replace("{prefix}", $prefix, $playerNotFoundMessage));
        }
    }

    public function getPermission(): string {
        return "martiantools.command.warn.remove";
    }
}
