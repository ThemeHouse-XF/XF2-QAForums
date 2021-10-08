<?php

namespace ThemeHouse\QAForums\Listener\Admin;

use XF\Repository\Node;
use XF\Template\Templater;

/**
 * Class TemplaterMacroPreRender
 * @package ThemeHouse\QAForums\Listener\Admin
 */
class TemplaterMacroPreRender
{
    /**
     * @param Templater $templater
     * @param $type
     * @param $template
     * @param $name
     * @param array $arguments
     * @param array $globalVars
     */
    public static function helperCriteriaUserPanes(
        Templater $templater,
        &$type,
        &$template,
        &$name,
        array &$arguments,
        array &$globalVars
    ) {
        /** @var Node $nodeRepo */
        $nodeRepo = \XF::repository('XF:Node');
        $nodeTree = $nodeRepo->createNodeTree($nodeRepo->getFullNodeList(null, 'NodeType'));

        $arguments['data']['thqaforums_nodetree'] = $nodeTree;
    }
}
