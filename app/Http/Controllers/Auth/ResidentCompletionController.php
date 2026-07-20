<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResidentCompletionController extends Controller
{
    // Show the Steps Form
    public function showForm()
    {
        $user = Auth::user();
        
        if ($user->status !== 'incomplete') {
            return redirect()->route('resident.login')->withErrors(['email' => 'Your account is already being processed or is approved.']);
        }

        return view('resident.complete_registration', compact('user'));
    }

    // Handle the final submission (Step 3)
    public function submitForm(Request $request)
    {
        $user = Auth::user();

        // 1. Base validation rules (Everyone needs these)
        $rules = [
            'terms' => 'accepted',
            'id_type' => 'required|string', 
            'proof_of_residency' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240', 
        ];

        // 2. Only require these if they signed up with Google!
        if ($user->google_id) {
            $rules['password'] = 'required|min:8';
            $rules['birthdate'] = 'required|date';
            $rules['gender'] = 'required|string';
        }

        $request->validate($rules);

        // 3. Handle the ID Upload
        $imagePath = null;
        if ($request->hasFile('proof_of_residency')) {
            $imagePath = $request->file('proof_of_residency')->store('residency_proofs', 'public');
        }

        // 4. Prepare the base data to update
        $updateData = [
            'id_type' => $request->id_type, 
            'proof_of_residency' => $imagePath ?? null,
            'status' => 'pending_approval', 
        ];

        // 5. If they came from Google, add their extra details to the update
        if ($user->google_id) {
            $updateData['password'] = Hash::make($request->password);
            $updateData['birthdate'] = $request->birthdate;
            $updateData['gender'] = $request->gender;
        }

        // THE FIX: 100% foolproof check. If they have no email, they MUST be a phone user.
        $notifyMethod = empty($user->email) ? 'phone' : 'email';

        // Update the User in the Database
        $user->update($updateData);

        // STRICT LOGOUT: Kick them out to Waiting Room 2!
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // THE FIX: Hardcode the query string so Laravel doesn't scramble the "=" sign!
        return redirect(route('resident.pending') . '?notify=' . $notifyMethod);
    }
}