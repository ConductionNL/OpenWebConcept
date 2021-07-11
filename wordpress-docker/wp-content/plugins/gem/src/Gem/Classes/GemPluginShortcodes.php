<?php

namespace OWC\Gem\Classes;

use OWC\Gem\Foundation\Plugin;

class GemPluginShortcodes
{
    /** @var Plugin */
    protected $plugin;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        $this->add_shortcode();
    }

    private function add_shortcode(): void
    {
        add_shortcode('gem-chatbox', [$this, 'gem_chatbox_shortcode']);
    }



    /**
     * Callback for shortcode [gem-chatbox].
     *
     * @return string
     */
    public function gem_chatbox_shortcode(): string
    {
        return '<div id="webchat"></div>';
    }

}
