<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        // Assuming staff are users with 'staff' role
        $staff = User::where('role', 'staff')->get();
        return view('staff.index', compact('staff'));
    }
}
