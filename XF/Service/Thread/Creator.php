<?php

namespace ThemeHouse\QAForums\XF\Service\Thread;

use ThemeHouse\QAForums\XF\Entity\Thread;

/**
 * Class Creator
 * @package ThemeHouse\QAForums\XF\Service\Thread
 *
 * @property Thread thread
 */
class Creator extends XFCP_Creator
{
    /**
     * @param $qaState
     */
    public function setQaState($qaState)
    {
        $this->thread->th_is_qa_qaforum = $qaState;
    }
}
