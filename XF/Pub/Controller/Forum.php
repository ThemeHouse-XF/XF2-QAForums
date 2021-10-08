<?php

namespace ThemeHouse\QAForums\XF\Pub\Controller;

use ThemeHouse\QAForums\XF\Service\Thread\Creator;

/**
 * Class Forum
 * @package ThemeHouse\QAForums\XF\Pub\Controller
 */
class Forum extends XFCP_Forum
{
    /**
     * @param \XF\Entity\Forum $forum
     * @return Creator|\XF\Service\Thread\Creator
     */
    protected function setupThreadCreate(\XF\Entity\Forum $forum)
    {
        /** @var Creator $creator */
        $creator = parent::setupThreadCreate($forum);

        /** @var \ThemeHouse\QAForums\XF\Entity\Thread $thread */
        $thread = $creator->getThread();

        $setOptions = $this->filter('_xfSet', 'array-bool');
        if ($thread->canAddQuestionStatus()) {
            if (isset($setOptions['th_is_qa_qaforum'])) {
                $creator->setQaState($this->filter('th_is_qa_qaforum', 'bool'));
            } else {
                $questionPrefix = intval(\XF::options()->th_qaPrefix_qaForums);
                $answeredPrefix = intval(\XF::options()->th_answeredPrefix_qaForums);

                if ($questionPrefix && $thread->prefix_id === $questionPrefix) {
                    $creator->setQaState(true);
                } elseif ($answeredPrefix && $thread->prefix_id === $answeredPrefix) {
                    $creator->setQaState(true);
                }
            }
        }

        return $creator;
    }

    /**
     * @param \XF\Entity\Forum $forum
     * @param \XF\Finder\Thread $threadFinder
     * @param array $filters
     */
    protected function applyForumFilters(\XF\Entity\Forum $forum, \XF\Finder\Thread $threadFinder, array $filters)
    {
        if (!empty($filters['thqa_status'])) {
            switch ($filters['thqa_status']) {
                case 'question':
                    $threadFinder->where('th_is_qa_qaforum', '=', '1');
                    break;

                case 'normal':
                    $threadFinder->where('th_is_qa_qaforum', '=', '0');
                    break;
            }
        }

        if (!empty($filters['thqa_answer_status'])) {
            switch ($filters['thqa_answer_status']) {
                case 'answered':
                    $threadFinder->where('th_answered_qaforum', '=', '1');
                    break;

                case 'unanswered':
                    $threadFinder->where('th_answered_qaforum', '=', '0');
                    break;
            }
        }

        if (!empty($filters['thqa_reply_state'])) {
            switch ($filters['thqa_reply_state']) {
                case 'has_reply':
                    $threadFinder->where('reply_count', '>=', '1');
                    break;

                case 'no_reply':
                    $threadFinder->where('reply_count', '=', '0');
                    break;
            }
        }

        parent::applyForumFilters($forum, $threadFinder, $filters);
    }

    /**
     * @param \XF\Entity\Forum $forum
     * @return array
     */
    protected function getForumFilterInput(\XF\Entity\Forum $forum)
    {
        $filters = parent::getForumFilterInput($forum);

        $qaFilters = $this->filter([
            'thqa_status' => 'str',
            'thqa_answer_status' => 'str',
            'thqa_reply_state' => 'str',
        ]);

        return array_merge($filters, $qaFilters);
    }
}
