<?php

namespace ecstsy\MartianTools\Player\Warnings;

use ecstsy\MartianTools\Loader;
use ecstsy\MartianTools\Player\Warnings\PlayerWarnings;
use ecstsy\MartianTools\Utils\Queries;
use pocketmine\utils\SingletonTrait;

final class WarningManager
{
    use SingletonTrait;

    /** @var PlayerWarnings[] */
    private array $warnings = [];

    public function __construct(public Loader $plugin)
    {
        self::setInstance($this);

        $this->loadWarnings();
    }

    /**
     * Store all warnings in $warnings property
     */
    private function loadWarnings() : void
    {
        Loader::getDatabase()->executeSelect(Queries::WARNINGS_SELECT, [], function (array $rows) : void {
            foreach ($rows as $row) {
                $this->warnings[] = new PlayerWarnings(
                    (int)$row['id'],
                    $row['uuid'],
                    $row['reason'],
                    $row['timestamp']
                );
            }
        });
    }

    /**
     * Get warnings for a specific UUID
     */
    public function getWarnings(string $uuid): array
    {
        return array_filter($this->warnings, fn(PlayerWarnings $warning) => $warning->getUuid() === $uuid);
    }

    /**
     * Add a warning
     */
    public function addWarning(string $uuid, string $reason): void
    {
        // Store the warning in the database
        Loader::getDatabase()->executeInsert(Queries::WARNINGS_CREATE, [
            "uuid" => $uuid,
            "reason" => $reason,
            "timestamp" => (new \DateTime())->format('Y-m-d H:i:s')
        ]);

        // Also store it in memory
        $this->warnings[] = new PlayerWarnings(
            count($this->warnings) + 1,
            $uuid,
            $reason,
            (new \DateTime())->format('Y-m-d H:i:s')
        );
    }

    /**
     * Delete a warning
     */
    public function deleteWarning(PlayerWarnings $warning): void
    {
        Loader::getDatabase()->executeChange(Queries::WARNINGS_DELETE, [
            "id" => $warning->getId()
        ]);

        $uuid = $warning->getUuid();
        $playerWarnings = $this->getWarnings($uuid);

        if (($key = array_search($warning, $playerWarnings)) !== false) {
            unset($playerWarnings[$key]);
        }

        $this->warnings[$uuid] = array_values($playerWarnings); 
    }
}

