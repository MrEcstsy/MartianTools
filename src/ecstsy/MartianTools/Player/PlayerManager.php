<?php

declare(strict_types=1);

namespace ecstsy\MartianTools\Player;

use ecstsy\MartianTools\Loader;
use ecstsy\MartianTools\Utils\Queries;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PlayerManager
{
    use SingletonTrait;

    /** @var MartianToolsPlayer[] */
    private array $sessions; // array to fetch player data

    public function __construct(
        public Loader $plugin
    ){
        self::setInstance($this);

        $this->loadSessions();
    }

    /**
     * Store all player data in $sessions property
     *
     * @return void
     */
    private function loadSessions(): void
    {
        Loader::getDatabase()->executeSelect(Queries::PLAYERS_SELECT, [], function (array $rows): void {
            foreach ($rows as $row) {
                $this->sessions[$row["uuid"]] = new MartianToolsPlayer(
                    Uuid::fromString($row["uuid"]),
                    $row["username"]
                );
            }
        });
    }

    /**
     * Create a session
     *
     * @param Player $player
     * @return MartianToolsPlayer
     * @throws \JsonException
     */
    public function createSession(Player $player): MartianToolsPlayer
    {
        $args = [
            "uuid" => $player->getUniqueId()->toString(),
            "username" => $player->getName()
        ];

        Loader::getDatabase()->executeInsert(Queries::PLAYERS_CREATE, $args);

        $this->sessions[$player->getUniqueId()->toString()] = new MartianToolsPlayer(
            $player->getUniqueId(),
            $args["username"]
        );
        return $this->sessions[$player->getUniqueId()->toString()];
    }

    /**
     * Get session by player object
     *
     * @param Player $player
     * @return MartianToolsPlayer|null
     */
    public function getSession(Player $player) : ?MartianToolsPlayer
    {
        return $this->getSessionByUuid($player->getUniqueId());
    }

    /**
     * Get session by player name
     *
     * @param string $name
     * @return MartianToolsPlayer|null
     */
    public function getSessionByName(string $name) : ?MartianToolsPlayer
    {
        foreach ($this->sessions as $session) {
            if (strtolower($session->getUsername()) === strtolower($name)) {
                return $session;
            }
        }
        return null;
    }

    /**
     * Get session by UuidInterface
     *
     * @param UuidInterface $uuid
     * @return MartianToolsPlayer|null
     */
    public function getSessionByUuid(UuidInterface $uuid) : ?MartianToolsPlayer
    {
        return $this->sessions[$uuid->toString()] ?? null;
    }

    public function destroySession(MartianToolsPlayer $session) : void
    {
        Loader::getDatabase()->executeChange(Queries::PLAYERS_DELETE, ["uuid", $session->getUuid()->toString()]);

        # Remove session from the array
        unset($this->sessions[$session->getUuid()->toString()]);
    }

    public function getSessions() : array
    {
        return $this->sessions;
    }

}