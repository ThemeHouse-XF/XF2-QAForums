<?php

namespace ThemeHouse\QAForums\Listener;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Manager;
use XF\Mvc\Entity\Structure;

/**
 * Class EntityStructure
 * @package ThemeHouse\QAForums\Listener
 */
class EntityStructure
{
    /**
     * @param Manager $em
     * @param Structure $structure
     */
    public static function xfUser(Manager $em, Structure &$structure)
    {
        $structure->columns['th_best_answers_qaforum'] = [
            'type' => Entity::UINT,
            'default' => 0,
            'changeLog' => false,
            'api' => true
        ];

        $structure->columns['th_points_qaforum'] = [
            'type' => Entity::INT,
            'default' => 0,
            'changeLog' => false,
            'api' => true
        ];

        $structure->columns['th_up_votes_qaforum'] = [
            'type' => Entity::UINT,
            'default' => 0,
            'changeLog' => false,
            'api' => true
        ];

        $structure->columns['th_down_votes_qaforum'] = [
            'type' => Entity::UINT,
            'default' => 0,
            'changeLog' => false,
            'api' => true
        ];

        $structure->relations['ForumUserBestAnswers'] = [
            'type' => Entity::TO_MANY,
            'entity' => 'ThemeHouse\QAForums:ForumUserBestAnswers',
            'conditions' => [
                'user_id',
            ]
        ];

        $structure->getters['forum_user_best_answers'] = true;
    }

    /**
     * @param Manager $em
     * @param Structure $structure
     */
    public static function xfForum(Manager $em, Structure &$structure)
    {
        $visitor = \XF::visitor();

        $structure->columns['th_force_qa_qaforum'] = [
            'type' => Entity::BOOL,
            'default' => false,
            'api' => true
        ];

        $structure->relations['ForumUserBestAnswers'] = [
            'type' => Entity::TO_ONE,
            'entity' => 'ThemeHouse\QAForums:ForumUserBestAnswers',
            'conditions' => [
                'node_id',
                ['user_id', '=', $visitor->user_id],
            ],
        ];
    }

    /**
     * @param Manager $em
     * @param Structure $structure
     */
    public static function xfPost(Manager $em, Structure &$structure)
    {
        $visitor = \XF::visitor();

        $structure->columns['th_best_answer_qaforum'] = [
            'type' => Entity::BOOL,
            'default' => false,
            'api' => true
        ];

        $structure->columns['th_points_qaforum'] = [
            'type' => Entity::INT,
            'default' => 0,
            'api' => true
        ];

        $structure->columns['th_up_votes_qaforum'] = [
            'type' => Entity::UINT,
            'default' => 0,
            'api' => true
        ];

        $structure->columns['th_down_votes_qaforum'] = [
            'type' => Entity::UINT,
            'default' => 0,
            'api' => true
        ];

        $structure->columns['th_best_answer_award_user_id_qaforum'] = [
            'type' => Entity::UINT,
            'default' => 0,
            'nullable' => true,
            'api' => true
        ];

        $structure->relations['CurrentVote'] = [
            'type' => Entity::TO_ONE,
            'entity' => 'ThemeHouse\QAForums:Vote',
            'conditions' => [
                'post_id',
                ['user_id', '=', $visitor->user_id],
            ],
        ];

        $structure->relations['BestAnswerAwardUser'] = [
            'type' => Entity::TO_ONE,
            'entity' => 'XF:User',
            'conditions' => [
                ['user_id', '=', '$th_best_answer_award_user_id_qaforum']
            ]
        ];

        $structure->relations['Thread']['with'][] = 'Forum.ForumUserBestAnswers';

        $structure->behaviors['XF:Indexable']['checkForUpdates'][] = 'th_best_answer_qaforum';
    }

    /**
     * @param Manager $em
     * @param Structure $structure
     */
    public static function xfThread(Manager $em, Structure &$structure)
    {
        $structure->columns['th_answered_qaforum'] = [
            'type' => Entity::BOOL,
            'default' => false,
            'api' => true
        ];

        $structure->columns['th_is_qa_qaforum'] = [
            'type' => Entity::BOOL,
            'default' => false,
            'api' => true
        ];

        $structure->relations['BestAnswer'] = [
            'type' => Entity::TO_ONE,
            'entity' => 'XF:Post',
            'api' => true,
            'conditions' => [
                'thread_id',
                ['th_best_answer_qaforum', '=', true],
            ],
        ];
    }
}
