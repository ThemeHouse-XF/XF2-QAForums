<?php

namespace ThemeHouse\QAForums\XF\Pub\Controller;

use ThemeHouse\QAForums\XF\Entity\User;

/**
 * Class Search
 * @package ThemeHouse\QAForums\XF\Pub\Controller
 */
class Search extends XFCP_Search
{
    /**
     * @return \XF\Mvc\Reply\Message|\XF\Mvc\Reply\Redirect
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionMemberBestAnswers()
    {
        $userId = $this->filter('user_id', 'uint');
        /** @var User $user */
        $user = $this->assertRecordExists('XF:User', $userId, null, 'requested_member_not_found');

        $constraints = ['users' => $user->username];

        $searcher = $this->app->search();
        $query = $searcher->getQuery();
        $query->byUserId($user->user_id)
            ->inType('post')
            ->withMetadata('thbestanswer', true)
            ->orderedBy('date');

        return $this->runSearch($query, $constraints, false);
    }
}
