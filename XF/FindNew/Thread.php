<?php

namespace ThemeHouse\QAForums\XF\FindNew;

use XF\Http\Request;

/**
 * Class Thread
 * @package ThemeHouse\QAForums\XF\FindNew
 */
class Thread extends XFCP_Thread
{
    /**
     * @param Request $request
     * @return array
     */
    public function getFiltersFromInput(Request $request)
    {
        $filters = parent::getFiltersFromInput($request);

        $questionStatus = $request->filter('thqa_status', 'str');
        if ($questionStatus) {
            $filters['thqa_status'] = $questionStatus;
        }

        $answerStatus = $request->filter('thqa_answer_status', 'str');
        if ($answerStatus) {
            $filters['thqa_answer_status'] = $answerStatus;
        }

        $replyStatus = $request->filter('thqa_reply_state', 'str');
        if ($replyStatus) {
            $filters['thqa_reply_state'] = $replyStatus;
        }

        return $filters;
    }

    /**
     * @param \XF\Finder\Thread $threadFinder
     * @param array $filters
     */
    protected function applyFilters(\XF\Finder\Thread $threadFinder, array $filters)
    {
        parent::applyFilters($threadFinder, $filters);

        if(!empty($filters['thqa_status'])) {
            $threadFinder->where('th_is_qa_qaforum', '=', $filters['thqa_status'] == 'question');
        }

        if(!empty($filters['thqa_answer_status'])) {
            $threadFinder
                ->where('th_is_qa_qaforum', '=', 1)
                ->where('th_answered_qaforum', '=', $filters['thqa_answer_status'] == 'answered');
        }

        if (!empty($filters['thqa_reply_state'])) {
            if ($filters['thqa_reply_state'] == 'no_reply') {
                $threadFinder->where('reply_count', '=', 0);
            }
            else {
                $threadFinder->where('reply_count', '>', 0);
            }
        }
    }
}
