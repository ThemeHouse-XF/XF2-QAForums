<?php

namespace ThemeHouse\QAForums\XF\Job;

use XF\Entity\Thread;

/**
 * Class ThreadAction
 * @package ThemeHouse\QAForums\XF\Job
 */
class ThreadAction extends XFCP_ThreadAction
{
    /**
     * @param Thread $thread
     */
    protected function applyInternalThreadChange(Thread $thread)
    {
        parent::applyInternalThreadChange($thread);

        if ($this->getActionValue('add_question_status')) {
            /** @var \ThemeHouse\QAForums\XF\Entity\Thread $thread */
            $thread->th_is_qa_qaforum = true;
        }
        if ($this->getActionValue('remove_question_status')) {
            $thread->th_is_qa_qaforum = false;
        }
    }
}
