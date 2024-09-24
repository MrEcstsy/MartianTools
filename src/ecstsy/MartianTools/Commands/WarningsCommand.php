<?php

namespace ecstsy\MartianTools\Commands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use DateTime;
use DateTimeZone;
use ecstsy\MartianTools\Loader;
use ecstsy\MartianTools\Player\Warnings\PlayerWarnings;
use ecstsy\MartianTools\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;

class WarningsCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());
        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new IntegerArgument("page", true));  
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $player = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : null;
        $lang = Loader::getInstance()->getLanguageManager();
        $prefix = $lang->get("prefix");
        $page = isset($args["page"]) ? (int) $args["page"] : 1;  
        $config = Utils::getConfiguration("config.yml");

        if ($player !== null) {
            $uuid = $player->getUniqueId()->toString();
            $warningsManager = Loader::getWarningsManager();
            $warnings = $warningsManager->getWarnings($uuid);
            $timezone = $config->getNested("settings.timezone");
            $dateTimeZone = new DateTimeZone($timezone);

            if (!empty($warnings)) {
                $totalWarnings = count($warnings);
                $totalPages = (int) ceil($totalWarnings / $lang->getNested("warnings.per-page"));

                if ($page < 1 || $page > $totalPages) {
                    $sender->sendMessage(C::RED . "Invalid page number. Use a valid page between 1 and " . $totalPages);
                    return;
                }

                $headerMessage = str_replace(
                    ["{player}", "{current_page}", "{total_pages}"],
                    [$player->getName(), $page, $totalPages],
                    $lang->getNested("warnings.header")
                );
                $sender->sendMessage(C::colorize(str_replace("{prefix}", $prefix, $headerMessage)));

                $startIndex = ($page - 1) * $lang->getNested("warnings.per-page");
                $endIndex = min($startIndex + $lang->getNested("warnings.per-page"), $totalWarnings);
                
                for ($i = $startIndex; $i < $endIndex; $i++) {
                    /** @var PlayerWarnings $warning */
                    $warning = $warnings[$i];
                
                    $dateTime = new DateTime($warning->getTimestamp(), new DateTimeZone('UTC')); 
                    $dateTime->setTimezone($dateTimeZone); // Convert to user's timezone
                
                    $formattedTimestamp = $dateTime->format('n/j/y g:i A');
                
                    $formattedMessage = C::colorize(str_replace(
                        ["{id}", "{reason}", "{timestamp}", "{prefix}"],
                        [$warning->getId(), $warning->getReason(), $formattedTimestamp, $prefix],
                        $lang->getNested("warnings.entry")
                    ));
                    $sender->sendMessage($formattedMessage);
                }                

                if ($page < $totalPages) {
                    $paginationHint = str_replace(["{next_page}", "{player}"], [$page + 1, $player->getName()], $lang->getNested("warnings.pagination-hint"));
                    $sender->sendMessage(C::colorize($paginationHint));
                }

            } else {
                $noWarningsMessage = str_replace("{player}", $player->getName(), $lang->getNested("warnings.no-warnings"));
                $sender->sendMessage(C::colorize(str_replace("{prefix}", $prefix, $noWarningsMessage)));
            }
        } else {
            $notFoundMessage = str_replace("{player}", $args["name"], $lang->get("not-found"));
            $sender->sendMessage(C::colorize(str_replace("{prefix}", $prefix, $notFoundMessage)));
        }
    }

    public function getPermission(): string {
        return "martiantools.command.warnings";
    }
}
