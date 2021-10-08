<?php

namespace ThemeHouse\QAForums\ThemeHouse\UserImprovements\Pub\Controller;

use ThemeHouse\QAForums\XF\Entity\User;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

/**
 * Class Trophy
 * @package ThemeHouse\QAForums\ThemeHouse\UserImprovements\Pub\Controller
 */
class Trophy extends XFCP_Trophy
{
    /**
     * @param ParameterBag $params
     * @return View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionView(ParameterBag $params)
    {
        $reply = parent::actionView($params);

        if ($reply instanceof View && $reply->getParam('trophyProgressCriteria')) {
            $trophyProgressCriteria = $reply->getParam('trophyProgressCriteria');
            if ($trophyProgressCriteria['valueKey'] === 'th_best_answers_qaforum') {
                if (!empty($trophyProgressCriteria['additionalData']['node_id'])) {
                    $user = \XF::visitor();
                    $nodeId = $trophyProgressCriteria['additionalData']['node_id'];
                    $trophyProgressCriteria['valueKey'] = 'forum_user_best_answers';
                    $trophyProgressCriteria['valueSubKey'] = $nodeId;
                    $reply->setParam('trophyProgressCriteria', $trophyProgressCriteria);
                    if (isset($user->ForumUserBestAnswers[$nodeId . '-' . $user->user_id])) {
                        /** @var User $user */
                        $progressValue = $user->ForumUserBestAnswers[$nodeId . '-' . $user->user_id]->best_answers;
                        $reply->setParam('progressValue', $progressValue);
                    } else {
                        $reply->setParam('progressValue', 0);
                    }
                }
            }
        }

        return $reply;
    }

    /**
     * @return View
     */
    public function actionStatsThBestAnswersQaforum()
    {
        $nodeId = $this->filter('node_id', 'int');

        $limit = $this->options()->membersPerPage;

        $finder = $this->finder('ThemeHouse\QAForums:ForumUserBestAnswers')
            ->with('User', true)
            ->with('User.Option', true)
            ->with('User.Profile', true)
            ->where('User.user_state', '=', 'valid')
            ->where('User.is_banned', '=', 0)
            ->order('best_answers', 'desc')
            ->limit($limit);

        if ($nodeId) {
            $finder->where('node_id', $nodeId);
        }

        $users = $finder->fetch();

        $viewParams = [
            'users' => $users,
            'extraData' => 'best_answers',
            'title' => \XF::phrase('thqaforums_most_answers'),
            'userKey' => 'User',
        ];

        return $this->view(
            'ThemeHouse\QAForums:Trophy\Stats\ThBestAnswersQaforum',
            'thuserimprovements_trophy_stats',
            $viewParams
        );
    }

    /**
     * @return \XF\Mvc\Reply\Reroute
     */
    public function actionStatsForumUserBestAnswers()
    {
        return $this->rerouteController('ThemeHouse\UserImprovements:Trophy', 'statsThBestAnswersQaforum');
    }
}
