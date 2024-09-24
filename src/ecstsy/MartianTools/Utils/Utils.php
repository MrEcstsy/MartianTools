<?php

namespace ecstsy\MartianTools\Utils;

use ecstsy\MartianTools\Loader;
use pocketmine\entity\Entity;
use pocketmine\math\AxisAlignedBB;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;

class Utils {

   private static array $configCache = [];
   
   public static function getConfiguration(string $fileName): ?Config {
        $pluginFolder = Loader::getInstance()->getDataFolder();
        $filePath = $pluginFolder . $fileName;

        if (isset(self::$configCache[$filePath])) {
            return self::$configCache[$filePath];
        }

        if (!file_exists($filePath)) {
            Loader::getInstance()->getLogger()->warning("Configuration file '$filePath' not found.");
            return null;
        }
        
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'yml':
            case 'yaml':
                $config = new Config($filePath, Config::YAML);
                break;

            case 'json':
                $config = new Config($filePath, Config::JSON);
                break;

            default:
                Loader::getInstance()->getLogger()->warning("Unsupported configuration file format for '$filePath'.");
                return null;
        }

        self::$configCache[$filePath] = $config;
        return $config;
   }

    /**
     * Returns an online player whose name begins with or equals the given string (case insensitive).
     * The closest match will be returned, or null if there are no online matches.
     *
     * @param string $name The prefix or name to match.
     * @return Player|null The matched player or null if no match is found.
     */
    public static function getPlayerByPrefix(string $name): ?Player {
        $found = null;
        $name = strtolower($name);
        $delta = PHP_INT_MAX;

        /** @var Player[] $onlinePlayers */
        $onlinePlayers = Server::getInstance()->getOnlinePlayers();

        foreach ($onlinePlayers as $player) {
            if (stripos($player->getName(), $name) === 0) {
                $curDelta = strlen($player->getName()) - strlen($name);

                if ($curDelta < $delta) {
                    $found = $player;
                    $delta = $curDelta;
                }

                if ($curDelta === 0) {
                    break;
                }
            }
        }

        return $found;
    }

    /**
     * @param Entity $player
     * @param string $sound
     * @param int $volume
     * @param int $pitch
     * @param int $radius
     */
    public static function playSound(Entity $player, string $sound, $volume = 1, $pitch = 1, int $radius = 5): void
    {
        foreach ($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius)) as $p) {
            if ($p instanceof Player) {
                if ($p->isOnline()) {
                    $spk = new PlaySoundPacket();
                    $spk->soundName = $sound;
                    $spk->x = $p->getLocation()->getX();
                    $spk->y = $p->getLocation()->getY();
                    $spk->z = $p->getLocation()->getZ();
                    $spk->volume = $volume;
                    $spk->pitch = $pitch;
                    $p->getNetworkSession()->sendDataPacket($spk);
                }
            }
        }
    }

    /**
     * @param Entity $player
     * @param string $particleName
     * @param int $radius
     */
    public static function spawnParticle(Position $position, string $particleName, int $radius = 5): void {
        $blockPosition = $position->asVector3();
        $world = $position->getWorld();

        $packet = new SpawnParticleEffectPacket();
        $packet->position = $blockPosition;
        $packet->particleName = $particleName;

        $bb = new AxisAlignedBB(
            $blockPosition->getX() - $radius,
            $blockPosition->getY() - $radius,
            $blockPosition->getZ() - $radius,
            $blockPosition->getX() + $radius,
            $blockPosition->getY() + $radius,
            $blockPosition->getZ() + $radius
        );

        foreach ($world->getNearbyEntities($bb) as $entity) {
            if ($entity instanceof Player && $entity->isOnline()) {
                $entity->getNetworkSession()->sendDataPacket(clone $packet);
            }
        }
    }

    public static function translateTime(int $seconds): string
    {
        $timeUnits = [
            'w' => 60 * 60 * 24 * 7,
            'd' => 60 * 60 * 24,
            'h' => 60 * 60,
            'm' => 60,
            's' => 1,
        ];

        $parts = [];

        foreach ($timeUnits as $unit => $value) {
            if ($seconds >= $value) {
                $amount = floor($seconds / $value);
                $seconds %= $value;
                $parts[] = $amount . $unit;
            }
        }

        return implode(', ', $parts);
    }

    public static function secondsToTicks(int $seconds) : int {
        return $seconds * 20;
    }

    
}