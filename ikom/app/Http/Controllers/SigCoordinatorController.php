<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\sig;
use App\Models\penyelarassig;
use App\Models\pengguna;

class SigCoordinatorController extends Controller
{
    public function index()
    {
        // Fetch all SIGs with their current Penyelaras and the Penyelaras' Pengguna details
        $dbSigs = sig::with('penyelarassig.pengguna')->get();

        // Fetch users with role 2 who are not assigned to any SIG yet
        $assignedIds = penyelarassig::pluck('fld_user_id')->toArray();
        $availablePenyelaras = pengguna::where('fld_user_role', 2)
                                       ->whereNotIn('fld_user_id', $assignedIds)
                                       ->get();

        return view('penyelarasSigRegistration', compact('dbSigs', 'availablePenyelaras'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required', // The selected fld_user_id
            'sig_id' => 'required'
        ]);

        // Link into penyelarassig table
        $ps = new \App\Models\penyelarassig();
        $ps->fld_user_id = $request->id;
        $ps->fld_sig_id = $request->sig_id;
        $ps->save();

        session()->flash('success', 'Penyelaras SIG berjaya ditambah!');
        return response()->json(['success' => true]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required', // The new fld_user_id
            'sig_id' => 'required'
        ]);

        // Find existing record in penyelarassig for this SIG, and update it
        $ps = \App\Models\penyelarassig::where('fld_sig_id', $request->sig_id)->first();
        
        if($ps) {
            $ps->fld_user_id = $request->id;
            $ps->save();
            session()->flash('success', 'Maklumat berjaya dikemaskini.');
            return response()->json(['success' => true]);
        }
        
        session()->flash('error', 'Rekod Penyelaras SIG tidak dijumpai.');
        return response()->json(['success' => false], 404);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required']);
        
        $user_id = $request->id;
        
        // Remove relationship in penyelarassig only (unassign them)
        \App\Models\penyelarassig::where('fld_user_id', $user_id)->delete();
        
        session()->flash('success', 'Penyelaras SIG berjaya dipadam.');
        return response()->json(['success' => true]);
    }
}
