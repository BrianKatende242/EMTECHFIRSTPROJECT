<?php

use App\Models\School;

if (!function_exists('getAuthenticatedSchool')) {
    /**
     * Get the authenticated school from session
     *
     * @return School|null
     */
    function getAuthenticatedSchool() {
        $authenticatedUser = session('authenticated_user');
        if ($authenticatedUser && $authenticatedUser['type'] === 'school') {
            return School::find($authenticatedUser['id']);
        }
        return null;
    }
}
