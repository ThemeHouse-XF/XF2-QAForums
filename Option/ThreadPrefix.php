<?php

namespace ThemeHouse\QAForums\Option;

use XF\Entity\Option;
use XF\Option\AbstractOption;

/**
 * Class ThreadPrefix
 * @package ThemeHouse\QAForums\Option
 */
class ThreadPrefix extends AbstractOption
{
    /**
     * @param Option $option
     * @param array $htmlParams
     * @return string
     */
    public static function renderSelect(Option $option, array $htmlParams)
    {
        $data = self::getSelectData($option, $htmlParams);

        return self::getTemplater()->formSelectRow(
            $data['controlOptions'], $data['choices'], $data['rowOptions']
        );
    }

    /**
     * @param Option $option
     * @param array $htmlParams
     * @return array
     */
    protected static function getSelectData(Option $option, array $htmlParams)
    {
        /** @var \XF\Repository\ThreadPrefix $prefixRepo */
        $prefixRepo = \XF::repository('XF:ThreadPrefix');

        $prefixes = $prefixRepo->findPrefixesForList()->fetch();

        $choices = [];

        $choices[] = [
            'value' => 0,
            'label' => \XF::phrase('no_prefix'),
        ];
        foreach ($prefixes as $prefix) {
            /** @var \XF\Entity\ThreadPrefix $prefix */
            $choices[] = [
                'value' => $prefix->prefix_id,
                'label' => $prefix->getTitle(),
            ];
        }

        return [
            'choices' => $choices,
            'controlOptions' => self::getControlOptions($option, $htmlParams),
            'rowOptions' => self::getRowOptions($option, $htmlParams)
        ];
    }
}