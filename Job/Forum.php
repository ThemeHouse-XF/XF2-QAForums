<?php

namespace ThemeHouse\QAForums\Job;

use XF\Job\AbstractJob;

/**
 * Class Forum
 * @package ThemeHouse\QAForums\Job
 */
class Forum extends AbstractJob
{
    /**
     * @var array
     */
    protected $defaultData = [
        'start' => 0,
        'batch' => 100
    ];

    /**
     * @param int $maxRunTime
     * @return \XF\Job\JobResult
     * @throws \XF\Db\Exception
     */
    public function run($maxRunTime)
    {
        $startTime = microtime(true);

        $db = $this->app->db();
        $em = $this->app->em();

        $ids = $db->fetchAllColumn($db->limit(
            "
				SELECT node_id
				FROM xf_forum
				WHERE node_id > ?
				ORDER BY node_id
			",
            $this->data['batch']
        ), $this->data['start']);
        if (!$ids) {
            return $this->complete();
        }

        $done = 0;

        foreach ($ids as $id) {
            if (microtime(true) - $startTime >= $maxRunTime) {
                break;
            }

            $this->data['start'] = $id;

            /** @var \ThemeHouse\QAForums\XF\Entity\Forum $forum */
            $forum = $em->find('XF:Forum', $id);
            if (!$forum) {
                continue;
            }

            $db->beginTransaction();

            $forum->rebuildQAVoteCounts();

            $db->commit();

            $done++;
        }

        $this->data['batch'] = $this->calculateOptimalBatch($this->data['batch'], $done, $startTime, $maxRunTime, 1000);

        return $this->resume();
    }

    /**
     * @return string
     */
    public function getStatusMessage()
    {
        $actionPhrase = \XF::phrase('rebuilding');
        $typePhrase = \XF::phrase('forums');
        return sprintf('%s... %s (%s)', $actionPhrase, $typePhrase, $this->data['start']);
    }

    /**
     * @return bool
     */
    public function canCancel()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canTriggerByChoice()
    {
        return true;
    }
}
