<?php

namespace ThemeHouse\QAForums\XF\Service\Thread;

use ThemeHouse\QAForums\XF\Entity\Thread;

/**
 * Class Editor
 * @package ThemeHouse\QAForums\XF\Service\Thread
 *
 * @property Thread thread
 */
class Editor extends XFCP_Editor
{
    /**
     * @param $qaState
     */
    public function setQaState($qaState)
    {
        $this->thread->th_is_qa_qaforum = $qaState;
    }
}
