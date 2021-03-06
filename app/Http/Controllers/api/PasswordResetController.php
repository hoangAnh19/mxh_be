<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\User;

use App\Models\PasswordReset;
use Illuminate\Support\Str;


class PasswordResetController extends Controller
{
    /**
     * Create token password reset
     *
     * @param  [string] email
     * @return [string] message
     */
    public function create(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        PasswordReset::updateOrCreate(['email' => $user->email, 'token' => Str::random(40)]);

        return response()->json([
            'message' => __('passwords.sent'),
            'data' => $user
        ]);
    }

    /**
     * Find token password reset
     *
     * @param  [string] $token
     * @return [string] message
     * @return [json] passwordReset object
     */
    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();

        if (!$passwordReset)
            return response()->json([
                'message' => __('passwords.token')
            ], 404);

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json([
                'message' => __('passwords.token')
            ], 404);
        }

        return response()->json($passwordReset);
    }

    /**
     * Reset password
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @param  [string] token
     * @return [string] message
     * @return [json] user object
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);

        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();

        if (!$passwordReset)
            return response()->json([
                'message' => __('passwords.token')
            ], 404);

        $user = User::where('email', $passwordReset->email)->first();

        if (!$user)
            return response()->json([
                'message' => __('passwords.user')
            ], 404);

        $user->password = bcrypt($request->password);
        $user->save();

        $passwordReset->delete();



        return response()->json($user);
    }
}
