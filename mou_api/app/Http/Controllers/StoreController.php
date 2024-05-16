<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Http\Resources\StoreResource;
use App\Services\StoreService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(protected StoreService $storeService)
    {

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return StoreResource::collection($this->storeService->list($request->search, $request->limit));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        return StoreResource::make($this->storeService->store($request->validated()));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, int $id)
    {
        return StoreResource::make($this->storeService->update($id, $request->validated()));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->storeService->delete($id);

        return response()->noContent();
    }
}
