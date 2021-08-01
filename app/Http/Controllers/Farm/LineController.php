<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Line\LineRequest;
use App\Http\Requests\Line\UpdateLineRequest;
use App\Http\Resources\Line\LineResource;
use App\Models\Line;
use App\Repositories\Line\LineRepositoryInterface;
use Illuminate\Http\Request;

class LineController extends Controller
{
    private $lineRepo;

    public function __construct(LineRepositoryInterface $line)
    {
        $this->lineRepo = $line;
    }

    public function store(LineRequest $request)
    {
        $attr = $request->validated();

        return $this->lineRepo->createLine($attr);
    }

    public function show(Request $request, Line $line)
    {
        $this->authorize('viewLine', [
            $line,
            $request->input('account_id')
        ]);

        if(!$line) {
            return response()->json(['message' => 'Not found'], 404);
        } else {
            return new LineResource($line);
        }
    }

    public function update(UpdateLineRequest $request, Line $line)
    {
        $attr = $request->validated();

        $this->authorize('editLine', [
            $line,
            $attr['account_id']
        ]);

        $this->lineRepo->editLine($attr);

        return response()->json(['status' => 'Success'], 200);
    }

    public function destroy(Request $request, Line $line)
    {
        $this->authorize('editLine', [
            $line,
            $request->input('account_id')
        ]);

        $line->delete();

        return response()->json(['message' => 'Success'], 200);
//        return new LineResource($deletedLine);
    }
}
