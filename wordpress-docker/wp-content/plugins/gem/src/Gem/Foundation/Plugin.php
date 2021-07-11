<?php

namespace OWC\Gem\Foundation;

class Plugin
{
    /**
     * Path to the root of the plugin.
     *
     * @var string $rootPath
     */
    protected $rootPath;

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
    }

    /**
     * Boot plugin classes.
     *
     * @return void
     */
    public function boot(): void
    {
        new \OWC\Waardepapieren\Classes\WaardepapierenPluginShortcodes($this);
        new \OWC\Waardepapieren\Classes\WaardepapierenPluginAdminSettings();
    }

    /**
     * Return root path of plugin.
     *
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }
}
