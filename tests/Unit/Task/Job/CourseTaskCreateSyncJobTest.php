<?php

namespace Tests\Unit\Task\Job;

use Biz\Task\Job\CourseTaskCreateSyncJob;
use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use Tests\Unit\Task\Job\Tools\MockedText;

class CourseTaskUpdateSyncJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $job = new CourseTaskCreateSyncJob();
        ReflectionUtils::setProperty($job, 'biz', $this->biz);
        $job->args = array('taskId' => 110);

        $this->biz['activity_type.text'] = new MockedText($this->biz);
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'getTask',
                    'withParams' => array(110),
                    'returnValue' => array(
                        'id' => 110,
                        'courseId' => 3330,
                        'activityId' => 44443,
                        'createdUserId' => 123,
                        'seq' => 22,
                        'categoryId' => 123,
                        'title' => 'task title',
                        'isFree' => 1,
                        'isOptional' => 1,
                        'startTime' => 111111111,
                        'endTime' => 111111121,
                        'number' => 123,
                        'mode' => 'task',
                        'type' => 'taskType',
                        'mediaSource' => 'a.png',
                        'maxOnlineNum' => 333,
                        'status' => 'ok',
                        'length' => 3,
                    ),
                ),
                array(
                    'functionName' => 'getCourseTaskByCourseIdAndCopyId',
                    'withParams' => array(3331, 110),
                    'returnValue' => array(),
                ),
                array(
                    'functionName' => 'getCourseTaskByCourseIdAndCopyId',
                    'withParams' => array(3332, 110),
                    'returnValue' => array(
                        'id' => 110,
                        'courseId' => 3332,
                        'activityId' => 44445,
                    ),
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseDao',
            array(
                array(
                    'functionName' => 'findCoursesByParentIdAndLocked',
                    'withParams' => array(3330, 1),
                    'returnValue' => array(
                        array('id' => 3331, 'courseSetId' => 222),
                        array('id' => 3332),
                    ),
                ),
            )
        );

        $this->mockBiz(
            'Task:TaskDao',
            array(
                array(
                    'functionName' => 'batchCreate',
                    'withParams' => array(
                        array(
                            array(
                                'courseId' => 3331,
                                'fromCourseSetId' => 222,
                                'createdUserId' => 123,
                                'seq' => 22,
                                'categoryId' => null,
                                'activityId' => null,
                                'title' => 'task title',
                                'isFree' => 1,
                                'isOptional' => 1,
                                'startTime' => 111111111,
                                'endTime' => 111111121,
                                'number' => 123,
                                'mode' => 'task',
                                'type' => 'taskType',
                                'mediaSource' => 'a.png',
                                'copyId' => 110,
                                'maxOnlineNum' => 333,
                                'status' => 'ok',
                                'length' => 3,
                            ),
                        ),
                    ),
                ),
            )
        );

        $this->mockBiz(
            'Activity:ActivityDao',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array(44443),
                    'returnValue' => array(
                        'id' => 44443,
                        'copyId' => 0,
                        'mediaType' => 'text',
                        'title' => 'activity title',
                        'remark' => 'activity remark',
                        'content' => 'activity content',
                        'length' => 3,
                        'fromUserId' => 332,
                        'startTime' => 123123123,
                        'endTime' => 123123124,
                        'fromCourseId' => 3330,
                    ),
                ),
                array(
                    'functionName' => 'create',
                    'withParams' => array(
                        array(
                            'title' => 'activity title',
                            'remark' => 'activity remark',
                            'mediaType' => 'text',
                            'content' => 'activity content',
                            'length' => 3,
                            'fromCourseId' => 3331,
                            'fromCourseSetId' => 222,
                            'fromUserId' => 332,
                            'startTime' => 123123123,
                            'endTime' => 123123124,
                            'copyId' => 44443,
                        ),
                    ),
                ),
            )
        );

        $job->execute();

        $this->getTaskDao()->shouldHaveReceived('batchCreate')->times(1);
        $this->getActivityDao()->shouldHaveReceived('create')->times(1);
        $this->getActivityDao()->shouldHaveReceived('get')->times(1);
        $this->getCourseDao()->shouldHaveReceived('findCoursesByParentIdAndLocked')->times(1);
        $this->getTaskService()->shouldHaveReceived('getTask')->times(1);
        $this->getTaskService()->shouldHaveReceived('getCourseTaskByCourseIdAndCopyId')->times(2);

        $mockedText = $this->biz['activity_type.text'];
        $this->assertEquals(
            array(
                'id' => 44443,
                'copyId' => 0,
                'mediaType' => 'text',
                'title' => 'activity title',
                'remark' => 'activity remark',
                'content' => 'activity content',
                'length' => 3,
                'fromUserId' => 332,
                'startTime' => 123123123,
                'endTime' => 123123124,
                'fromCourseId' => 3330,
            ),
            $mockedText->getCopiedActivity()
        );
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->biz->dao('Task:TaskService');
    }

    /**
     * @return TaskDao
     */
    private function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }

    /**
     * @return CourseDao
     */
    private function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }
}
