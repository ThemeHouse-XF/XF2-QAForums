<?php

namespace ThemeHouse\QAForums\XF\Repository;

/**
 * Class Counters
 * @package ThemeHouse\QAForums\XF\Repository
 */
class Counters extends XFCP_Counters
{
    /**
     * @return array|bool
     */
    public function getForumStatisticsCacheData()
    {
        $cache = parent::getForumStatisticsCacheData();

        $cache += $this->getTHQAAnsweredTotals();
        $cache += $this->getTHQAUnansweredTotals();
        return $cache;
    }

    /**
     * @return array|bool
     */
    public function getTHQAAnsweredTotals()
    {
        return $this->db()->fetchRow("
			SELECT COUNT(*) AS thqaanswered
			FROM xf_thread
			WHERE th_is_qa_qaforum = 1
			  AND th_answered_qaforum = 1
		");
    }

    /**
     * @return array|bool
     */
    public function getTHQAUnansweredTotals()
    {
        return $this->db()->fetchRow("
			SELECT COUNT(*) AS thqaunanswered
			FROM xf_thread
			WHERE th_is_qa_qaforum = 1
			  AND th_answered_qaforum = 0
		");
    }
}