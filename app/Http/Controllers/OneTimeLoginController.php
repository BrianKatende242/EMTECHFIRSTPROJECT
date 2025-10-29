<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use App\Models\OneTimeLoginToken;

class OneTimeLoginController extends Controller
{
    public function consume($token, Request $request)
    {
        // Find token record
        $record = OneTimeLoginToken::where('token', $token)->first();

        if (!$record) {
            return response()->view('one-time-login.error', [
                'message' => 'Invalid or expired login link.',
                'seconds' => 10
            ]);
        }

        if ($record->used) {
            return response()->view('one-time-login.error', [
                'message' => 'This login link has already been used.',
                'seconds' => 10
            ]);
        }

        if ($record->isExpired()) {
            return response()->view('one-time-login.error', [
                'message' => 'This login link has expired.',
                'seconds' => 10
            ]);
        }

        // Get the user based on type
        $user = null;
        $redirectUrl = '';

        switch ($record->user_type) {
            case 'school':
                $user = \App\Models\School::find($record->user_id);
                $redirectUrl = url("/school-dashboard");
                break;
            case 'doctor':
                $user = \App\Models\Doctor::find($record->user_id);
                $redirectUrl = url('/doctor/dashboard');
                break;
            case 'health_facility':
                $user = \App\Models\HealthFacility::find($record->user_id);
                $redirectUrl = url("/health-facility/dashboard");
                break;
            default:
                return response()->view('one-time-login.error', [
                    'message' => 'Invalid user type.',
                    'seconds' => 10
                ]);
        }

        if (!$user) {
            return response()->view('one-time-login.error', [
                'message' => 'User account not found.',
                'seconds' => 10
            ]);
        }

        // Handle authentication based on user type
        if ($record->user_type === 'doctor') {
            // Flush other sessions for this doctor (best-effort)
            $this->flushSessionsForDoctor($user);

            // Ensure any currently authenticated user is logged out and session invalidated
            try {
                $guard = Auth::guard();
                $guard->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Forget remember-me recaller cookie if present
                $recaller = $guard->getRecallerName();
                if ($recaller) {
                    Cookie::queue(Cookie::forget($recaller));
                }
            } catch (\Exception $e) {
                // ignore
            }

            // Log the doctor in using the doctor guard
            Auth::guard('doctor')->loginUsingId($user->id);
        } else {
            // For schools and health facilities, we might need to implement session-based auth
            // For now, store user info in session
            $request->session()->put('authenticated_user', [
                'type' => $record->user_type,
                'id' => $user->id,
                'name' => $user->name,
                'email' => $record->email
            ]);

            // Set session lifetime to 12 hours (720 minutes) for one-time login users
            $request->session()->put('_session_lifetime', 720);
        }

        // Regenerate session to prevent fixation
        $request->session()->regenerate();

        // Mark token as used
        $record->markAsUsed();

        \Log::info('One-time login successful', [
            'user_type' => $record->user_type,
            'user_id' => $record->user_id,
            'email' => $record->email
        ]);

        // Redirect to appropriate dashboard
        return redirect($redirectUrl);
    }

    protected function flushSessionsForDoctor($doctor)
    {
        $driver = Config::get('session.driver', 'file');

        try {
            if ($driver === 'database') {
                // Remove rows in sessions table where user_id matches
                DB::table(Config::get('session.table', 'sessions'))
                    ->where('user_id', $doctor->id)
                    ->delete();

                // Also try a payload search as a fallback
                DB::table(Config::get('session.table', 'sessions'))
                    ->where('payload', 'like', '%' . $doctor->id . '%')
                    ->delete();
                return;
            }

            if ($driver === 'file') {
                $dir = storage_path('framework/sessions');
                if (File::isDirectory($dir)) {
                    $files = File::files($dir);
                    foreach ($files as $f) {
                        $contents = File::get($f->getPathname());
                        if (strpos($contents, (string) $doctor->id) !== false) {
                            // best-effort: delete session file
                            @unlink($f->getPathname());
                        }
                    }
                }
                return;
            }

            // For other drivers (redis, memcached) attempt DB fallback: delete by payload
            DB::table(Config::get('session.table', 'sessions'))
                ->where('payload', 'like', '%' . $doctor->id . '%')
                ->delete();
        } catch (\Exception $e) {
            // don't block login on cleanup failure; just continue
        }
    }
}
