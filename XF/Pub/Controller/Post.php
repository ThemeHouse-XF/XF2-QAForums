<?php

namespace ThemeHouse\QAForums\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

/**
 * Class Post
 * @package ThemeHouse\QAForums\XF\Pub\Controller
 */
class Post extends XFCP_Post
{
    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Redirect
     * @throws \XF\Mvc\Reply\Exception
     * @throws \XF\PrintableException
     */
    public function actionBestAnswer(ParameterBag $params)
    {
        $this->assertValidCsrfToken();

        /** @var \ThemeHouse\QAForums\XF\Entity\Post $post */
        $post = $this->assertViewablePost($params['post_id']);

        /** @var \ThemeHouse\QAForums\XF\Entity\Thread $thread */
        $thread = $post->Thread;

        if (!$thread->th_is_qa_qaforum) {
            return $this->notFound();
        }

        if (!$post->canMarkAsBestAnswer($error)) {
            return $this->noPermission($error);
        }

        if ($post->th_best_answer_qaforum) {
            $post->th_best_answer_qaforum = false;
            $post->th_best_answer_award_user_id_qaforum = 0;
        } elseif ($thread->th_answered_qaforum) {
            $thread->removeBestAnswer();
            $post->th_best_answer_qaforum = true;
            $post->th_best_answer_award_user_id_qaforum = \XF::visitor()->user_id;
        } else {
            $post->th_best_answer_qaforum = true;
            $post->th_best_answer_award_user_id_qaforum = \XF::visitor()->user_id;
        }
        $post->save();

        return $this->redirect($this->buildLink('posts', $post));
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     * @throws \XF\PrintableException
     */
    public function actionUpvoteAnswer(ParameterBag $params)
    {
        return $this->voteAnswerProcess($params['post_id'], 'up');
    }

    /**
     * @param $postId
     * @param string $voteType
     * @return \XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     * @throws \XF\PrintableException
     */
    protected function voteAnswerProcess($postId, $voteType = 'up')
    {
        $this->assertValidCsrfToken();

        $visitor = \XF::visitor();
        // This should never happen, so no need for a phrase.
        if (!in_array($voteType, ['up', 'down'])) {
            throw $this->errorException('Invalid vote type', 400);
        }

        /** @var \ThemeHouse\QAForums\XF\Entity\Post $post */
        $post = $this->assertViewablePost($postId);

        $thread = $post->Thread;
        if (!$thread->th_is_qa_qaforum) {
            return $this->notFound();
        }

        if (!$post->canVoteAnswer()) {
            return $this->noPermission();
        }

        $isBestAnswer = $post->th_best_answer_qaforum;

        if ($post->CurrentVote) {
            $vote = $post->CurrentVote;

            if ($vote->vote_type === $voteType) {
                $vote->delete();
            } else {
                $vote->vote_type = $voteType;
                $vote->save();
            }
        } else {
            $vote = $this->em()->create('ThemeHouse\QAForums:Vote');
            $vote->bulkSet([
                'user_id' => $visitor->user_id,
                'post_id' => $post->post_id,
                'vote_type' => $voteType,
            ]);
            $vote->save();
        }

        $bestAnswerChanged = $isBestAnswer !== $post->th_best_answer_qaforum;

        if ($this->filter('_xfWithData', 'bool') && !$bestAnswerChanged) {
            if ($vote->isDeleted()) {
                $post->hydrateRelation('CurrentVote', null);
            } else {
                $post->hydrateRelation('CurrentVote', $vote);
            }
            $viewParams = [
                'post' => $post,
            ];

            return $this->view('ThemeHouse\QAForums:Post\VoteAnswer', 'th_post_vote_count_qaForums', $viewParams);
        }

        return $this->redirect($this->buildLink('posts', $post));
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     * @throws \XF\PrintableException
     */
    public function actionDownvoteAnswer(ParameterBag $params)
    {
        return $this->voteAnswerProcess($params['post_id'], 'down');
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionQaVotes(ParameterBag $params)
    {
        /** @var \ThemeHouse\QAForums\XF\Entity\Post $post */
        $post = $this->assertViewablePost($params['post_id']);

        if (!$post->canViewVoteDetails()) {
            return $this->noPermission();
        }

        $breadcrumbs = $post->Thread->getBreadcrumbs();

        $page = $this->filterPage();
        $perPage = 50;

        $votes = $this->finder('ThemeHouse\QAForums:Vote')->where('post_id', '=',
            $post->post_id)->with('User')->order('vote_date', 'desc')
            ->limitByPage($page, $perPage, 1)->fetch();

        $hasNext = count($votes) > $perPage;
        $votes = $votes->slice(0, $perPage);

        $viewParams = [
            'post' => $post,

            'votes' => $votes,
            'hasNext' => $hasNext,
            'page' => $page,

            'breadcrumbs' => $breadcrumbs,
        ];
        return $this->view('ThemeHouse\QAForums:Post\Votes', 'th_vote_list_qaForums', $viewParams);
    }
}
