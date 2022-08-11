<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CoreValue;

class CoreValueController extends Controller
{

    function getCoreValue()
    {
        if ($core = CoreValue::get()) {
            return response()->json([
                'status' => 'success',
                'message' => 'lay danh sach thanh cong',
                'data' => $core
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'He thong da co loi xay ra, vui long thu lai',
            ]);
        }
    }




    function createCoreValue(Request $request)
    {
        $newcore = new CoreValue();
        $CoreValue = $request->CoreValue ?? null;
        $newcore->CoreValue = $CoreValue;


        if (CoreValue::updateOrCreate(['CoreValue' => $CoreValue])) {
            return response()->json([
                'status' => 'success',
                'message' => 'Tao moi thanh cong',
                'data' => $newcore
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'He thong da co loi xay ra, vui long thu lai',
            ]);
        }
    }



    function deleteCoreValue(Request $request)
    {

        $coreValue = CoreValue::find($request->id);
        if ($coreValue->delete()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Xoá thành công',
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'He thong da co loi xay ra, vui long thu lai',
            ]);
        }
    }

    function updateCoreValue(Request $request)
    {

        $coreValue = CoreValue::find($request->id);
        $coreValue->CoreValue = $request->coreValue;
        if ($coreValue->save()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật thành công',
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'He thong da co loi xay ra, vui long thu lai',
            ]);
        }
    }
}
