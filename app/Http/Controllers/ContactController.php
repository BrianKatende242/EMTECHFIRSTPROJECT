<?php

namespace App\Http\Controllers;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string',
            'accept_policy' => 'required|boolean'
        ]);

        $submission = ContactSubmission::create($validated);

        return response()->json([
            'message' => 'Contact form submitted successfully',
            'data' => $submission
        ], 201);
    }

    public function index(Request $request)
    {
        $submissions = ContactSubmission::latest()->get();
        
        // Check if this is an API request
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $submissions
            ]);
        }
        
        return view('contact-us', ['submissions' => $submissions]);
    }
}