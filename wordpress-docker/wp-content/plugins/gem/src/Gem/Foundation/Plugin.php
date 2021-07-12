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
        new \OWC\Gem\Classes\GemPluginShortcodes($this);
        new \OWC\Gem\Classes\GemPluginAdminSettings();
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
