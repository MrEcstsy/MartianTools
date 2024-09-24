<?php

namespace ecstsy\MartianTools;

use ecstsy\MartianTools\Commands\RemoveWarningCommand;
use ecstsy\MartianTools\Commands\WarnCommand;
use ecstsy\MartianTools\Commands\WarningsCommand;
use ecstsy\MartianTools\Listeners\EventListener;
use ecstsy\MartianTools\Player\PlayerManager;
use ecstsy\MartianTools\Player\Warnings\PlayerWarnings;
use ecstsy\MartianTools\Utils\LanguageManager;
use ecstsy\MartianTools\Utils\Queries;
use ecstsy\MartianTools\Utils\Utils;
use ecstsy\MartianTools\Player\Warnings\WarningManager;
use JackMD\ConfigUpdater\ConfigUpdater;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

use function PHPSTORM_META\map;

class Loader extends PluginBase {

    use SingletonTrait;

    public int $configVer = 1;

    public int $enUsVer = 1;
    
    public static DataConnector $connector;

    public static PlayerManager $manager;

    public static WarningManager $warnings;

    public static LanguageManager $lang;

    protected function onLoad(): void {
        self::setInstance($this);
    }

    protected function onEnable(): void
    {
        $subDirectories = ["locale"];

        foreach ($subDirectories as $directory) {
            $this->saveAllFilesInDirectory($directory);
        }

        ConfigUpdater::checkUpdate($this, $this->getConfig(), "version", $this->configVer);

        $files = [
            "locale/en-us.yml" => $this->enUsVer
        ];

        foreach ($files as $file => $version) {
            ConfigUpdater::checkUpdate($this, Utils::getConfiguration($file), "version", $version);
        }

        self::$connector = libasynql::create($this, ["type" => "sqlite", "sqlite" => ["file" => "sqlite.sql"], "worker-limit" => 2], ["sqlite" => "sqlite.sql"]);
        self::$connector->executeGeneric(Queries::PLAYERS_INIT);
        self::$connector->executeGeneric(Queries::WARNINGS_INIT);
        self::$connector->waitAll();

        self::$manager = new PlayerManager($this);
        self::$lang = new LanguageManager($this->getConfig()->getNested("settings.language"));
        self::$warnings = new WarningManager($this);
        
        $listeners = [
            new EventListener()
        ];

        foreach ($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }

        $this->getServer()->getCommandMap()->registerAll("MartianTools", [
            new WarningsCommand($this, "warnings", "View player warnings"),
            new WarnCommand($this, "warn", "Warn a player"),
            new RemoveWarningCommand($this, "removewarning", "Remove a warning")
        ]);
    }

    /**
     * Unregisters a vanilla command if it conflicts with the plugin command.
     *
     * @param string $commandName The name of the command to unregister if it's a vanilla command.
     */
    private function unregisterVanillaCommand(string $commandName): void {
        $commandMap = $this->getServer()->getCommandMap();
        $command = $commandMap->getCommand($commandName);

        if ($command instanceof VanillaCommand) {
            $command->unregister($commandMap);
            $this->getLogger()->info("Unregistered vanilla command: {$commandName}");
        }
    }

    public static function getDatabase(): DataConnector {
        return self::$connector;
    }

    public static function getPlayerManager(): PlayerManager {
        return self::$manager;
    }

    public static function getLanguageManager(): LanguageManager {
        return self::$lang;
    }

    public static function getWarningsManager(): WarningManager {
        return self::$warnings;
    }

    private function saveAllFilesInDirectory(string $directory): void {
        $resourcePath = $this->getFile() . "resources/$directory/";
        if (!is_dir($resourcePath)) {
            $this->getLogger()->warning("Directory $directory does not exist.");
            return;
        }

        $files = scandir($resourcePath);
        if ($files === false) {
            $this->getLogger()->warning("Failed to read directory $directory.");
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $this->saveResource("$directory/$file");
        }
    }
}