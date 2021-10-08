<?php

namespace ThemeHouse\QAForums\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int user_id
 * @property int node_id
 * @property int best_answers
 *
 * RELATIONS
 * @property \XF\Entity\Forum Forum
 * @property \XF\Entity\User User
 */
class ForumUserBestAnswers extends Entity
{
    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_th_qaforums_forum_user_best_answers';
        $structure->shortName = 'ThemeHouse\QAForums:ForumUserBestAnswers';
        $structure->primaryKey = ['node_id', 'user_id'];
        $structure->columns = [
            'node_id' => ['type' => self::UINT],
            'user_id' => ['type' => self::UINT],
            'best_answers' => ['type' => self::UINT],
        ];
        $structure->relations = [
            'Forum' => [
                'entity' => 'XF:Forum',
                'type' => self::TO_ONE,
                'conditions' => 'node_id',
            ],
            'User' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'user_id',
            ],
        ];

        return $structure;
    }
}
