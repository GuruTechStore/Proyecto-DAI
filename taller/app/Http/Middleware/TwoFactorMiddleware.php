<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->hasEnabledTwoFactor() && !session('2fa_verified')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Verificación de dos factores requerida',
                    'redirect' => route('two-factor.verify')
                ], 403);
            }

            return redirect()->route('two-factor.verify');
        }

        return $next($request);
    }
}