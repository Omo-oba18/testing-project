<?php

namespace App\Http\Controllers;

use App\Http\Requests\RosterRequest;
use App\Http\Resources\RosterResource;
use App\Services\AuthService;
use App\Services\RosterService;
use Illuminate\Http\Request;

class RosterController extends Controller
{
    private $rosterService;
    // protected $authService;

    public function __construct(RosterService $rosterService, AuthService $authService)
    {
        parent::__construct($authService);
        $this->rosterService = $rosterService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        $date = $request->date ?? date('Y-m-d');
        $data = $this->rosterService->rostersByDate($date);

        return RosterResource::collection($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param App\Http\Requests\RosterRequest
     * @return \Illuminate\Http\Response
     */
    public function create(RosterRequest $request)
    {
        $user = $this->getUserAuth();

        $newRequest = $request->validated();
        $newRequest['creator_id'] = $user->id;

        $roster = $this->rosterService->createRoster($newRequest);
        $roster->refresh('status')->load('employee'); // refresh record to load default fields like status

        RosterResource::withoutWrapping();

        return response()->json(new RosterResource($roster));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RosterRequest $request, $id)
    {
        $user = $this->getUserAuth();

        $roster = $this->rosterService->updateRoster($request->validated(), $id);

        RosterResource::withoutWrapping();

        return response()->json(new RosterResource($roster));
    }

    public function show($id)
    {
        $roster = $this->rosterService->findById($id);

        return response()->json(new RosterResource($roster));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id - id roster
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = $this->getUserAuth();

        $result = $this->rosterService->deleteRoster($id);
        if ($result) {
            return response()->json(['message' => __('roster.delete_success')]);
        }

        return response()->json(['message' => __('roster.delete_failed')], 422);
    }

    /**
     * Employee accept request add roster by employee
     *
     * @param  int  $id - id roster
     * @return responseJson
     */
    public function acceptRoster($id)
    {
        $user = $this->getUserAuth();
        $roster = $this->rosterService->employeeActionRoster($id, $user->id, config('constant.event.status.confirm'));

        return response()->json(['message' => __('roster.accept_roster_success')]);
    }

    /**
     * Employee decline request add roster by employee
     *
     * @param  int  $id - id roster
     * @return responseJson
     */
    public function declineRoster($id)
    {
        $user = $this->getUserAuth();
        $roster = $this->rosterService->employeeActionRoster($id, $user->id, config('constant.event.status.deny'));

        return response()->json(['message' => __('roster.decline_roster_success')]);
    }

    /**
     * Get status roster by week month
     *
     * @param  Illuminate\Http\Request  $request
     * @return ResponseJson
     *
     * @throws ValidateException
     */
    public function statusByWeekMonth(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after:start_date',
        ]);
        $rosters = $this->rosterService->getStatusRoster($request->start_date, $request->end_date);

        return $rosters;
    }
}
