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

        $sesiAktif = \App\Models\kursus::getActive();
        $pelajarIds = $sesiAktif 
            ? \App\Models\PendaftaranPelajar::where('fld_sig_id', $penyelaras->fld_sig_id)
                ->where('fld_krs_id', $sesiAktif->fld_krs_id)
                ->pluck('fld_pel_nomat')
            : collect();

        // Get all students in this SIG for the active session
        $pelajars = pelajar::with('pengguna')
                           ->whereIn('fld_pel_nomat', $pelajarIds)
                           ->get();

        // Get all submissions for this assignment (key by uppercase student matrix number)
        $penghantarans = penghantaran::with('pelajar.pengguna')
                                     ->where('fld_tgs_id', $id)
                                     ->get()
                                     ->keyBy(function($item) {
                                         return strtoupper($item->fld_pel_nomat);
                                     });

        // Key pelajars by uppercase nomat for reliable lookup
        $pelajarsByKey = $pelajars->keyBy(function($p) {
            return strtoupper($p->fld_pel_nomat);
        });

        // Fix broken relationships due to case sensitivity
        foreach ($penghantarans as $penghantaran) {
            $nomat = strtoupper($penghantaran->fld_pel_nomat);
            if (isset($pelajarsByKey[$nomat])) {
                $penghantaran->setRelation('pelajar', $pelajarsByKey[$nomat]);
            }
        }

        // Pre-compute view logic for each student
        foreach ($pelajars as $pelajar) {
            $nama = $pelajar->pengguna->fld_user_nama ?? 'Tiada Nama';
            $pelajar->display_nama = $nama;
            
            $pelajar->initials = collect(explode(' ', $nama))
                                 ->map(function($word) { return strtoupper(substr($word, 0, 1)); })
                                 ->take(2)->join('');
            
            $nomatUpper = strtoupper($pelajar->fld_pel_nomat);
            $pelajar->has_submission = isset($penghantarans[$nomatUpper]) && !empty($penghantarans[$nomatUpper]->fld_pgh_fail);
            $hasRecord = isset($penghantarans[$nomatUpper]);
            $pelajar->mark = $hasRecord ? $penghantarans[$nomatUpper]->fld_pgh_markah : '';
            
            // Check for student picture
            $nomat = strtolower($pelajar->fld_pel_nomat);
            $picPath = 'pic/' . $nomat . '.jpg';
            $picUpperPath = 'pic/' . strtoupper($pelajar->fld_pel_nomat) . '.jpg';
            
            $pelajar->has_pic = false;
            $pelajar->final_pic_url = '';
            
            if (file_exists(public_path($picPath))) {
                $pelajar->has_pic = true;
                $pelajar->final_pic_url = asset($picPath);
            } elseif (file_exists(public_path($picUpperPath))) {
                $pelajar->has_pic = true;
                $pelajar->final_pic_url = asset($picUpperPath);
            } elseif ($pelajar->fld_pel_pic && file_exists(public_path('storage/' . $pelajar->fld_pel_pic))) {
                $pelajar->has_pic = true;
                $pelajar->final_pic_url = asset('storage/' . $pelajar->fld_pel_pic);
            }
        }

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

        // Verify the assignment belongs to their SIG
        $tugasan = \App\Models\tugasan::findOrFail($id);
        if ($tugasan->fld_sig_id != $penyelaras->fld_sig_id) {
            return redirect()->route('tugasan')->with('error', 'Akses ditolak. Tugasan ini tidak tergolong dalam kumpulan SIG anda.');
        }

        $sesiAktif = \App\Models\kursus::getActive();

        // Get valid student list for this SIG to prevent cross-SIG grading
        $validStudents = $sesiAktif
            ? \App\Models\PendaftaranPelajar::where('fld_sig_id', $penyelaras->fld_sig_id)
                ->where('fld_krs_id', $sesiAktif->fld_krs_id)
                ->pluck('fld_pel_nomat')
            : collect();

        $marks = $request->input('marks', []);

        try {
            foreach ($marks as $nomat => $mark) {
                if ($mark !== null && $mark !== '') {
                    // Skip students not in this SIG
                    if (!$validStudents->contains($nomat)) {
                        continue;
                    }

                    // Ensure mark is out of 10
                    if ($mark > 10) $mark = 10;
                    if ($mark < 0) $mark = 0;

                    // Find or create the submission record for this student for this assignment
                    $penghantaran = penghantaran::firstOrNew([
                        'fld_tgs_id' => $id,
                        'fld_pel_nomat' => $nomat
                    ]);
                    
                    if (!$penghantaran->exists && is_null($penghantaran->fld_pgh_fail)) {
                        $penghantaran->fld_pgh_fail = '';
                    }
                    
                    $penghantaran->fld_pgh_markah = $mark;
                    $penghantaran->save();
                }
            }
            return back()->with('success', 'Semua markah berjaya disimpan!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Ralat simpan markah: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan markah. Sila pastikan pangkalan data membenarkan penyimpanan. Ralat: ' . $e->getMessage());
        }
    }
}
