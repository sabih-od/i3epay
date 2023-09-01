<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\NotificationCreateRequest;
use App\Http\Requests\NotificationUpdateRequest;
use App\Repositories\NotificationRepository;
use App\Helper\APIresponse;

/**
 * Class NotificationsController.
 *
 * @package namespace App\Http\Controllers;
 */
class NotificationsController extends Controller
{
    /**
     * @var NotificationRepository
     */
    protected $repository;


    /**
     * NotificationsController constructor.
     *
     * @param NotificationRepository $repository
     */
    public function __construct(NotificationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $this->repository->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));
        // $notifications = $this->repository->all();

        // if (request()->wantsJson()) {

        //     return response()->json([
        //         'data' => $notifications,
        //     ]);
        // }

        // return view('notifications.index', compact('notifications'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  NotificationCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function store(NotificationCreateRequest $request)
    {
        // try {
        //     $notification = $this->repository->create($request->all());

        //     $response = [
        //         'message' => 'Notification created.',
        //         'data'    => $notification->toArray(),
        //     ];

        //     if ($request->wantsJson()) {

        //         return response()->json($response);
        //     }

        //     return redirect()->back()->with('message', $response['message']);
        // } catch (ValidatorException $e) {
        //     if ($request->wantsJson()) {
        //         return response()->json([
        //             'error'   => true,
        //             'message' => $e->getMessageBag()
        //         ]);
        //     }

        //     return redirect()->back()->withErrors($e->getMessageBag())->withInput();
        // }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $notification = $this->repository->find($id);

        // if (request()->wantsJson()) {

        //     return response()->json([
        //         'data' => $notification,
        //     ]);
        // }

        // return view('notifications.show', compact('notification'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // $notification = $this->repository->find($id);

        // return view('notifications.edit', compact('notification'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  NotificationUpdateRequest $request
     * @param  string            $id
     *
     * @return Response
     *
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(NotificationUpdateRequest $request, $id)
    {
        // try {
        //     $notification = $this->repository->update($request->all(), $id);

        //     $response = [
        //         'message' => 'Notification updated.',
        //         'data'    => $notification->toArray(),
        //     ];

        //     if ($request->wantsJson()) {

        //         return response()->json($response);
        //     }

        //     return redirect()->back()->with('message', $response['message']);
        // } catch (ValidatorException $e) {

        //     if ($request->wantsJson()) {

        //         return response()->json([
        //             'error'   => true,
        //             'message' => $e->getMessageBag()
        //         ]);
        //     }

        //     return redirect()->back()->withErrors($e->getMessageBag())->withInput();
        // }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $deleted = $this->repository->delete($id);

        // if (request()->wantsJson()) {

        //     return response()->json([
        //         'message' => 'Notification deleted.',
        //         'deleted' => $deleted,
        //     ]);
        // }

        // return redirect()->back()->with('message', 'Notification deleted.');
    }

    /**
     * @OA\Get(
     * path="/api/unread-notifications",
     * summary="Get unread notifications",
     * security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(type="boolean")
     *             }
     *         )
     *     )
     * )
    */
    public function getUnreadNotifications()
    {
        try {
            //list all user notifications
            $data = $this->repository->findWhere([
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => auth()->user()->id ?? null,
                'read_at' => null
            ])->pluck('data')->map(function($data) {
                return json_decode($data);
            });
            
            // return response
            return APIresponse::success('', $data->toArray());
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/read-notifications",
     *     summary="Read notifications",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(type="boolean")
     *             }
     *         )
     *     )
     * )
     */
    public function readNotifications()
    {
        try {
            //list all user notifications
            $data = $this->repository->readNotifications();
            $message = 'Notification read successfully!';

            if(!$data) $message = 'Notification not found!';

            // return response
            return APIresponse::success($message, []);
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }
}
