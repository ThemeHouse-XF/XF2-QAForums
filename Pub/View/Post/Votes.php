<?php

namespace ThemeHouse\QAForums\Pub\View\Post;

use XF\Mvc\View;

/**
 * Class Votes
 * @package ThemeHouse\QAForums\Pub\View\Post
 */
class Votes extends View
{
    /**
     *
     */
    public function renderHtml()
    {
        $this->response->header('X-Robots-Tag', 'none');
    }
}