<?php

namespace ThemeHouse\QAForums\Widget;

use XF\Http\Request;
use XF\Repository\Node;
use XF\Widget\AbstractWidget;

/**
 * Class UnansweredThread
 * @package ThemeHouse\QAForums\Widget
 */
class UnansweredThread extends AbstractWidget
{
    /**
     * @var array
     */
    protected $defaultOptions = [
        'limit' => 5,
        'node_ids' => '',
        'style' => 'simple',
        'show_expanded_title' => false
    ];

    /**
     * @return \XF\Widget\WidgetRenderer
     */
    public function render()
    {
        $visitor = \XF::visitor();

        $options = $this->options;
        $limit = $options['limit'];
        $style = $options['style'];
        $nodeIds = $options['node_ids'];

        $router = $this->app->router('public');

        /** @var \XF\Repository\Thread $threadRepo */
        $threadRepo = $this->repository('XF:Thread');

        $threadFinder = $threadRepo->findLatestThreads();
        $title = \XF::phrase('unanswered_threads');
        $link = $router->buildLink('whats-new/posts', null, ['skip' => 1]);

        $threadFinder
            ->with('Forum.Node.Permissions|' . $visitor->permission_combination_id)
            ->where('th_answered_qaforum', '=', 0)
            ->where('th_is_qa_qaforum', '=', 1)
            ->limit(max($limit * 4, 20));

        if ($nodeIds && !in_array(0, $nodeIds)) {
            $threadFinder->where('node_id', $nodeIds);
        }

        if ($style == 'full' || $style == 'expanded') {
            $threadFinder->with('full');
            if ($style == 'expanded') {
                $threadFinder->with('FirstPost');
            }
        }

        /** @var \XF\Entity\Thread $thread */
        foreach ($threads = $threadFinder->fetch() as $threadId => $thread) {
            if (!$thread->canView()
                || $visitor->isIgnoring($thread->user_id)
            ) {
                unset($threads[$threadId]);
            }

            if ($options['style'] != 'expanded' && $visitor->isIgnoring($thread->last_post_user_id)) {
                unset($threads[$threadId]);
            }
        }
        $threads = $threads->slice(0, $limit, true);

        $viewParams = [
            'title' => $this->getTitle() ?: $title,
            'link' => $link,
            'threads' => $threads,
            'style' => $options['style'],
            'showExpandedTitle' => $options['show_expanded_title']
        ];
        return $this->renderer('widget_new_threads', $viewParams);
    }

    /**
     * @param Request $request
     * @param array $options
     * @param null $error
     * @return bool
     */
    public function verifyOptions(Request $request, array &$options, &$error = null)
    {
        $options = $request->filter([
            'limit' => 'uint',
            'style' => 'str',
            'node_ids' => 'array-uint',
            'show_expanded_title' => 'bool'
        ]);

        if (in_array(0, $options['node_ids'])) {
            $options['node_ids'] = [0];
        }

        if ($options['limit'] < 1) {
            $options['limit'] = 1;
        }

        if ($options['style'] != 'expanded') {
            $options['show_expanded_title'] = false;
        }

        return true;
    }

    /**
     * @param $context
     * @return array
     */
    protected function getDefaultTemplateParams($context)
    {
        $params = parent::getDefaultTemplateParams($context);
        if ($context == 'options') {
            /** @var Node $nodeRepo */
            $nodeRepo = $this->app->repository('XF:Node');
            $params['nodeTree'] = $nodeRepo->createNodeTree($nodeRepo->getFullNodeList());
        }
        return $params;
    }
}
