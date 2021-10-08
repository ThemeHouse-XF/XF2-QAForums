<?php

namespace ThemeHouse\QAForums\Job;

use XF\Job\AbstractRebuildJob;

/**
 * Class Post
 * @package ThemeHouse\QAForums\Job
 */
class Post extends AbstractRebuildJob
{
    /**
     * @var array
     */
    protected $defaultData = [
        'position_rebuild' => false
    ];

    /**
     * @param $start
     * @param $batch
     * @return array
     */
    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        return $db->fetchAllColumn($db->limit(
            "
				SELECT post_id
				FROM xf_post
				WHERE post_id > ?
				ORDER BY post_id
			",
            $batch
        ), $start);
    }

    /**
     * @param $id
     * @throws \XF\PrintableException
     */
    protected function rebuildById($id)
    {
        /** @var \ThemeHouse\QAForums\XF\Entity\Post $post */
        $post = $this->app->em()->find('XF:Post', $id);
        if (!$post) {
            return;
        }

        if ($post->rebuildQAVoteCounts()) {
            $post->save();
        }
    }

    /**
     * @return \XF\Phrase
     */
    protected function getStatusType()
    {
        return \XF::phrase('posts');
    }
}
