<?php

namespace ThemeHouse\QAForums;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

/**
 * Class Setup
 * @package ThemeHouse\QAForums
 */
class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    /**
     *
     */
    public function installStep1()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->createTable('xf_th_vote_qaforum', function (Create $table) {
            $table->addColumn('vote_id', 'int')->autoIncrement();
            $table->addColumn('post_id', 'int');
            $table->addColumn('user_id', 'int');
            $table->addColumn('vote_type', 'varchar', 10);
            $table->addColumn('vote_date', 'int');
            $table->addUniqueKey(['post_id', 'user_id']);
            $table->addKey(['user_id', 'vote_type']);
        });
    }

    /**
     *
     */
    public function installStep2()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->createTable('xf_th_qaforums_forum_user_best_answers', function (Create $table) {
            $table->addColumn('node_id', 'int');
            $table->addColumn('user_id', 'int');
            $table->addColumn('best_answers', 'int');
            $table->addPrimaryKey(['node_id', 'user_id']);
            $table->addKey('user_id');
            $table->addKey('best_answers');
        });
    }

    /**
     *
     */
    public function installStep3()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_forum', function (Alter $table) {
            $table->addColumn('th_force_qa_qaforum', 'boolean')->setDefault(0);
        });
    }

    /**
     *
     */
    public function installStep4()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_post', function (Alter $table) {
            $table->addColumn('th_best_answer_qaforum', 'boolean')->setDefault(0);
            $table->addColumn('th_points_qaforum', 'int')->unsigned(false)->setDefault(0);
            $table->addColumn('th_up_votes_qaforum', 'int')->setDefault(0);
            $table->addColumn('th_down_votes_qaforum', 'int')->setDefault(0);
            $table->addColumn('th_best_answer_award_user_id_qaforum', 'int')->nullable();
            $table->addKey('th_best_answer_qaforum');
        });
    }

    /**
     *
     */
    public function installStep5()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_thread', function (Alter $table) {
            $table->addColumn('th_answered_qaforum', 'boolean')->setDefault(0);
            $table->addColumn('th_is_qa_qaforum', 'boolean')->setDefault(0);

            $table->addKey('th_answered_qaforum');
            $table->addKey('th_is_qa_qaforum');
        });
    }

    /**
     *
     */
    public function installStep6()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_user', function (Alter $table) {
            $table->addColumn('th_best_answers_qaforum', 'int')->setDefault(0);
            $table->addColumn('th_points_qaforum', 'int')->unsigned(false)->setDefault(0);
            $table->addColumn('th_up_votes_qaforum', 'int')->setDefault(0);
            $table->addColumn('th_down_votes_qaforum', 'int')->setDefault(0);
        });
    }

    /**
     * @param array $stateChanges
     */
    public function postInstall(array &$stateChanges)
    {
        $this->applyDefaultPermissions(0);
    }

    /**
     * @param $previousVersion
     */
    protected function applyDefaultPermissions($previousVersion)
    {
        if (!$previousVersion) {
            $this->applyGlobalPermission('forum', 'th_bestAnswerOwnThread', 'forum', 'postThread');
            $this->applyGlobalPermission('forum', 'th_voteAnswer', 'forum', 'like');
            $this->applyGlobalPermission('forum', 'th_viewVotes', 'general', 'view');
        }

        if ($previousVersion < 1001031) {
            $this->applyGlobalPermission('forum', 'th_removeQuestionOwn', 'forum', 'deleteOwnThread');
            $this->applyContentPermission('forum', 'th_removeQuestionOwn', 'forum', 'deleteOwnThread');
            $this->applyGlobalPermission('forum', 'th_removeQuestionAny', 'forum', 'deleteAnyThread');
            $this->applyContentPermission('forum', 'th_removeQuestionAny', 'forum', 'deleteAnyThread');
        }

        if ($previousVersion < 1010331) {
            $this->applyGlobalPermission('forum', 'th_bestAnswerOwnPost', 'postThread');
        }
    }

    /**
     *
     */
    public function upgrade1000032Step1()
    {
        $this->applyGlobalPermission('forum', 'th_viewVotes', 'general', 'view');

        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_user', function (Alter $table) {
            $table->addColumn('th_best_answers_qaforum', 'int')->setDefault(0);
            $table->addColumn('th_points_qaforum', 'int')->unsigned(false)->setDefault(0);
            $table->addColumn('th_up_votes_qaforum', 'int')->setDefault(0);
            $table->addColumn('th_down_votes_qaforum', 'int')->setDefault(0);
        });
    }

    /**
     *
     */
    public function upgrade1000032Step2()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_post', function (Alter $table) {
            $table->renameColumn('th_votes_qaforum', 'th_points_qaforum');
            $table->addColumn('th_up_votes_qaforum', 'int')->setDefault(0);
            $table->addColumn('th_down_votes_qaforum', 'int')->setDefault(0);
        });
    }

    /**
     *
     */
    public function upgrade1000170Step1()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_forum', function (Alter $table) {
            $table->addColumn('th_force_qa_qaforum', 'boolean')->setDefault(0);
        });
    }

    /**
     *
     */
    public function upgrade1000170Step2()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_thread', function (Alter $table) {
            $table->addColumn('th_is_qa_qaforum', 'boolean')->setDefault(0);
        });
    }

    /**
     * @throws \XF\Db\Exception
     */
    public function upgrade1000170Step3()
    {
        $this->db()->query('UPDATE xf_forum SET th_force_qa_qaforum = 1 WHERE th_qaforum = 1');
        $nodeIds = $this->db()->fetchAllColumn('SELECT node_id FROM xf_forum WHERE th_force_qa_qaforum = 1');
        if (!empty($nodeIds)) {
            $this->db()->query('UPDATE xf_thread SET th_is_qa_qaforum = 1 WHERE node_id IN (' . $this->db()->quote($nodeIds) . ')');
        }
    }

    /**
     *
     */
    public function upgrade1000970Step1()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_th_vote_qaforum', function (Alter $table) {
            $table->addUniqueKey(['post_id', 'user_id']);
        });
    }

    /**
     *
     */
    public function upgrade1000970Step2()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->createTable('xf_th_qaforums_forum_user_best_answers', function (Create $table) {
            $table->addColumn('node_id', 'int');
            $table->addColumn('user_id', 'int');
            $table->addColumn('best_answers', 'int');
            $table->addPrimaryKey(['node_id', 'user_id']);
            $table->addKey('user_id');
        });
    }

    /**
     *
     */
    public function upgrade1000970Step3()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_post', function (Alter $table) {
            $table->addKey('th_best_answer_qaforum');
        });
    }

    /**
     *
     */
    public function upgrade1000970Step4()
    {
        $this->app->jobManager()->enqueueUnique(
            'thQAForumsForum',
            'ThemeHouse\QAForums:Forum',
            [],
            false
        );
    }

    /**
     *
     */
    public function upgrade1001013Step1()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_th_qaforums_forum_user_best_answers', function (Alter $table) {
            $table->addKey('best_answers');
        });
    }

    /**
     * @throws \XF\Db\Exception
     */
    public function upgrade1001031Step1()
    {
        $db = $this->db();

        $db->query("
			REPLACE INTO xf_permission_entry_content
				(content_type, content_id, user_group_id, user_id,
				permission_group_id, permission_id, permission_value, permission_value_int)
			SELECT 'node', node_id, 2, 0, 'forum', 'th_addQuestionOwnThread', 'content_allow', 0
			FROM xf_forum
			WHERE th_qaforum = 1
		");
    }

    /**
     *
     */
    public function upgrade1001031Step2()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_forum', function (Alter $table) {
            $table->dropColumns([
                'th_qaforum',
            ]);
        });
    }

    /**
     *
     */
    public function upgrade1001073Step3()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_th_vote_qaforum', function (Alter $table) {
            $table->addKey(['user_id', 'vote_type']);
        });
    }

    /**
     *
     */
    public function upgrade1010331Step1()
    {
        $this->schemaManager()->alterTable('xf_post', function (Alter $table) {
            $table->addColumn('th_best_answer_award_user_id_qaforum', 'int')->nullable();
        });
    }

    /**
     *
     */
    public function upgrade1010331Step2()
    {
        $this->app->jobManager()->enqueueUnique('searchRebuild', 'XF:SearchRebuild');
    }

    public function upgrade1010511Step1()
    {
        $schemaManager = $this->schemaManager();
        $schemaManager->alterTable('xf_thread', function (Alter $table) {
            $table->addKey('th_answered_qaforum');
            $table->addKey('th_is_qa_qaforum');
        });

    }

    /**
     * @param $previousVersion
     * @param array $stateChanges
     */
    public function postUpgrade($previousVersion, array &$stateChanges)
    {
        $this->applyDefaultPermissions($previousVersion);
    }

    /**
     *
     */
    public function uninstallStep1()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->dropTable('xf_th_vote_qaforum');
    }

    /**
     *
     */
    public function uninstallStep2()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_forum', function (Alter $table) {
            $table->dropColumns([
                'th_force_qa_qaforum',
            ]);
        });
    }

    /**
     *
     */
    public function uninstallStep3()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_post', function (Alter $table) {
            $table->dropColumns([
                'th_best_answer_qaforum',
                'th_points_qaforum',
                'th_up_votes_qaforum',
                'th_down_votes_qaforum',
            ]);
        });
    }

    /**
     *
     */
    public function uninstallStep4()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_thread', function (Alter $table) {
            $table->dropColumns([
                'th_is_qa_qaforum',
                'th_answered_qaforum',
            ]);
        });
    }

    /**
     *
     */
    public function uninstallStep5()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_user', function (Alter $table) {
            $table->dropColumns([
                'th_best_answers_qaforum',
                'th_points_qaforum',
                'th_up_votes_qaforum',
                'th_down_votes_qaforum',
            ]);
        });
    }

    /**
     * 
     */
    public function uninstallStep6() {
        $this->schemaManager()->dropTable('xf_th_qaforums_forum_user_best_answers');
    }
}
