<?php

namespace ThemeHouse\QAForums\Import\Importer;

use ThemeHouse\QAForums\XF\Entity\Post;
use ThemeHouse\QAForums\XF\Entity\Thread;
use XF\Import\StepState;

/**
 * Class NanocodeBestAnswer
 * @package ThemeHouse\QAForums\Import\Importer
 */
class NanocodeBestAnswer extends AbstractQAForum
{
    /**
     * @return array
     */
    public static function getListInfo()
    {
        return [
            'target' => '[TH] Question & Answer Forums',
            'source' => '[n] Best Answer / Q&A System',
        ];
    }

    /**
     * @return array
     */
    public function getSteps()
    {
        return [
            'votes' => [
                'title' => 'Votes',
            ],
        ];
    }

    /**
     * @return int
     */
    public function getStepEndVotes()
    {
        return $this->db()->fetchOne('SELECT MAX(vote_id) FROM ba_votes') ?: 0;
    }

    /**
     * @param StepState $state
     * @param array $stepConfig
     * @param $maxTime
     * @return $this|StepState
     * @throws \XF\PrintableException
     */
    public function stepVotes(StepState $state, array $stepConfig, $maxTime)
    {
        $limit = 100;

        $votes = $this->db()->fetchAll('
            SELECT vote.*, thread.bestanswer
            FROM ba_votes AS vote
            LEFT JOIN xf_post AS post ON post.post_id = vote.post_id
            LEFT JOIN xf_thread AS thread ON thread.thread_id = vote.thread_id
            WHERE vote.vote_id > ?
              AND vote.vote_id <= ?
            LIMIT ' . $limit, [
            $state->startAfter,
            $state->end,
        ]);
        if (!$votes) {
            return $state->complete();
        }

        foreach ($votes as $vote) {
            $state->startAfter = $vote['vote_id'];

            /** @var Post $post */
            $post = $this->app->finder('XF:Post')
                ->with('Thread')
                ->with('Thread.Forum')
                ->where('post_id', '=', $vote['post_id'])
                ->fetchOne();

            /** @var Thread $thread */
            $thread = $post->Thread;

            $existingVote = $this->app->finder('ThemeHouse\QAForums:Vote')->where([
                'post_id' => $post->post_id,
                'user_id' => $vote['vote_user_id'],
            ])->fetch()->count();

            if ($existingVote) {
                continue;
            }

            if (!$thread->th_is_qa_qaforum) {
                $thread->th_is_qa_qaforum = true;
            }
            if (!$thread->th_answered_qaforum && $vote['bestanswer']) {
                $thread->th_answered_qaforum = true;
            }
            $thread->save();

            if ($post->post_id === $vote['bestanswer'] && !$post->th_best_answer_qaforum) {
                $post->th_best_answer_qaforum = 1;
                $post->save();
            }

            $newVote = $this->em()->create('ThemeHouse\QAForums:Vote');
            $newVote->bulkSet([
                'user_id' => $vote['vote_user_id'],
                'post_id' => $post->post_id,
                'vote_type' => 'up',
                'vote_date' => $vote['vote_date'],
            ]);
            $newVote->save();

            $state->imported++;
        }

        return $state;
    }
}
