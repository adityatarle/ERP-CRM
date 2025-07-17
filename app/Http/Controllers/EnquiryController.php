<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnquiryController extends Controller
{
    public function index()
    {
        $enquiries = Enquiry::with('followUps')->latest()->paginate(10);
        return view('enquiry.index', compact('enquiries'));
    }

    public function create()
    {
        return view('enquiry.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'details' => 'required|string',
            'status' => 'required|in:new,in_progress,resolved,closed',
        ]);

        Enquiry::create(array_merge($validated, [
            'user_id' => Auth::id(), // Assign to the logged-in manager
        ]));

        return redirect()->route('enquiry.index')->with('success', 'Enquiry created successfully.');
    }

    public function show(Enquiry $enquiry)
    {
        $enquiry->load('followUps.user'); // Load follow-ups with the user who added them
        return view('enquiry.show', compact('enquiry'));
    }

    public function edit(Enquiry $enquiry)
    {
        return view('enquiry.edit', compact('enquiry'));
    }

    public function update(Request $request, Enquiry $enquiry)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'details' => 'required|string',
            'status' => 'required|in:new,in_progress,resolved,closed',
        ]);

        $enquiry->update($validated);

        return redirect()->route('enquiry.index')->with('success', 'Enquiry updated successfully.');
    }

    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();
        return redirect()->route('enquiry.index')->with('success', 'Enquiry deleted successfully.');
    }

    public function addFollowUp(Request $request, Enquiry $enquiry)
    {
        $validated = $request->validate([
            'follow_up_date' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:new,in_progress,resolved,closed',
        ]);

        $enquiry->followUps()->create(array_merge($validated, [
            'user_id' => Auth::id(), // Manager who added the follow-up
        ]));

        // Update the enquiry status based on the follow-up
        $enquiry->update(['status' => $validated['status']]);

        return redirect()->route('enquiry.show', $enquiry)->with('success', 'Follow-up added successfully.');
    }
}
