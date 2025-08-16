<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSession
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $sessionIP = $request->session()->get('login_ip');
            $sessionUA = $request->session()->get('user_agent');
            $currentIP = $request->ip();
            $currentUA = $request->header('User-Agent');

            logger()->info('Session Check', [
                'session_ip' => $sessionIP,
                'current_ip' => $currentIP,
                'session_ua' => $sessionUA,
                'current_ua' => $currentUA,
            ]);

            if ($sessionIP !== $currentIP || $sessionUA !== $currentUA) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['warning' => 'Phiên đăng nhập bị thoát, vui lòng đăng nhập lại.']);
            }
        }

        return $next($request);
    }
}
