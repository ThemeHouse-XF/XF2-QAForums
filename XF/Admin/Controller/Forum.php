<?php

namespace ThemeHouse\QAForums\XF\Admin\Controller;

use XF\Entity\AbstractNode;
use XF\Entity\Node;
use XF\Mvc\FormAction;

/**
 * Class Forum
 * @package ThemeHouse\QAForums\XF\Admin\Controller
 */
class Forum extends XFCP_Forum
{
    /**
     * @param FormAction $form
     * @param Node $node
     * @param AbstractNode $data
     */
    protected function saveTypeData(FormAction $form, Node $node, AbstractNode $data)
    {
        $input = $this->filter(['th_force_qa_qaforum' => 'bool']);

        $form->basicEntitySave($data, $input);

        parent::saveTypeData($form, $node, $data);
    }
}
