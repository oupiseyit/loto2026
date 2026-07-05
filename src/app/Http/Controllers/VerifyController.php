<?php

namespace App\Http\Controllers;

use App\Verify\VerifyRunner;
use Illuminate\Http\JsonResponse;

class VerifyController extends Controller
{
    public function dashboard(): \Illuminate\View\View
    {
        abort_unless(config('app.debug'), 403);
        $manifest = (new VerifyRunner())->manifest();
        return view('verify', compact('manifest'));
    }

    public function replay(): \Illuminate\View\View
    {
        abort_unless(config('app.debug'), 403);
        $manifest = (new VerifyRunner())->manifest();
        return view('verify-replay', compact('manifest'));
    }

    public function manifest(): JsonResponse
    {
        abort_unless(config('app.debug'), 403);
        return response()->json((new VerifyRunner())->manifest());
    }

    public function runAll(): JsonResponse
    {
        abort_unless(config('app.debug'), 403);
        $results = (new VerifyRunner())->run();
        $counts  = collect($results)->countBy('verdict');
        $pass    = ($counts->get('FAIL', 0) + $counts->get('BLOCKED', 0)) === 0;

        return response()->json(['pass' => $pass, 'counts' => $counts, 'results' => $results]);
    }

    public function runOne(string $endpoint, string $fixture): JsonResponse
    {
        abort_unless(config('app.debug'), 403);
        $results = (new VerifyRunner())->run($endpoint, $fixture);
        return response()->json($results[0] ?? ['verdict' => 'BLOCKED', 'error' => 'fixture not found']);
    }
}
