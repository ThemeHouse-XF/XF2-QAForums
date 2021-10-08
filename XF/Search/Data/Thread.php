<?php

namespace ThemeHouse\QAForums\XF\Search\Data;

use XF\Search\MetadataStructure;

/**
 * Class Thread
 * @package ThemeHouse\QAForums\XF\Search\Data
 */
class Thread extends XFCP_Thread
{
    /**
     * @param MetadataStructure $structure
     */
    public function setupMetadataStructure(MetadataStructure $structure)
    {
        parent::setupMetadataStructure($structure);
        $structure->addField('thqastatus', MetadataStructure::STR);
    }

    /**
     * @param \XF\Entity\Thread $entity
     * @return array
     */
    protected function getMetaData(\XF\Entity\Thread $entity)
    {
        $metadata = parent::getMetaData($entity);

        /** @var \ThemeHouse\QAForums\XF\Entity\Thread $entity */
        if ($entity->th_is_qa_qaforum) {
            $metadata['thqastatus'] = $entity->th_answered_qaforum ? 'answered' : 'unanswered';
        }

        return $metadata;
    }
}