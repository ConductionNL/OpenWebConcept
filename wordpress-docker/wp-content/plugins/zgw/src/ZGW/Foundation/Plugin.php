<?php

namespace OWC\ZGW\Foundation;

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
        new \OWC\ZGW\Classes\ZGWPluginShortcodes($this);
        new \OWC\ZGW\Classes\ZGWPluginAdminSettings();
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
