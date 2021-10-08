<?php

namespace ThemeHouse\QAForums\XF\Search\Data;

use XF\Search\MetadataStructure;

/**
 * Class Post
 * @package ThemeHouse\QAForums\XF\Search\Data
 */
class Post extends XFCP_Post
{
    /**
     * @param MetadataStructure $structure
     */
    public function setupMetadataStructure(MetadataStructure $structure)
    {
        parent::setupMetadataStructure($structure);
        $structure->addField('thbestanswer', MetadataStructure::BOOL);
    }

    /**
     * @param \XF\Entity\Post $entity
     * @return array
     */
    protected function getMetaData(\XF\Entity\Post $entity)
    {
        $metadata = parent::getMetaData($entity);

        /** @var \ThemeHouse\QAForums\XF\Entity\Post $entity */
        if ($entity->th_best_answer_qaforum) {
            $metadata = [
                'thbestanswer' => true
            ];
        }

        return $metadata;
    }
}
