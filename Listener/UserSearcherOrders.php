<?php

namespace ThemeHouse\QAForums\Listener;

use XF\Searcher\User;

/**
 * Class UserSearcherOrders
 * @package ThemeHouse\QAForums\Listener
 */
class UserSearcherOrders
{
    /**
     * @param User $userSearcher
     * @param array $sortOrders
     */
    public static function userSearcherOrders(User $userSearcher, array &$sortOrders)
    {
        $sortOrders = array_replace($sortOrders, [
            'th_best_answers_qaforum' => \XF::phrase('thqaforums_most_answers'),
            'th_points_qaforum' => \XF::phrase('thqaforums_qa_points'),
            'th_up_votes_qaforum' => \XF::phrase('thqaforums_upvotes'),
            'th_down_votes_qaforum' => \XF::phrase('thqaforums_downvotes')
        ]);
    }
}
