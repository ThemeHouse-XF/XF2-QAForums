<?php

namespace ThemeHouse\QAForums\XF\Entity;

/**
 * Class Forum
 * @package ThemeHouse\QAForums\XF\Entity
 *
 * @property boolean th_force_qa_qaforum
 */
class Forum extends XFCP_Forum
{
    /**
     * @return \XF\Mvc\Entity\Entity
     */
    public function getNewThread()
    {
        $thread = parent::getNewThread();

        $questionPrefix = intval(\XF::options()->th_qaPrefix_qaForums);
        $answeredPrefix = intval(\XF::options()->th_answeredPrefix_qaForums);

        $prefixId = $thread->Forum->default_prefix_id;
        if ($this->th_force_qa_qaforum || ($prefixId && ($prefixId === $questionPrefix || $prefixId === $answeredPrefix))) {
            $thread->th_is_qa_qaforum = true;
        }

        return $thread;
    }

    /**
     * @throws \XF\Db\Exception
     */
    public function rebuildQAVoteCounts()
    {
        $db = $this->db();

        $db->beginTransaction();
        $db->delete('xf_th_qaforums_forum_user_best_answers', 'node_id = ?', $this->node_id);
        $db->query("
			INSERT INTO xf_th_qaforums_forum_user_best_answers (node_id, user_id, best_answers)
			SELECT node_id, post.user_id, COUNT(*)
            FROM xf_post AS post
            INNER JOIN xf_thread AS thread ON (post.thread_id = thread.thread_id)
			WHERE thread.node_id = ?
				AND message_state = 'visible'
                AND post.user_id > 0
                AND th_best_answer_qaforum = 1
			GROUP BY user_id
		", $this->node_id);
        $db->commit();
    }

    /**
     * @param null $error
     * @return bool
     */
    public function canPostQuestion(&$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return false;
        }

        if ($visitor->hasNodePermission($this->node_id, 'manageAnyThread')) {
            return true;
        }

        return $visitor->hasNodePermission($this->node_id, 'th_addQuestionOwnThread');
    }
}
