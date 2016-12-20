<?php
namespace Biz\File\Event;

use Topxia\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\File\Service\UploadFileService;
use Codeages\Biz\Framework\Event\Event;
use Topxia\Service\Common\ServiceKernel;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UploadFileEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'question.create'           => 'onQuestionCreate',
            'question.update'           => 'onQuestionUpdate',
            'question.delete'           => 'onQuestionDelete',

            'course.delete'             => 'onCourseDelete',
            //'course.lesson.create' => 'onCourseLessonCreate',
            'course.lesson.delete'      => 'onCourseLessonDelete',
            'course.material.create'    => 'onMaterialCreate',
            'course.material.update'    => 'onMaterialUpdate',
            'course.material.delete'    => 'onMaterialDelete',

            'open.course.lesson.delete' => 'onOpenCourseLessonDelete',
            'open.course.delete'        => 'onOpenCourseDelete',

            'article.delete'            => 'onArticleDelete',
            'group.thread.post.delete'  => 'onGroupThreadPostDelete',
            'group.thread.delete'       => 'onGroupThreadDelete',
            'course.thread.delete'      => 'onCourseThreadDelete',
            'course.thread.post.delete' => 'onCourseThreadPostDelete',
            'thread.delete'             => 'onThreadDelete',
            'thread.post.delete'        => 'onThreadPostDelete'
        );
    }

    public function onQuestionCreate(Event $event)
    {
        $question = $event->getSubject();

        if (!$event->hasArgument('argument')) {
            return;
        }
        $argument = $event->getArgument('argument');

        $attachment = $argument['attachment'];

        $this->getUploadFileService()->createUseFiles($attachment['stem']['fileIds'], $question['id'], $attachment['stem']['targetType'], $attachment['stem']['type']);
        $this->getUploadFileService()->createUseFiles($attachment['analysis']['fileIds'], $question['id'], $attachment['analysis']['targetType'], $attachment['analysis']['type']);
    }

    public function onQuestionUpdate(Event $event)
    {
        $question = $event->getSubject();
        if (!$event->hasArgument('argument')) {
            return;
        }
        $argument = $event->getArgument('argument');

        if (empty($argument['fields']['attachment'])) {
            return;
        }

        $attachment = $argument['fields']['attachment'];

        $this->getUploadFileService()->createUseFiles($attachment['stem']['fileIds'], $question['id'], $attachment['stem']['targetType'], $attachment['stem']['type']);
        $this->getUploadFileService()->createUseFiles($attachment['analysis']['fileIds'], $question['id'], $attachment['analysis']['targetType'], $attachment['analysis']['type']);
    }

    public function onQuestionDelete(Event $event)
    {
        $question = $event->getSubject();

        $this->deleteAttachment('question.stem,question.analysis', $question['id']);
    }

    protected function deleteAttachment($targetType, $targetId)
    {
        $conditions = array('targetId' => $targetId, 'type' => 'attachment');
        if (strpos($targetType, ',') === false) {
            $conditions['targetType'] = $targetType;
        } else {
            $conditions['targetTypes'] = explode(',', $targetType);
        }

        $attachments = $this->getUploadFileService()->searchUseFiles($conditions);

        if (!$attachments) {
            return true;
        }

        foreach ($attachments as $attachment) {
            $this->getUploadFileService()->deleteUseFile($attachment['id']);
        }
    }

    public function onArticleDelete(Event $event)
    {
        $article = $event->getSubject();
        $this->deleteAttachment('article', $article['id']);
    }

    public function onGroupThreadPostDelete(Event $event)
    {
        $threadPost = $event->getSubject();
        $this->deleteAttachment('group.thread.post', $threadPost['id']);
    }

    public function onGroupThreadDelete(Event $event)
    {
        $thread = $event->getSubject();
        $this->deleteAttachment('group.thread', $thread['id']);
    }

    public function onCourseThreadDelete(Event $event)
    {
        $thread = $event->getSubject();
        $this->deleteAttachment('course.thread', $thread['id']);
    }

    public function onCourseThreadPostDelete(Event $event)
    {
        $threadPost = $event->getSubject();
        $this->deleteAttachment('course.thread.post', $threadPost['id']);
    }

    public function onThreadDelete(Event $event)
    {
        $thread = $event->getSubject();
        if (!empty($thread['targetType'])) {
            $this->deleteAttachment($thread['targetType'].'.thread', $thread['id']);
        }
    }

    public function onThreadPostDelete(Event $event)
    {
        $threadPost = $event->getSubject();
        if (!empty($threadPost['targetType'])) {
            $this->deleteAttachment($threadPost['targetType'].'.thread.post', $threadPost['id']);
        }
    }

    public function onCourseDelete(Event $event)
    {
        $course = $event->getSubject();

        /**
         * @TODO 教学计划删除时需要使文件使用数减少
         */
        $lessons = $this->getCourseService()->getCourse($course['id']);

        if (!empty($lessons)) {
            $fileIds = ArrayToolkit::column($lessons, "mediaId");

            if (!empty($fileIds)) {
                foreach ($fileIds as $fileId) {
                    $this->getUploadFileService()->waveUsedCount($fileId, -1);
                }
            }
        }
    }

    public function onCourseLessonCreate(Event $event)
    {
        $context = $event->getSubject();
        $lesson  = $context['lesson'];

        if (in_array($lesson['type'], array('video', 'audio', 'ppt', 'document', 'flash'))) {
            $this->getUploadFileService()->waveUsedCount($lesson['mediaId'], 1);
        }
    }

    public function onCourseLessonDelete(Event $event)
    {
        $context = $event->getSubject();
        $lesson  = $context['lesson'];

        if (!empty($lesson['mediaId'])) {
            $this->getUploadFileService()->waveUsedCount($lesson['mediaId'], -1);
        }
    }

    public function onMaterialCreate(Event $event)
    {
        $context  = $event->getSubject();
        $material = $context['material'];

        if (!empty($material['fileId'])) {
            $this->getUploadFileService()->waveUsedCount($material['fileId'], 1);
        }
    }

    public function onMaterialUpdate(Event $event)
    {
        $context        = $event->getSubject();
        $argument       = $context['argument'];
        $material       = $context['material'];
        $sourceMaterial = $context['sourceMaterial'];

        if (!$material['lessonId'] && $sourceMaterial['lessonId']) {
            $this->getUploadFileService()->waveUsedCount($material['fileId'], -1);
        } elseif ($material['fileId'] != $argument['fileId'] && $argument['fileId']) {
            $this->getUploadFileService()->waveUsedCount($material['fileId'], 1);
            $this->getUploadFileService()->waveUsedCount($argument['fileId'], -1);
        } elseif (!$sourceMaterial['lessonId'] && $material['lessonId']) {
            $this->getUploadFileService()->waveUsedCount($material['fileId'], 1);
        }
    }

    public function onMaterialDelete(Event $event)
    {
        $material = $event->getSubject();

        $file = $this->getUploadFileService()->getFile($material['fileId']);

        if (!$file) {
            return;
        }

        $this->getUploadFileService()->waveUsedCount($file['id'], -1);

        if (!$this->getUploadFileService()->canManageFile($file['id'])) {
            return;
        }

        if ($file['targetId'] == $material['courseId']) {
            $this->getUploadFileService()->update($material['fileId'], array('targetId' => 0));
        }
    }

    public function onOpenCourseLessonDelete(Event $event)
    {
        $context = $event->getSubject();
        $lesson  = $context['lesson'];

        if (!empty($lesson['mediaId'])) {
            $this->getUploadFileService()->waveUsedCount($lesson['mediaId'], -1);
        }
    }

    public function onOpenCourseDelete(Event $event)
    {
        $course = $event->getSubject();

        $lessons = $this->getOpenCourseService()->findLessonsByCourseId($course['id']);

        if (!empty($lessons)) {
            $fileIds = ArrayToolkit::column($lessons, "mediaId");

            if (!empty($fileIds)) {
                foreach ($fileIds as $fileId) {
                    $this->getUploadFileService()->waveUsedCount($fileId, -1);
                }
            }
        }
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getOpenCourseService()
    {
        return ServiceKernel::instance()->createService('OpenCourse:OpenCourseService');
    }
}
