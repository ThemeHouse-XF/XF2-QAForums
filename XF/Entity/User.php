<?php

namespace ThemeHouse\QAForums\XF\Entity;

use XF\Mvc\Entity\ArrayCollection;

/**
 * Class User
 * @package ThemeHouse\QAForums\XF\Entity
 *
 * @property ArrayCollection|Post[] ForumUserBestAnswers
 * @property integer th_up_votes_qaforum
 * @property integer th_down_votes_qaforum
 * @property integer th_best_answers_qaforum
 * @property integer th_points_qaforum
 */
class User extends XFCP_User
{
    /**
     * @var null
     */
    protected $countTHQAForumsQuestions = null;

    /**
     * @return array|ArrayCollection
     */
    public function getForumUserBestAnswers()
    {
        return $this->ForumUserBestAnswers->pluckNamed('best_answers', 'node_id');
    }

    /**
     * @return int|null
     */
    public function countTHQAForumsQuestions()
    {
        if ($this->countTHQAForumsQuestions === null) {
            $this->countTHQAForumsQuestions = $this->finder('XF:Thread')->where('user_id',
                $this->user_id)->where('th_is_qa_qaforum', '1')->total();
        }

        return $this->countTHQAForumsQuestions;
    }

    /**
     * @param array $config
     * @throws \XF\PrintableException
     */
    public function rebuildQAVoteCounts(array $config = [])
    {
        $config = array_replace([
            'up_votes' => true,
            'down_votes' => true,
            'best_answers' => true,
        ], $config);

        if ($config['up_votes']) {
            $upVotes = $this->finder('ThemeHouse\QAForums:Vote')
                ->with('Post')
                ->where('Post.user_id', '=', $this->user_id)
                ->where('vote_type', '=', 'up')
                ->total();
            $this->th_up_votes_qaforum = $upVotes;
        }
        if ($config['down_votes']) {
            $downVotes = $this->finder('ThemeHouse\QAForums:Vote')
                ->with('Post')
                ->where('Post.user_id', '=', $this->user_id)
                ->where('vote_type', '=', 'down')
                ->total();
            $this->th_down_votes_qaforum = $downVotes;
        }
        if ($config['best_answers']) {
            $bestAnswers = $this->finder('XF:Post')
                ->with('Thread')
                ->where('user_id', '=', $this->user_id)
                ->where('th_best_answer_qaforum', '=', 1)
                ->total();
            $this->th_best_answers_qaforum = $bestAnswers;
        }

        $this->th_points_qaforum = $this->th_up_votes_qaforum - $this->th_down_votes_qaforum;
        $this->save();
    }
}
