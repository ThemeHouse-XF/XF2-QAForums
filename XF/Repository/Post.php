<?php

namespace ThemeHouse\QAForums\XF\Repository;

use XF\Entity\Thread;

/**
 * Class Post
 * @package ThemeHouse\QAForums\XF\Repository
 */
class Post extends XFCP_Post
{
    /**
     * @param Thread $thread
     * @param array $limits
     * @return \ThemeHouse\QAForums\XF\Finder\Post
     */
    public function findPostsForThreadVotesView(Thread $thread, $limits = [])
    {
        /** @var \ThemeHouse\QAForums\XF\Finder\Post $finder */
        $finder = $this->findPostsForThreadView($thread, $limits);

        $finder->where('post_id', '!=', $thread->first_post_id);

        $finder->resetOrder();
        $finder->orderByQAVotes();

        return $finder;
    }
}
