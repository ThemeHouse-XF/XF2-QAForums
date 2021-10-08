<?php

namespace ThemeHouse\QAForums\XF\Entity;

use ThemeHouse\QAForums\Entity\Vote;

/**
 * Class Post
 * @package ThemeHouse\QAForums\XF\Entity
 *
 * @property integer th_up_votes_qaforum
 * @property integer th_down_votes_qaforum
 * @property integer th_points_qaforum
 * @property boolean th_best_answer_qaforum
 * @property integer th_best_answer_award_user_id_qaforum
 *
 * @property Thread Thread
 * @property User User
 * @property Vote CurrentVote
 */
class Post extends XFCP_Post
{
    /**
     * @return bool
     */
    public function rebuildQAVoteCounts()
    {
        $upVotes = $this->finder('ThemeHouse\QAForums:Vote')
            ->where('post_id', '=', $this->post_id)
            ->where('vote_type', '=', 'up')
            ->total();
        $downVotes = $this->finder('ThemeHouse\QAForums:Vote')
            ->where('post_id', '=', $this->post_id)
            ->where('vote_type', '=', 'down')
            ->total();

        $voteCount = $upVotes - $downVotes;

        $this->th_up_votes_qaforum = $upVotes;
        $this->th_down_votes_qaforum = $downVotes;
        $this->th_points_qaforum = $voteCount;

        if ($this->isChanged('th_up_votes_qaforum') || $this->isChanged('th_down_votes_qaforum') || $this->isChanged('th_points_qaforum')) {
            return true;
        }

        return false;
    }

    /**
     * @param null $error
     * @return bool
     */
    public function canMarkAsBestAnswer(&$error = null)
    {
        $visitor = \XF::visitor();
        $thread = $this->Thread;
        $nodeId = $thread->node_id;

        if (!$thread->th_is_qa_qaforum) {
            return false;
        }

        if ($this->isFirstPost()) {
            return false;
        }

        if (!$this->canView($error)) {
            return false;
        }

        if (!$visitor->user_id || !$thread) {
            return false;
        }

        if ($visitor->hasNodePermission($nodeId, 'th_bestAnswerAnyThread')) {
            if ($this->user_id == $visitor->user_id) {
                return $visitor->hasNodePermission($nodeId, 'th_bestAnswerOwnPost');
            }

            return true;
        }

        if ($thread->user_id === $visitor->user_id && $visitor->hasNodePermission($nodeId, 'th_bestAnswerOwnThread')) {
            if ($this->user_id == $visitor->user_id) {
                return $visitor->hasNodePermission($nodeId, 'th_bestAnswerOwnPost');
            }

            return true;
        }

        return false;
    }

    /**
     * @param bool $returnFalseIfVoted
     * @param null $error
     * @return bool
     */
    public function canVoteAnswer($returnFalseIfVoted = false, &$error = null)
    {
        $user = \XF::visitor();

        // User must be logged in to vote
        if (!$user->user_id) {
            return false;
        }

        if ($this->user_id === $user->user_id) {
            return false;
        }

        if ($this->Thread->user_id === $user->user_id && !$user->hasNodePermission($this->Thread->node_id,
                'th_voteAnswerOwnThread')) {
            return false;
        }

        if ($this->Thread->user_id !== $user->user_id && !$user->hasNodePermission($this->Thread->node_id,
                'th_voteAnswer')) {
            return false;
        }

        return true;
    }

    /**
     * @param null $error
     * @return bool
     */
    public function canViewVoteDetails(&$error = null)
    {
        $user = \XF::visitor();

        return $user->hasNodePermission($this->Thread->node_id, 'th_viewVotes');
    }

    /**
     * @return bool
     */
    public function canSeeBestAnswerAwarder()
    {
        return \XF::visitor()->hasNodePermission($this->Thread->node_id, 'th_seeBestAnswerAwarder');
    }

    /**
     * @return bool
     */
    public function isBelowPointThreshold()
    {
        if ($this->Thread->th_is_qa_qaforum && !$this->isFirstPost()) {
            $pointThreshold = \XF::options()->th_hidePostThreshold_qaForums;
            if (!empty($pointThreshold['enabled'])) {
                if ($this->th_points_qaforum < $pointThreshold['points']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @throws \XF\PrintableException
     */
    public function checkBestAnswerThreshold()
    {
        if (!$this->th_best_answer_qaforum && $this->isAboveBestAnswerThreshold()) {
            $this->Thread->removeBestAnswer();
            $this->th_best_answer_qaforum = true;
            $this->save();
        }
    }

    /**
     * @return bool
     */
    protected function isAboveBestAnswerThreshold()
    {
        if ($this->Thread->th_is_qa_qaforum && !$this->isFirstPost()) {
            $daysLimit = \XF::options()->thqaforums_subsequentBestAnswerDaysLimit;
            if (!empty($daysLimit['enabled'])) {
                if ($this->Thread->post_date < (\XF::$time - $daysLimit['days'] * 86400)) {
                    return false;
                }
            }
            $pointThreshold = \XF::options()->thqaforums_bestAnswerThreshold;
            if (!empty($pointThreshold['enabled'])) {
                $bestAnswerThreshold = $pointThreshold['points'];
                if ($this->Thread->BestAnswer) {
                    $bestAnswerThreshold = max(
                        $this->Thread->BestAnswer->th_points_qaforum + 1,
                        $bestAnswerThreshold
                    );
                }
                if ($this->th_points_qaforum >= $bestAnswerThreshold) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @throws \XF\PrintableException
     */
    protected function _postSave()
    {
        parent::_postSave();

        if ($this->isUpdate()) {
            if ($this->isChanged('th_best_answer_qaforum')) {
                if ($this->th_best_answer_qaforum) {
                    $bestAnswers = $this->User->th_best_answers_qaforum + 1;
                    $this->adjustQAForumUserBestAnswerCount(1);
                    $this->sendBestAnswerAlert($this);
                } else {
                    $bestAnswers = $this->User->th_best_answers_qaforum - 1;
                    $this->adjustQAForumUserBestAnswerCount(-1);
                    $this->removeBestAnswerAlert($this);
                }
                if ($bestAnswers < 0) {
                    $bestAnswers = 0;
                }
                $this->User->th_best_answers_qaforum = $bestAnswers;
                $this->User->save();

                if ($this->Thread && $this->Thread->th_is_qa_qaforum) {
                    $this->Thread->th_answered_qaforum = $this->th_best_answer_qaforum;
                    $this->Thread->save();
                }
            }
        }
    }

    /**
     * @param $amount
     */
    protected function adjustQAForumUserBestAnswerCount($amount)
    {
        if ($this->user_id) {
            $db = $this->db();

            if ($amount > 0) {
                $db->insert('xf_th_qaforums_forum_user_best_answers', [
                    'node_id' => $this->Thread->node_id,
                    'user_id' => $this->user_id,
                    'best_answers' => $amount
                ], false, 'best_answers = best_answers + VALUES(best_answers)');
            } else {
                $existingValue = $db->fetchOne("
					SELECT best_answers
					FROM xf_th_qaforums_forum_user_best_answers
					WHERE node_id = ?
						AND user_id = ?
				", [$this->Thread->node_id, $this->user_id]);
                if ($existingValue !== null) {
                    $newValue = $existingValue + $amount;
                    if ($newValue <= 0) {
                        $this->db()->delete(
                            'xf_th_qaforums_forum_user_best_answers',
                            'node_id = ? AND user_id = ?',
                            [$this->Thread->node_id, $this->user_id]
                        );
                    } else {
                        $this->db()->update(
                            'xf_th_qaforums_forum_user_best_answers',
                            ['best_answers' => $newValue],
                            'node_id = ? AND user_id = ?',
                            [$this->Thread->node_id, $this->user_id]
                        );
                    }
                }
            }
        }
    }

    /**
     * @param \XF\Entity\Post $post
     * @param \XF\Entity\User|null $viewingUser
     * @return bool
     */
    public function sendBestAnswerAlert(\XF\Entity\Post $post, \XF\Entity\User $viewingUser = null)
    {
        if (!$viewingUser) {
            $viewingUser = \XF::visitor();
        }

        if (!$post->User || $post->user_id === \XF::visitor()->user_id) {
            return false;
        }

        /** @var \XF\Repository\UserAlert $alertRepo */
        $alertRepo = \XF::repository('XF:UserAlert');
        return $alertRepo->alertFromUser(
            $post->User,
            $viewingUser,
            'post',
            $post->post_id,
            'best_answer', [
                'depends_on_addon_id' => 'ThemeHouse/QAForums'
            ]
        );
    }

    /**
     * @param \XF\Entity\Post $post
     */
    public function removeBestAnswerAlert(\XF\Entity\Post $post)
    {
        /** @var \XF\Repository\UserAlert $alertRepo */
        $alertRepo = \XF::repository('XF:UserAlert');
        $alertRepo->fastDeleteAlertsToUser($post->user_id, 'post', $post->post_id, 'best_answer');
    }

    /**
     * @throws \XF\PrintableException
     */
    protected function _postDelete()
    {
        parent::_postDelete();

        if ($this->th_best_answer_qaforum) {
            $this->Thread->th_answered_qaforum = false;
            $bestAnswers = $this->User->th_best_answers_qaforum - 1;
            $this->adjustQAForumUserBestAnswerCount(-1);

            $this->removeBestAnswerAlert($this);
            if ($bestAnswers < 0) {
                $bestAnswers = 0;
            }

            $this->User->th_best_answers_qaforum = $bestAnswers;
            $this->User->save();
        }
    }
}
