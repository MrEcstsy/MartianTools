<?php

declare(strict_types=1);

namespace ecstsy\MartianTools\Player;

use ecstsy\MartianTools\Loader;
use ecstsy\MartianTools\Utils\Queries;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Ramsey\Uuid\UuidInterface;

final class MartianToolsPlayer
{


    private bool $isConnected = false;

    public function __construct(
        private UuidInterface $uuid,
        private string        $username
    )
    {
    }

    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    public function setConnected(bool $connected): void
    {
        $this->isConnected = $connected;
    }

    /**
     * Get UUID of the player
     *
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * This function gets the PocketMine player
     *
     * @return Player|null
     */
    public function getPocketminePlayer(): ?Player
    {
        return Server::getInstance()->getPlayerByUUID($this->uuid);
    }

    /**
     * Get username of the session
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set username of the session
     *
     * @param string $username
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
        $this->updateDb(); // Make sure to call updateDb function when you're making changes to the player data
    }

    /**
     * Update player information in the database
     *
     * @return void
     */
    private function updateDb(): void
    {

        Loader::getDatabase()->executeChange(Queries::PLAYERS_UPDATE, [
            "uuid" => $this->uuid->toString(),
            "username" => $this->username
        ]);
    }

}