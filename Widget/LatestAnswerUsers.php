<?php

namespace ThemeHouse\QAForums\Widget;

use XF\Widget\AbstractWidget;

/**
 * Class LatestAnswerUsers
 * @package ThemeHouse\QAForums\Widget
 */
class LatestAnswerUsers extends AbstractWidget
{
    /**
     * @var array
     */
    protected $defaultOptions = [
        'limit' => 5,
        'node_ids' => '',
        'latest' => 'week'
    ];

    /**
     * @return \XF\Widget\WidgetRenderer
     */
    public function render()
    {
        if ($this->getFetchTimestamp()) {
            $finder = \XF::finder('XF:Post');
            $finder->where('th_best_answer_qaforum', '=', 1)
                ->where('post_date', '>', $this->getFetchTimestamp())
                ->limit($this->options['limit'])
                ->with('User', true);

            $list = $finder->fetch();
            $data = [];

            $visitor = \XF::visitor();
            foreach ($list as $post) {
                /** @var \XF\Entity\Post $post */
                if (!$post->canView() || $visitor->isIgnoring($post->user_id)) {
                    continue;
                }

                $data[$post->user_id]['user'] = $post->User;
                $data[$post->user_id]['count'] += 1;
            }

            usort($data, function ($a, $b) {
                return $a['count'] > $b['count'];
            });
        } else {
            $list = \XF::finder('XF:User')
                ->where('th_best_answers_qaforum', '>', 0)
                ->order('th_best_answers_qaforum', 'DESC')
                ->limit($this->options['limit'] * 2)
                ->fetch();

            $data = [];
            foreach ($list as $userId => $entry) {
                $data[$userId] = [
                    'user' => $entry,
                    'count' => $entry->th_best_answers_qaforum
                ];
            }
        }

        $data = array_slice($data, 0, min($this->options['limit'], count($data)));

        $viewParams = [
            'data' => $data
        ];

        return $this->renderer('thqaforums_widget_latest_answerers', $viewParams);
    }

    /**
     * @return int
     */
    public function getFetchTimestamp()
    {
        $timestamp = \XF::$time;

        switch ($this->options['latest']) {
            case 'all-time':
                $timestamp = 0;
                break;

            case 'year':
                $timestamp -= 31622400;
                break;

            case 'day':
                $timestamp -= 86400;
                break;

            case 'hour':
                $timestamp -= 3600;
                break;

            default:
            case 'week':
                $timestamp -= 604800;
                break;
        }

        return $timestamp;
    }
}
