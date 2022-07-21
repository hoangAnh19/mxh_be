<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CoreValue;

class CoreValueController extends Controller
{

    function getCoreValue()
    {
        $core = CoreValue::get();
        return response()->json([
            'status' => 'success',
            'message' => 'lay danh sach thanh cong',
            'data' => $core
        ]);
    }

    function createCoreValue(Request $request)
    {
        $newcore = new CoreValue();
        $CoreValue = $request->CoreValue ?? null;
        $newcore->CoreValue = $CoreValue;


        CoreValue::updateOrCreate(['CoreValue' => $CoreValue]);
        return response()->json([
            'status' => 'success',
            'message' => 'Tao moi thanh cong',
            'data' => $newcore
        ]);
    }
    function updateCoreValue()
    {
    }
}
