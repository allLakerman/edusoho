<?php


namespace AppBundle\Controller\Classroom;


use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;

class ClassroomController extends BaseController
{

    public function myClassroomAction()
    {
        $user       = $this->getUser();
        $progresses = array();

        $members = $this->getClassroomService()->searchMembers(array(
            'roles'  => array('student', 'auditor'),
            'userId' => $user->id
        ), array('createdTime' => 'desc'), 0, PHP_INT_MAX);

        $members = ArrayToolkit::index($members, 'classroomId');


        $classroomIds = ArrayToolkit::column($members, 'classroomId');

        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        foreach ($classrooms as $key => $classroom) {
            $courses      = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
            $coursesCount = count($courses);

            $classrooms[$key]['coursesCount'] = $coursesCount;

            $time = time() - $members[$classroom['id']]['createdTime'];
            $day  = intval($time / (3600 * 24));

            $classrooms[$key]['day'] = $day;

            $progresses[$classroom['id']] = $this->calculateUserLearnProgress($classroom, $user->id);
        }

        return $this->render("my-classroom/classroom.html.twig", array(
            'classrooms' => $classrooms,
            'members'    => $members,
            'progresses' => $progresses
        ));
    }


    private function calculateUserLearnProgress($classroom, $userId)
    {
        $courses            = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
        $courseIds          = ArrayToolkit::column($courses, 'id');
        $findLearnedCourses = array();

        foreach ($courseIds as $key => $value) {
            $learnedCourses = $this->getCourseService()->findLearnedCoursesByCourseIdAndUserId($value, $userId);

            if (!empty($learnedCourses)) {
                $findLearnedCourses[] = $learnedCourses;
            }
        }

        $learnedCoursesCount = count($findLearnedCourses);
        $coursesCount        = count($courses);

        if ($coursesCount == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($learnedCoursesCount / $coursesCount * 100).'%';

        return array(
            'percent' => $percent,
            'number'  => $learnedCoursesCount,
            'total'   => $coursesCount
        );
    }


    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }

}