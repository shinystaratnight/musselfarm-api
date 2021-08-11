<?php

namespace App\Http\Controllers\Farm;

use App\Models\Farm;
use App\Models\Line;
use App\Http\Requests\Farm\FarmRequest;
use App\Http\Requests\Farm\UpdateFarmRequest;
use App\Http\Resources\Farm\FarmResource;
use App\Http\Controllers\Controller;
use App\Repositories\Farm\FarmRepositoryInterface as MarineFarm;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    private $farmRepo;

    public function __construct(MarineFarm $farming)
    {
        $this->farmRepo = $farming;
    }

    public function index()
    {
//        return $this->farmRepo->farms(auth()->user()->id);
    }

    public function show(Request $request, Farm $farm)
    {
        $this->authorize('show', [
            $farm,
            $request->input('account_id')
        ]);

        return new FarmResource($farm);
    }

    public function store(FarmRequest $request)
    {
        $attr = $request->validated();

        return $this->farmRepo->createFarm($attr);
    }

    public function update(UpdateFarmRequest $request, Farm $farm)
    {
        $this->authorize('update', [
            $farm,
            $request->input('account_id')
        ]);
        
        $farm->update($request->validated());

        return response()->json(['message' => 'Update completed'], 200);
    }

    public function allFarms(Request $request)
    {
        return $this->farmRepo->farms($request->input('account_id'));
    }

    public function allFarmsByUser(Request $request)
    {
        return $this->farmRepo->farmsByUser();
    }

    public function destroy(Request $request, Farm $farm)
    {
        $this->authorize('update', [
            $farm,
            $request->input('account_id')
        ]);

        $deletedFarm = $farm;

        $farm->delete();

        return response()->json(['message' => 'Success'], 200);
    }
}
