<?php

namespace ThemeHouse\QAForums\Job;

use XF\Job\AbstractJob;

/**
 * Class User
 * @package ThemeHouse\QAForums\Job
 */
class User extends AbstractJob
{
    /**
     * @var array
     */
    protected $defaultData = [
        'start' => 0,
        'batch' => 100,
        'up_votes' => false,
        'down_votes' => false,
        'best_answers' => false,
    ];

    /**
     * @param int $maxRunTime
     * @return \XF\Job\JobResult
     * @throws \XF\PrintableException
     */
    public function run($maxRunTime)
    {
        $startTime = microtime(true);

        $db = $this->app->db();
        $em = $this->app->em();

        $ids = $db->fetchAllColumn($db->limit(
            "
				SELECT user_id
				FROM xf_user
				WHERE user_id > ?
				ORDER BY user_id
			",
            $this->data['batch']
        ), $this->data['start']);
        if (!$ids) {
            return $this->complete();
        }

        $options = $this->data;

        $done = 0;

        foreach ($ids as $id) {
            if (microtime(true) - $startTime >= $maxRunTime) {
                break;
            }

            $this->data['start'] = $id;

            /** @var \ThemeHouse\QAForums\XF\Entity\User $user */
            $user = $em->find('XF:User', $id);
            if (!$user) {
                continue;
            }

            $db->beginTransaction();

            $user->rebuildQAVoteCounts($options);

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
        $typePhrase = \XF::phrase('users');
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
