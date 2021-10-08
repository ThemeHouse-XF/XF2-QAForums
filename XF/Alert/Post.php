<?php

namespace ThemeHouse\QAForums\XF\Alert;

/**
 * Class Post
 * @package ThemeHouse\QAForums\XF\Alert
 */
class Post extends XFCP_Post
{
    /**
     * @return array
     */
    public function getOptOutActions()
    {
        $actions = parent::getOptOutActions();

        array_push($actions, 'best_answer', 'thqa_vote');

        return $actions;
    }
}
