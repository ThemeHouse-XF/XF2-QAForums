<?php

namespace ThemeHouse\QAForums\XF\ControllerPlugin;

use XF\Entity\Post;

/**
 * Class Thread
 * @package ThemeHouse\QAForums\XF\ControllerPlugin
 */
class Thread extends XFCP_Thread
{
    /**
     * @param Post $post
     * @return string
     */
    public function getPostLink(Post $post)
    {
        /** @var \ThemeHouse\QAForums\XF\Entity\Thread $thread */
        $thread = $post->Thread;
        if (!$thread) {
            throw new \LogicException("Post has no thread");
        }

        $options = \XF::options();

        if ($thread->th_is_qa_qaforum && $options->th_defaultThreadView_qaForums === 'votes') {
            $page = floor($post->position / $this->options()->messagesPerPage) + 1;
            return $this->buildLink('threads', $thread,
                    ['page' => $page, 'view' => 'votes']) . '#post-' . $post->post_id;
        }

        return parent::getPostLink($post);
    }
}