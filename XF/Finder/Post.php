<?php

namespace ThemeHouse\QAForums\XF\Finder;

/**
 * Class Post
 * @package ThemeHouse\QAForums\XF\Finder
 */
class Post extends XFCP_Post
{
    /**
     * @return $this
     */
    public function orderByQAVotes()
    {
        $this->order('th_best_answer_qaforum', 'desc')
            ->order('th_points_qaforum', 'desc')
            ->order('post_date', 'asc');

        return $this;
    }

    /**
     * @param $page
     * @param null $perPage
     * @return $this
     */
    public function onVotesPage($page, $perPage = null)
    {
        $page = max(1, intval($page));
        if ($perPage === null) {
            $perPage = $this->app()->options()->messagesPerPage;
        }
        $perPage = max(1, intval($perPage));

        $this->limitByPage($page, $perPage);

        return $this;
    }
}