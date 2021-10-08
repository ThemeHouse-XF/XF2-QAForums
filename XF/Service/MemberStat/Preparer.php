<?php

namespace ThemeHouse\QAForums\XF\Service\MemberStat;

/**
 * Class Preparer
 * @package ThemeHouse\QAForums\XF\Service\MemberStat
 */
class Preparer extends XFCP_Preparer
{
    /**
     * @param $order
     * @param array $cacheResults
     * @return array
     */
    protected function prepareCacheResults($order, array $cacheResults)
    {
        switch ($order) {
            case 'th_best_answers_qaforum':
            case 'th_points_qaforum':
            case 'th_up_votes_qaforum':
                return array_map(function ($value) {
                    return \XF::language()->numberFormat($value);
                }, $cacheResults);
        }

        return parent::prepareCacheResults($order, $cacheResults);
    }
}
