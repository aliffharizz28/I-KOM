<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\tugasan;
use App\Models\penghantaran;
use App\Models\pelajar;
use App\Models\penyelarassig;

class semakanTugasanController extends Controller
{
    public function show($id)
    {
        $userId = Auth::user()->fld_user_id;

        // Verify that the user is a penyelarassig
        $penyelaras = penyelarassig::where('fld_user_id', $userId)->first();
        if (!$penyelaras) {
            return redirect()->route('tugasan')->with('error', 'Akses ditolak. Anda tiada rekod penyelaras SIG.');
        }

        // Get the requested assignment
        $tugasan = tugasan::findOrFail($id);

        // Verify the assignment belongs to their SIG
        if ($tugasan->fld_sig_id != $penyelaras->fld_sig_id) {
            return redirect()->route('tugasan')->with('error', 'Akses ditolak. Tugasan ini tidak tergolong dalam kumpulan SIG anda.');
        }

        // Get all students in this SIG
        $pelajars = pelajar::with('pengguna')
                           ->where('fld_sig_id', $penyelaras->fld_sig_id)
                           ->get();

        // Get all submissions for this assignment (key by student matrix number)
        $penghantarans = penghantaran::with('pelajar.pengguna')
                                     ->where('fld_tgs_id', $id)
                                     ->get()
                                     ->keyBy('fld_pel_nomat');

        return view('semakanTugasan', compact('tugasan', 'pelajars', 'penghantarans'));
    }

    public function saveMarks(Request $request, $id)
    {
        $userId = Auth::user()->fld_user_id;

        // Verify that the user is a penyelarassig
        $penyelaras = penyelarassig::where('fld_user_id', $userId)->first();
        if (!$penyelaras) {
            return redirect()->route('tugasan')->with('error', 'Akses ditolak.');
        }

        $marks = $request->input('marks', []);

        foreach ($marks as $nomat => $mark) {
            if ($mark !== null && $mark !== '') {
                // Ensure mark is out of 10
                if ($mark > 10) $mark = 10;
                if ($mark < 0) $mark = 0;

                // Find or create the submission record for this student for this assignment
                $penghantaran = penghantaran::firstOrNew([
                    'fld_tgs_id' => $id,
                    'fld_pel_nomat' => $nomat
                ]);
                
                $penghantaran->fld_pgh_markah = $mark;
                $penghantaran->save();
            }
        }

        return back()->with('success', 'Semua markah berjaya disimpan!');
    }
}
