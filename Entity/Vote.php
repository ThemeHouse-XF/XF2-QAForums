<?php

namespace ThemeHouse\QAForums\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;
use XF\Repository\UserAlert;

/**
 * Class Vote
 * @package ThemeHouse\QAForums\Entity
 *
 * COLUMNS
 * @property integer vote_id
 * @property integer post_id
 * @property integer user_id
 * @property string vote_type
 * @property integer vote_date
 *
 * RELATIONS
 * @property \ThemeHouse\QAForums\XF\Entity\User User
 * @property \ThemeHouse\QAForums\XF\Entity\Post Post
 */
class Vote extends Entity
{
    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_th_vote_qaforum';
        $structure->shortName = 'ThemeHouse\QAForums:Vote';
        $structure->primaryKey = 'vote_id';
        $structure->columns = [
            'vote_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'post_id' => ['type' => self::UINT],
            'user_id' => ['type' => self::UINT],
            'vote_type' => ['type' => self::STR],
            'vote_date' => ['type' => self::UINT, 'default' => \XF::$time],
        ];
        $structure->getters = [];
        $structure->relations = [
            'User' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'user_id',
            ],
            'Post' => [
                'entity' => 'XF:Post',
                'type' => self::TO_ONE,
                'conditions' => 'post_id',
            ],
        ];
        return $structure;
    }

    /**
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    protected function _postSave()
    {
        /** @var \XF\Entity\UserAlert $existingAlert */
        $existingAlert = \XF::finder('XF:UserAlert')
            ->where('content_type', '=', 'post')
            ->where('content_id', '=', $this->post_id)
            ->where('alerted_user_id', '=', $this->Post->user_id)
            ->where('user_id', '=', $this->user_id)
            ->where('action', '=', 'thqa_vote')
            ->fetchOne();

        if ($this->isInsert()) {
            if (!$existingAlert) {
                /** @var UserAlert $alertRepo */
                $alertRepo = \XF::repository('XF:UserAlert');
                if ($alertRepo->userReceivesAlert($this->Post->User, $this->user_id, 'post', 'thqa_vote')) {
                    $alertRepo->insertAlert($this->Post->user_id, $this->user_id, $this->User->username, 'post',
                        $this->Post->post_id, 'thqa_vote', [
                            'type' => $this->vote_type,
                            'thread_title' => $this->Post->Thread->title,
                            'thread_id' => $this->Post->thread_id,
                            'depends_on_addon_id' => 'ThemeHouse/QAForums'
                        ]);
                }
            }
        }

        if ($this->isChanged('vote_type')) {
            if ($this->vote_type === 'up') {
                $this->updateUpVoteCount(1);
                if (!$this->isInsert()) {
                    $this->updateDownVoteCount(-1);
                }
            } elseif ($this->vote_type === 'down') {
                $this->updateDownVoteCount(1);
                if (!$this->isInsert()) {
                    $this->updateUpVoteCount(-1);
                }
            }

            if ($existingAlert) {
                $data = $existingAlert->extra_data;
                $data['type'] = $this->vote_type;
                $existingAlert->extra_data = $data;
                $existingAlert->saveIfChanged();
            }
        }
    }

    /**
     * @param $amount
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    protected function updateUpVoteCount($amount)
    {
        $this->updateUserUpVoteCount($amount);
        $this->updatePostUpVoteCount($amount);
    }

    /**
     * @param $amount
     * @throws \XF\Db\Exception
     */
    protected function updateUserUpVoteCount($amount)
    {
        if ($this->user_id) {
            $this->db()->query("
                UPDATE xf_user
                SET th_up_votes_qaforum = GREATEST(0, CAST(th_up_votes_qaforum AS SIGNED) + ?),
                    th_points_qaforum = th_points_qaforum + ?
                WHERE user_id = ?
            ", [$amount, $amount, $this->Post->user_id]);
        }
    }

    /**
     * @param $amount
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    protected function updatePostUpVoteCount($amount)
    {
        if ($this->post_id) {
            $this->db()->query("
                UPDATE xf_post
                SET th_up_votes_qaforum = GREATEST(0, CAST(th_up_votes_qaforum AS SIGNED) + ?),
                    th_points_qaforum = th_points_qaforum + ?
                WHERE post_id = ?
            ", [$amount, $amount, $this->post_id]);
            if ($this->Post) {
                $this->Post->th_up_votes_qaforum += $amount;
                $this->Post->th_points_qaforum += $amount;
                if ($amount > 0) {
                    $this->Post->checkBestAnswerThreshold();
                }
            }
        }
    }

    /**
     * @param $amount
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    protected function updateDownVoteCount($amount)
    {
        $this->updateUserDownVoteCount($amount);
        $this->updatePostDownVoteCount($amount);
    }

    /**
     * @param $amount
     * @throws \XF\Db\Exception
     */
    protected function updateUserDownVoteCount($amount)
    {
        if ($this->user_id) {
            $this->db()->query("
                UPDATE xf_user
                SET th_down_votes_qaforum = GREATEST(0, CAST(th_down_votes_qaforum AS SIGNED) + ?),
                    th_points_qaforum = th_points_qaforum + ?
                WHERE user_id = ?
            ", [$amount, -$amount, $this->Post->user_id]);
        }
    }

    /**
     * @param $amount
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    protected function updatePostDownVoteCount($amount)
    {
        if ($this->post_id) {
            $this->db()->query("
                UPDATE xf_post
                SET th_down_votes_qaforum = GREATEST(0, CAST(th_down_votes_qaforum AS SIGNED) + ?),
                    th_points_qaforum = th_points_qaforum + ?
                WHERE post_id = ?
            ", [$amount, -$amount, $this->post_id]);
            if ($this->Post) {
                $this->Post->th_down_votes_qaforum += $amount;
                $this->Post->th_points_qaforum -= $amount;
                if ($amount < 0) {
                    $this->Post->checkBestAnswerThreshold();
                }
            }
        }
    }

    /**
     * @throws \XF\Db\Exception
     * @throws \XF\PrintableException
     */
    protected function _postDelete()
    {
        if ($this->vote_type === 'up') {
            $this->updateUpVoteCount(-1);
        } elseif ($this->vote_type === 'down') {
            $this->updateDownVoteCount(-1);
        }
    }
}