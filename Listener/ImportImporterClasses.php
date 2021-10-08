<?php

namespace ThemeHouse\QAForums\Listener;

use XF\Container;
use XF\SubContainer\Import;

/**
 * Class ImportImporterClasses
 * @package ThemeHouse\QAForums\Listener
 */
class ImportImporterClasses
{
    /**
     * @param Import $container
     * @param Container $parentContainer
     * @param array $importers
     */
    public static function importImporterClasses(Import $container, Container $parentContainer, array &$importers)
    {
        $importers[] = '\ThemeHouse\QAForums:NanocodeBestAnswer';
    }
}
