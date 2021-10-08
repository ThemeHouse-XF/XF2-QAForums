<?php

namespace ThemeHouse\QAForums\XF\InlineMod;

use XF\Mvc\Entity\Entity;

/**
 * Class Thread
 * @package ThemeHouse\QAForums\XF\InlineMod
 */
class Thread extends XFCP_Thread
{
    /**
     * @return array|\XF\InlineMod\AbstractAction[]
     */
    public function getPossibleActions()
    {
        /** @var array $actions */
        $actions = parent::getPossibleActions();

        $actions['th_addqa_qaforums'] = $this->getSimpleActionHandler(
            \XF::phrase('thqaforums_add_question_status'),
            'canAddQuestionStatus',
            function (Entity $entity) {
                /** @var \ThemeHouse\QAForums\XF\Entity\Thread $entity */
                if ($entity->discussion_type != 'redirect' && !$entity->th_is_qa_qaforum) {
                    $entity->th_is_qa_qaforum = true;
                    $entity->save();
                }
            }
        );

        $actions['th_removeqa_qaforums'] = $this->getSimpleActionHandler(
            \XF::phrase('thqaforums_remove_question_status'),
            'canRemoveQuestionStatus',
            function (Entity $entity) {
                /** @var \ThemeHouse\QAForums\XF\Entity\Thread $entity */
                if ($entity->discussion_type != 'redirect' && $entity->th_is_qa_qaforum) {
                    $entity->th_is_qa_qaforum = false;
                    $entity->save();
                }
            }
        );

        return $actions;
    }
}
