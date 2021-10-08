<?php

namespace ThemeHouse\QAForums\ThemeHouse\UserImprovements\Pub\Controller;

use ThemeHouse\QAForums\XF\Entity\User;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

/**
 * Class TrophyCategory
 * @package ThemeHouse\QAForums\ThemeHouse\UserImprovements\Pub\Controller
 */
class TrophyCategory extends XFCP_TrophyCategory
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
                    /** @var User $user */
                    $user = \XF::visitor();
                    $nodeId = $trophyProgressCriteria['additionalData']['node_id'];
                    if (isset($user->ForumUserBestAnswers[$nodeId . '-' . $user->user_id])) {
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
}
