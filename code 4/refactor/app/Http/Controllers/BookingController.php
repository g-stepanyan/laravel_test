<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository;
    protected $authUser;
    protected $data;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository, Request $request)
    {
        $this->repository = $bookingRepository;
        $this->authUser = $request->__autheticatedUser;
        $this->data = $request->all();
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($userId = $request->get('user_id')) {
            $response = $this->repository->getUsersJobs($userId);
        } elseif (
            $this->authUser->user_type == env('ADMIN_ROLE_ID') ||
            $this->authUser->user_type == env('SUPERADMIN_ROLE_ID')
        ) {
            $response = $this->repository->getAll($request);
        }

        return response($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);

        return response($job);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $response = $this->repository->store($this->authUser, $this->data);

        return response($response);
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $response = $this->repository->updateJob($id, array_except($this->data, ['_token', 'submit']), $this->authUser);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $adminSenderEmail = config('app.adminemail');

        $response = $this->repository->storeJobEmail($this->data);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if ($userId = $request->get('user_id')) {
            $response = $this->repository->getUsersJobsHistory($userId, $request);
            return response($response);
        }

        return null;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $response = $this->repository->acceptJob($this->data, $this->authUser);

        return response($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');

        $response = $this->repository->acceptJobWithId($data, $this->authUser);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $response = $this->repository->cancelJobAjax($this->data, $this->authUser);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $response = $this->repository->endJob($this->data);

        return response($response);
    }

    public function customerNotCall(Request $request)
    {

        $response = $this->repository->customerNotCall($this->data);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->getPotentialJobs($this->authUser);

        return response($response);
    }

    public function distanceFeed(Request $request)
    {

        if (isset($this->data['distance']) && $this->data['distance'] != "") {
            $distance = $this->data['distance'];
        } else {
            $distance = "";
        }
        if (isset($this->data['time']) && $this->data['time'] != "") {
            $time = $this->data['time'];
        } else {
            $time = "";
        }
        if (isset($this->data['jobid']) && $this->data['jobid'] != "") {
            $jobId = $this->data['jobid'];
        }

        if (isset($this->data['session_time']) && $this->data['session_time'] != "") {
            $session = $this->data['session_time'];
        } else {
            $session = "";
        }

        if ($this->data['flagged'] == 'true') {
            if ($this->data['admincomment'] == '') {
                return "Please, add comment";
            }
            $flagged = 'yes';
        } else {
            $flagged = 'no';
        }
        
        if ($this->data['manually_handled'] == 'true') {
            $manuallyHandled = 'yes';
        } else {
            $manuallyHandled = 'no';
        }

        if ($this->data['by_admin'] == 'true') {
            $byAdmin = 'yes';
        } else {
            $byAdmin = 'no';
        }

        if (isset($this->data['admincomment']) && $this->data['admincomment'] != "") {
            $adminComment = $this->data['admincomment'];
        } else {
            $adminComment = "";
        }
        if ($time || $distance) {
            $affectedRows = Distance::where('job_id', '=', $jobId)->update(array(
                'distance' => $distance, 'time' => $time));
        }

        if ($adminComment || $session || $flagged || $manuallyHandled || $byAdmin) {
            $affectedRows1 = Job::where('id', '=', $jobId)->update(
                array(
                'admin_comments' => $adminComment,
                'flagged' => $flagged,
                'session_time' => $session,
                'manually_handled' => $manuallyHandled,
                'by_admin' => $byAdmin
                )
            );
        }

        return response('Record updated!');
    }

    public function reopen(Request $request)
    {
        $response = $this->repository->reopen($this->data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $job = $this->repository->find($this->data['jobid']);
        $jobData = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, '*',$jobData);

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $job = $this->repository->find($this->data['jobid']);
        $jobData = $this->repository->jobToData($job);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }
}
