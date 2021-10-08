<?php

namespace ThemeHouse\QAForums\Listener;

use XF\Entity\User;

/**
 * Class CriteriaUser
 * @package ThemeHouse\QAForums\Listener
 */
class CriteriaUser
{
    /**
     * @param $rule
     * @param array $data
     * @param User $user
     * @param $returnValue
     */
    public static function criteriaUser($rule, array $data, User $user, &$returnValue)
    {
        /** @var \ThemeHouse\QAForums\XF\Entity\User $user */

        switch ($rule) {
            case 'th_best_answers_qaforums':
                if (!empty($data['node_id'])) {
                    if (isset($user->ForumUserBestAnswers[$data['node_id'] . '-' . $user->user_id]) &&
                        $user->ForumUserBestAnswers[$data['node_id'] . '-' . $user->user_id]->best_answers >= $data['messages']) {
                        $returnValue = true;
                    }
                } elseif ($user->th_best_answers_qaforum >= $data['messages']) {
                    $returnValue = true;
                }
                break;
            case 'th_best_answers_max_qaforums':
                if (!empty($data['node_id'])) {
                    if (!isset($user->ForumUserBestAnswers[$data['node_id'] . '-' . $user->user_id]) ||
                        $user->ForumUserBestAnswers[$data['node_id'] . '-' . $user->user_id]->best_answers <= $data['messages']) {
                        $returnValue = true;
                    }
                } elseif ($user->th_best_answers_qaforum <= $data['messages']) {
                    $returnValue = true;
                }
                break;

            case 'th_up_votes_qaforums':
                if ($user->th_up_votes_qaforum >= $data['votes']) {
                    $returnValue = true;
                }
                break;
            case 'th_up_votes_max_qaforums':
                if ($user->th_up_votes_qaforum <= $data['votes']) {
                    $returnValue = true;
                }
                break;

            case 'th_down_votes_qaforums':
                if ($user->th_down_votes_qaforum >= $data['votes']) {
                    $returnValue = true;
                }
                break;
            case 'th_down_votes_max_qaforums':
                if ($user->th_down_votes_qaforum <= $data['votes']) {
                    $returnValue = true;
                }
                break;

            case 'th_points_qaforums':
                if ($user->th_points_qaforum >= $data['points']) {
                    $returnValue = true;
                }
                break;
            case 'th_points_max_qaforums':
                if ($user->th_points_qaforum <= $data['points']) {
                    $returnValue = true;
                }
                break;

            case 'th_questions_qaforums':
                if ($user->countTHQAForumsQuestions() >= $data['count']) {
                    $returnValue = true;
                }
                break;

            case 'th_questions_max_qaforums':
                if ($user->countTHQAForumsQuestions() <= $data['count']) {
                    $returnValue = true;
                }
                break;
        }
    }
}
