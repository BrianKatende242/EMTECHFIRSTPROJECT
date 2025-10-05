<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;

class OneTimeLoginController extends Controller
{
    public function consume($token, Request $request)
    {
        // find token record
        $record = DB::table('one_time_logins')->where('token', $token)->first();

        if (!$record) {
            return response()->view('one-time-login.error', ['message' => 'Invalid or expired login link.', 'seconds' => 10]);
        }

        if ($record->used) {
            return response()->view('one-time-login.error', ['message' => 'This login link has already been used.', 'seconds' => 10]);
        }

        if ($record->expires_at && now()->greaterThan(
            \Carbon\Carbon::parse($record->expires_at)
        )) {
            return response()->view('one-time-login.error', ['message' => 'This login link has expired.', 'seconds' => 10]);
        }

        // load doctor and log them in using the web guard
        $doctor = \App\Models\Doctor::find($record->doctor_id);
        if (!$doctor) {
            return response()->view('one-time-login.error', ['message' => 'Doctor account not found.', 'seconds' => 10]);
        }

        // Flush other sessions for this doctor (best-effort)
        $this->flushSessionsForDoctor($doctor);

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

        // Log the doctor in using the default guard
        Auth::loginUsingId($doctor->id);
        $request->session()->regenerate();

    // mark token used
    DB::table('one_time_logins')->where('id', $record->id)->update(['used' => true, 'updated_at' => now()]);

        // redirect to doctor's dashboard (route expects doctorId)
        return redirect()->route('doctor.dashboard', ['doctorId' => $doctor->id]);
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
