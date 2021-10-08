<?php

namespace ThemeHouse\QAForums\Listener;

use ThemeHouse\QAForums\Util\Number;
use XF\Container;
use XF\Template\Templater;

/**
 * Class TemplaterSetup
 * @package ThemeHouse\QAForums\Listener
 */
class TemplaterSetup
{
    /**
     * @param Container $container
     * @param Templater $templater
     */
    public static function templaterSetup(Container $container, Templater &$templater)
    {
        $templater->addFilter('th_friendlynumber_qaforum', function (Templater $templater, $value, &$escape) {
            return Number::formatFriendlyNumber($value);
        });
    }
}
