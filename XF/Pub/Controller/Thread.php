<?php

namespace ThemeHouse\QAForums\XF\Pub\Controller;

use ThemeHouse\QAForums\XF\Service\Thread\Editor;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

/**
 * Class Thread
 * @package ThemeHouse\QAForums\XF\Pub\Controller
 */
class Thread extends XFCP_Thread
{
    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Reroute|View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionIndex(ParameterBag $params)
    {
        /** @var \ThemeHouse\QAForums\XF\Entity\Thread $thread */
        $thread = $this->assertViewableThread($params['thread_id'], $this->getThreadViewExtraWith());

        if ($thread->th_is_qa_qaforum) {
            $options = \XF::options();
            $view = $this->filter('view', 'string', $options->th_defaultThreadView_qaForums);

            if ($view == 'votes') {
                return $this->actionThreadVotes($params);
            } else {
                $response = parent::actionIndex($params);

                if ($response instanceof View) {
                    $pageNavParams = $response->getParam('pageNavParams');
                    if (!$pageNavParams) {
                        $pageNavParams = [];
                    }
                    $pageNavParams['view'] = 'date';

                    $response->setParam('pageNavParams', $pageNavParams);
                    $response->setParam('th_view_qaForum', 'date');
                }

                return $response;
            }
        }

        return parent::actionIndex($params);
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionThreadVotes(ParameterBag $params)
    {
        $thread = $this->assertViewableThread($params['thread_id'], $this->getThreadViewExtraWith());

        if ($thread->discussion_type == 'redirect') {
            if (!$thread->Redirect) {
                return $this->noPermission();
            }

            return $this->redirectPermanently($this->request->convertToAbsoluteUri($thread->Redirect->target_url));
        }

        $threadRepo = $this->getThreadRepo();
        /** @var \ThemeHouse\QAForums\XF\Repository\Post $postRepo */
        $postRepo = $this->getPostRepo();

        $page = $params['page'];
        $perPage = $this->options()->messagesPerPage;

        $this->assertValidPage($page, $perPage, $thread->reply_count + 1, 'threads', $thread);
        $this->assertCanonicalUrl($this->buildLink('threads', $thread, ['page' => $page]));

        /** @noinspection PhpUndefinedMethodInspection */
        /** @var \XF\Mvc\Entity\Finder $postList */
        $postList = $postRepo->findPostsForThreadVotesView($thread)->onVotesPage($page, $perPage);
        $posts = $postList->fetch();

        /** @var \XF\Repository\Attachment $attachmentRepo */
        $attachmentRepo = $this->repository('XF:Attachment');
        $attachmentRepo->addAttachmentsToContent($posts, 'post');

        $lastPost = $thread->LastPost;
        if (!$lastPost) {
            if ($page > 1) {
                return $this->redirect($this->buildLink('threads', $thread));
            } else {
                // should never really happen
                return $this->error(\XF::phrase('something_went_wrong_please_try_again'));
            }
        }

        $firstPost = $thread->FirstPost;

        /** @var \XF\Entity\Post $post */

        $canInlineMod = false;
        foreach ($posts as $post) {
            if ($post->canUseInlineModeration()) {
                $canInlineMod = true;
                break;
            }
        }

        $firstUnread = null;
        foreach ($posts as $post) {
            if ($post->isUnread()) {
                $firstUnread = $post;
                break;
            }
        }

        $poll = ($thread->discussion_type == 'poll' ? $thread->Poll : null);

        $threadRepo->markThreadReadByVisitor($thread, $lastPost->post_date);
        $threadRepo->logThreadView($thread);

        $viewParams = [
            'thread' => $thread,
            'forum' => $thread->Forum,
            'posts' => $posts,
            'firstPost' => $firstPost,
            'lastPost' => $lastPost,
            'firstUnread' => $firstUnread,

            'poll' => $poll,

            'canInlineMod' => $canInlineMod,

            'page' => $page,
            'perPage' => $perPage,

            'pageNavParams' => [
                'view' => 'votes'
            ],

            'attachmentData' => $this->getReplyAttachmentData($thread),

            'pendingApproval' => $this->filter('pending_approval', 'bool'),

            'th_view_qaForum' => 'votes',
        ];
        return $this->view('XF:Thread\View', 'thread_view', $viewParams);
    }

    /**
     * @param \XF\Entity\Thread $thread
     * @param $lastDate
     * @return View
     */
    protected function getNewPostsReply(\XF\Entity\Thread $thread, $lastDate)
    {
        $response = parent::getNewPostsReply($thread, $lastDate);
        /** @var \ThemeHouse\QAForums\XF\Entity\Thread $thread */
        if ($thread->th_is_qa_qaforum) {
            $response->setParam('firstUnshownPost', false);
            $postsOrig = $response->getParam('posts');

            if ($postsOrig) {
                $posts = new ArrayCollection([
                    $postsOrig->last(),
                ]);
            } else {
                $posts = [];
            }
            $response->setParam('posts', $posts);
        }

        return $response;
    }

    /**
     * @param \XF\Entity\Thread $thread
     * @return Editor|\XF\Service\Thread\Editor
     */
    protected function setupThreadEdit(\XF\Entity\Thread $thread)
    {
        /** @var Editor $editor */
        $editor = parent::setupThreadEdit($thread);

        /** @var \ThemeHouse\QAForums\XF\Entity\Thread $thread */
        if ($thread->th_is_qa_qaforum) {
            $canRemoveQuestionStatus = $thread->canRemoveQuestionStatus();
            if ($canRemoveQuestionStatus) {
                $editor->setQaState($this->filter('th_is_qa_qaforum', 'bool'));
            }
        } else {
            $canAddQuestionStatus = $thread->canAddQuestionStatus();
            if ($canAddQuestionStatus) {
                $editor->setQaState($this->filter('th_is_qa_qaforum', 'bool'));
            }
        }

        return $editor;
    }
}
