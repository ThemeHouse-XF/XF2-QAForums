<?php

namespace ThemeHouse\QAForums\Import\Importer;

use XF\Import\Importer\AbstractImporter;

/**
 * Class AbstractQAForum
 * @package ThemeHouse\QAForums\Import\Importer
 */
abstract class AbstractQAForum extends AbstractImporter
{
    /**
     * @var \XF\Db\Mysqli\Adapter
     */
    protected $sourceDb;

    /**
     * @return bool
     */
    public function isBeta()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canRetainIds()
    {
        return false;
    }

    /**
     * @param array $stepsRun
     * @return array
     */
    public function getFinalizeJobs(array $stepsRun)
    {
        return [
            'ThemeHouse\QAForums:User'
        ];
    }

    /**
     * @return bool
     */
    public function resetDataForRetainIds()
    {
        return false;
    }

    /**
     * @param array $vars
     */
    public function renderBaseConfigOptions(array $vars)
    {
    }

    /**
     * @param array $baseConfig
     * @param array $errors
     * @return bool
     */
    public function validateBaseConfig(array &$baseConfig, array &$errors)
    {
        return true;
    }

    /**
     * @param array $vars
     */
    public function renderStepConfigOptions(array $vars)
    {
    }

    /**
     * @param array $steps
     * @param array $stepConfig
     * @param array $errors
     * @return bool
     */
    public function validateStepConfig(array $steps, array &$stepConfig, array &$errors)
    {
        return true;
    }

    /**
     * @return array
     */
    protected function getBaseConfigDefault()
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getStepConfigDefault()
    {
        return [];
    }

    /**
     *
     */
    protected function doInitializeSource()
    {
        $this->sourceDb = $this->db();
    }
}