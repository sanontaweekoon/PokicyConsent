<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function microsoftCallback(Request $request)
    {
        if ($request->get('error') === 'consent_required') {
            return redirect('/login?error=consent_required');
        }

        try {
            $ms = Socialite::driver('azure')->stateless()->user();

            $email = strtolower($ms->getEmail() ?? '');

            if (!$email || ! Str::of($email)->lower()->endsWith('@inteqc.com')) {
                return redirect('/login?error=no_permission');
            }
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response([
                    'message' => 'Invalid Login!', 401
                ]);
                return redirect('/login?error=no_account');

            } else {

                Auth::login($user, true);

                $user->tokens()->delete();

                $token = $user->createToken($request->userAgent(), ["$user->role"])->plainTextToken;

                $user->update([
                    'current_token' => explode('|', $token)[1] ?? null
                ]);

                return redirect('/login?token=' . urlencode($token));
     
            }
        } catch (\Throwable $e) {
            report($e);
             if (env('APP_DEBUG', false)) {
                $msg = urlencode(substr($e->getMessage(), 0, 300));
                return redirect('/login?error=callback_failed&msg=' . $msg);
            }
            return redirect('/login?error=callback_failed');
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->noContent();
    }

    public function logoutBeacon(Request $request)
    {
        $token = (string) $request->input('token');

        if ($pat = PersonalAccessToken::findToken($token)) {
            $pat->delete();
        }
        return response()->json(['ok' => true]);
    }
}
