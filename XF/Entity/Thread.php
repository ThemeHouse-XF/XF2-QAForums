<?php

namespace ThemeHouse\QAForums\XF\Entity;

use ThemeHouse\QAForums\Repository\Vote;
use XF\Entity\ThreadPrefix;

/**
 * Class Thread
 * @package ThemeHouse\QAForums\XF\Entity
 *
 * @property boolean th_is_qa_qaforum
 * @property boolean th_answered_qaforum
 *
 * @property Post BestAnswer
 * @property Forum Forum
 */
class Thread extends XFCP_Thread
{
    /**
     * @param null $error
     * @return bool
     */
    public function canAddQuestionStatus(&$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return false;
        }

        $nodeId = $this->node_id;

        if ($visitor->hasNodePermission($nodeId, 'th_addQuestionAny')) {
            return true;
        }

        if ($this->user_id == $visitor->user_id && $visitor->hasNodePermission($nodeId, 'editOwnPost')) {
            $editLimit = $visitor->hasNodePermission($nodeId, 'editOwnPostTimeLimit');
            if ($editLimit != -1 && (!$editLimit || $this->post_date < \XF::$time - 60 * $editLimit)) {
                $error = \XF::phraseDeferred('message_edit_time_limit_expired', ['minutes' => $editLimit]);
                return false;
            }

            if (!$this->Forum || !$this->Forum->allow_posting) {
                $error = \XF::phraseDeferred('you_may_not_perform_this_action_because_forum_does_not_allow_posting');
                return false;
            }

            return $visitor->hasNodePermission($nodeId, 'th_addQuestionOwnThread');
        }

        if ($this->isInsert()) {
            return $visitor->hasNodePermission($nodeId, 'th_addQuestionOwnThread');
        }

        return false;
    }

    /**
     *
     */
    protected function _preSave()
    {
        parent::_preSave();

        $options = \XF::options();

        if ($this->isInsert() && $this->th_is_qa_qaforum && !$this->prefix_id && $options->th_qaPrefix_qaForums) {
            $prefix = $this->em()->find('XF:ThreadPrefix', $options->th_qaPrefix_qaForums);
            if ($prefix && $this->Forum->isPrefixValid($prefix)) {
                $this->prefix_id = $options->th_qaPrefix_qaForums;
            }
        }

        if (!$this->th_is_qa_qaforum) {
            $this->th_answered_qaforum = false;
        }

        if ($this->isUpdate() && $this->isChanged('th_answered_qaforum')) {
            $answeredPrefix = intval($options->th_answeredPrefix_qaForums);
            $questionPrefix = intval($options->th_qaPrefix_qaForums);

            $answered = $this->th_answered_qaforum;
            $prefix = false;
            if ($answered && $answeredPrefix) {
                /** @var ThreadPrefix $prefix */
                $prefix = $this->em()->find('XF:ThreadPrefix', $answeredPrefix);
            }

            if (!$answered && $questionPrefix) {
                if (!$this->prefix_id || ($answeredPrefix && $this->prefix_id === $answeredPrefix)) {
                    $prefix = $this->em()->find('XF:ThreadPrefix', $questionPrefix);
                }
            }

            if ($prefix && $this->Forum->isPrefixValid($prefix)) {
                $this->prefix_id = $prefix->prefix_id;
            }
        }

        if ($this->isChanged('node_id')) {
            if (!$this->th_is_qa_qaforum && $this->Forum->th_force_qa_qaforum && !$this->canRemoveQuestionStatus()) {
                $this->th_is_qa_qaforum = 1;
            }
        }
    }

    /**
     * @param null $error
     * @return bool
     */
    public function canRemoveQuestionStatus(&$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return false;
        }

        $nodeId = $this->node_id;

        if ($visitor->hasNodePermission($nodeId, 'th_removeQuestionAny')) {
            return true;
        }

        if ($this->user_id == $visitor->user_id && $visitor->hasNodePermission($nodeId, 'editOwnPost')) {
            $editLimit = $visitor->hasNodePermission($nodeId, 'editOwnPostTimeLimit');
            if ($editLimit != -1 && (!$editLimit || $this->post_date < \XF::$time - 60 * $editLimit)) {
                $error = \XF::phraseDeferred('message_edit_time_limit_expired', ['minutes' => $editLimit]);
                return false;
            }

            if (!$this->Forum || !$this->Forum->allow_posting) {
                $error = \XF::phraseDeferred('you_may_not_perform_this_action_because_forum_does_not_allow_posting');
                return false;
            }

            return $visitor->hasNodePermission($nodeId, 'th_removeQuestionOwn');
        }

        return false;
    }

    /**
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    protected function _postSave()
    {
        parent::_postSave();

        if ($this->isUpdate() && $this->isChanged('th_is_qa_qaforum') && !$this->th_is_qa_qaforum) {
            $this->removeBestAnswer();
            /** @var Vote $voteRepository */
            $voteRepository = $this->repository('ThemeHouse\QAForums:Vote');
            $voteRepository->removeVotesFromThread($this->thread_id);
        }
    }

    /**
     * @throws \XF\PrintableException
     */
    public function removeBestAnswer()
    {
        $post = $this->BestAnswer;
        if ($post) {
            $post->th_best_answer_qaforum = false;
            $post->save();
        }
    }

    /**
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    protected function _postDelete()
    {
        parent::_postDelete();

        if ($this->th_is_qa_qaforum) {
            $this->removeBestAnswer();
            /** @var Vote $voteRepository */
            $voteRepository = $this->repository('ThemeHouse\QAForums:Vote');
            $voteRepository->removeVotesFromThread($this->thread_id);
        }
    }
}
