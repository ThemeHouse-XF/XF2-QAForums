<?php

namespace ThemeHouse\QAForums\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

/**
 * Class Member
 * @package ThemeHouse\QAForums\XF\Pub\Controller
 */
class Member extends XFCP_Member
{
    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionBestAnswers(ParameterBag $params)
    {
        $user = $this->assertViewableUser($params['user_id']);

        $searcher = $this->app->search();
        $query = $searcher->getQuery();

        $query->byUserId($user->user_id)
            ->inType('post')
            ->withMetadata('thbestanswer', true)
            ->orderedBy('date');

        $resultSet = $searcher->getResultSet($searcher->search($query));
        $resultSet->limitResults(15);

        $results = $searcher->wrapResultsForRender($resultSet);
        $resultCount = $resultSet->countResults();

        $viewParams = [
            'user' => $user,
            'results' => $results,
            'resultCount' => $resultCount
        ];
        return $this->view('ThemeHouse\QAForums:Member\BestAnswers', 'thqaforums_member_best_answers', $viewParams);
    }
}
