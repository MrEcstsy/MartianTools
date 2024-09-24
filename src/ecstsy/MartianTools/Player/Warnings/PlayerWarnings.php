<?php

declare(strict_types=1);

namespace ecstsy\MartianTools\Player\Warnings;

use ecstsy\MartianTools\Loader;
use ecstsy\MartianTools\Utils\Queries;

final class PlayerWarnings {
    private int $id;
    private string $uuid;
    private string $reason;
    private string $timestamp;

    public function __construct(
        int $id,
        string $uuid,
        string $reason,
        string $timestamp
    ) {
        $this->id = $id;
        $this->uuid = $uuid;
        $this->reason = $reason;
        $this->timestamp = $timestamp;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): void
    {
        $this->reason = $reason;
        $this->updateDb();
    }

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    public function setTimestamp(string $timestamp): void
    {
        $this->timestamp = $timestamp;
        $this->updateDb();
    }

    private function updateDb(): void
    {
        try {
            Loader::getDatabase()->executeChange(Queries::WARNINGS_UPDATE, [
                "id" => $this->id,
                "uuid" => $this->uuid,
                "reason" => $this->reason,
                "timestamp" => $this->timestamp
            ]);
        } catch (\Exception $e) {
            error_log("Failed to update warning ID {$this->id}: " . $e->getMessage());
        }
    }

    public function delete(): void
    {
        $this->removeWarning();
    }

    private function removeWarning(): void
    {
        try {
            Loader::getDatabase()->executeChange(Queries::WARNINGS_DELETE, ["id" => $this->id]);
        } catch (\Exception $e) {
            error_log("Failed to delete warning ID {$this->id}: " . $e->getMessage());
        }
    }
}
 