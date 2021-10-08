<?php

namespace ThemeHouse\QAForums\XF\Searcher;

use XF\Mvc\Entity\Finder;

/**
 * Class Thread
 * @package ThemeHouse\QAForums\XF\Searcher
 */
class Thread extends XFCP_Thread
{
    /**
     * @return array
     */
    public function getFormDefaults()
    {
        $formDefaults = parent::getFormDefaults();

        $formDefaults['th_is_qa_qaforum'] = [0, 1];
        $formDefaults['thqaforum_question_answered'] = [0, 1];

        return $formDefaults;
    }

    /**
     * @param Finder $finder
     * @param $key
     * @param $value
     * @param $column
     * @param $format
     * @param $relation
     * @return bool
     */
    protected function applySpecialCriteriaValue(Finder $finder, $key, $value, $column, $format, $relation)
    {
        if ($key == 'thqaforum_question_answered') {
            if ($value === [0]) {
                $finder->where('th_answered_qaforum', $value);
            } else {
                $finder->whereOr(
                    [
                        'th_is_qa_qaforum' => 1,
                        'th_answered_qaforum' => $value,
                    ],
                    [
                        'th_is_qa_qaforum' => 0,
                    ]
                );
            }
            return true;
        }

        return parent::applySpecialCriteriaValue($finder, $key, $value, $column, $format, $relation);
    }
}
