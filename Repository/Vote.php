<?php

namespace ThemeHouse\QAForums\Repository;

use XF\Mvc\Entity\Repository;

/**
 * Class Vote
 * @package ThemeHouse\QAForums\Repository
 */
class Vote extends Repository
{
    /**
     * @param $threadId
     * @throws \XF\Db\Exception
     */
    public function removeVotesFromThread($threadId)
    {
        $this->db()->update('xf_post', [
            'th_points_qaforum' => 0,
            'th_up_votes_qaforum' => 0,
            'th_down_votes_qaforum' => 0,
        ], 'thread_id = ?', $threadId);

        $this->db()->query("
            UPDATE xf_user AS user
            INNER JOIN (
                SELECT post.user_id, count(*) AS votes
                FROM xf_post AS post
                INNER JOIN xf_th_vote_qaforum AS vote ON post.post_id = vote.post_id
                WHERE post.thread_id = ? AND vote.vote_type = 'up'
                GROUP BY post.user_id
            ) AS up_votes ON up_votes.user_id = user.user_id
            SET user.th_up_votes_qaforum = GREATEST(0, IF(
                    up_votes.votes,
                    CAST(user.th_up_votes_qaforum AS signed) - up_votes.votes, user.th_up_votes_qaforum
                )),
                user.th_points_qaforum = CAST(user.th_up_votes_qaforum AS signed)
                    - CAST(user.th_down_votes_qaforum AS signed)
        ", $threadId);

        $this->db()->query("
            UPDATE xf_user AS user
            INNER JOIN (
                SELECT post.user_id, count(*) AS votes
                FROM xf_post AS post
                INNER JOIN xf_th_vote_qaforum AS vote ON post.post_id = vote.post_id
                WHERE post.thread_id = ? AND vote.vote_type = 'down'
                GROUP BY post.user_id
            ) AS down_votes ON down_votes.user_id = user.user_id
            SET user.th_down_votes_qaforum = GREATEST(0, IF(
                    down_votes.votes,
                    CAST(user.th_down_votes_qaforum AS signed) - down_votes.votes, user.th_down_votes_qaforum
                )),
                user.th_points_qaforum = CAST(user.th_up_votes_qaforum AS signed)
                    - CAST(user.th_down_votes_qaforum AS signed)
        ", $threadId);

        $this->db()->query("
            DELETE vote
            FROM xf_th_vote_qaforum AS vote
            INNER JOIN xf_post AS post ON post.post_id = vote.post_id
            WHERE post.thread_id = ?
        ", $threadId);
    }
}
