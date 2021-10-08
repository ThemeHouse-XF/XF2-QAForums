<?php

namespace ThemeHouse\QAForums\Stats;

use XF\Stats\AbstractHandler;

/**
 * Class QAForums
 * @package ThemeHouse\QAForums\Stats
 */
class QAForums extends AbstractHandler
{
    /**
     * @return array
     */
    public function getStatsTypes()
    {
        return [
            'thqaf_uq' => \XF::phrase('thqaforums_unanswered_questions'),
            'thqaf_aq' => \XF::phrase('thqaforums_answered_questions'),
            'thqaf_q' => \XF::phrase('thqaforums_questions')
        ];
    }

    /**
     * @param $start
     * @param $end
     * @return array
     */
    public function getData($start, $end)
    {
        return [
            'thqaf_uq' => $this->db()->fetchPairs(
                $this->getBasicDataQuery('xf_thread', 'post_date',
                    'th_is_qa_qaforum = 1 AND th_answered_qaforum = 0 AND discussion_state = "visible"'),
                [$start, $end]
            ),
            'thqaf_aq' => $this->db()->fetchPairs(
                $this->getBasicDataQuery('xf_thread', 'post_date',
                    'th_is_qa_qaforum = 1 AND th_answered_qaforum = 1 AND discussion_state = "visible"'),
                [$start, $end]
            ),
            'thqaf_q' => $this->db()->fetchPairs(
                $this->getBasicDataQuery('xf_thread', 'post_date',
                    'th_is_qa_qaforum = 1 AND discussion_state = "visible"'),
                [$start, $end]
            )
        ];
    }
}