<?php

namespace OWC\ZGW\Classes;

use OWC\ZGW\Foundation\Plugin;

class ZGWPluginShortcodes
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
        add_shortcode('zgw-casetable', [$this, 'zgw_casetable_shortcode']);
    }



    /**
     * Callback for shortcode [gw-casetable].
     *
     * @return string
     */
    public function zgw_casetable_shortcode(): string
    {
        return 'All your case belong to us';
    }

}
