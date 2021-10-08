<?php

namespace ThemeHouse\QAForums\XF\Admin\Controller;

use XF\Db\Schema\Alter;
use XF\Mvc\Reply\View;

/**
 * Class Tools
 * @package ThemeHouse\QAForums\XF\Admin\Controller
 */
class Tools extends XFCP_Tools
{
    /**
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|View
     */
    public function actionRebuild()
    {
        $response = parent::actionRebuild();

        if ($response instanceof View) {
            $installedAddOns = $this->finder('XF:AddOn')->fetch();

            if (!empty($installedAddOns['ApanticBestAnswer'])) {
                $response->setParam('th_apantticBestAnswer_qaForum', true);
            }
        }

        return $response;
    }

    /**
     * @return \XF\Mvc\Reply\Redirect
     */
    public function actionUninstallNanocodeBestAnswer()
    {
        $schemaManager = $this->app()->db()->getSchemaManager();

        try {
            $schemaManager->dropTable('ba_votes');
            $schemaManager->alterTable('xf_user', function (Alter $table) {
                $table->dropColumns(['bestanswers']);
            });
            $schemaManager->alterTable('xf_thread', function (Alter $table) {
                $table->dropColumns(['bestanswer', 'ba_alternativeanswers']);
            });
            $schemaManager->alterTable('xf_post', function (Alter $table) {
                $table->dropColumns(['ba_votes']);
            });
        } catch (\Exception $e) {
        }

        return $this->redirect($this->buildLink('add-ons/ApanticBestAnswer/uninstall'));
    }
}
