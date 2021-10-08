<?php

namespace ThemeHouse\QAForums\XF\Repository;

/**
 * Class Trophy
 * @package ThemeHouse\QAForums\XF\Repository
 */
class Trophy extends XFCP_Trophy
{
    /**
     * @return mixed
     */
    protected function getSupportedTrophyProgressRules()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $supportedTrophyProgressRules = parent::getSupportedTrophyProgressRules();

        $supportedTrophyProgressRules['th_best_answers_qaforums'] = [
            'valueKey' => 'th_best_answers_qaforum',
            'statsPhraseTitle' => 'thqaforums_most_answers',
        ];

        return $supportedTrophyProgressRules;
    }
}
