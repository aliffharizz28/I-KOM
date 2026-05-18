<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\tugasan;
use App\Models\pelajar;
use App\Models\penghantaran;
use Illuminate\Support\Facades\Auth;

class tugasanPelajarController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->fld_user_id;
        
        // Dapatkan rekod pelajar current user
        $pelajar = pelajar::where('fld_user_id', $userId)->first();
        
        if (!$pelajar) {
            return redirect()->route('dashboard')->with('error', 'Rekod pelajar tidak dijumpai.');
        }
        
        if (!$pelajar->fld_sig_id) {
            return redirect()->route('dashboard')->with('error', 'Anda belum didaftarkan ke mana-mana SIG.');
        }

        // Ambil tugasan bagi SIG pelajar tersebut
        $tugasans = tugasan::with('penghantaran')
            ->where('fld_sig_id', $pelajar->fld_sig_id)
            ->where('is_published', 1)
            ->orderBy('fld_tgs_tarikh', 'desc')
            ->get();

        $rakanSigs = pelajar::with('pengguna')
            ->where('fld_sig_id', $pelajar->fld_sig_id)
            ->where('fld_pel_nomat', '!=', $pelajar->fld_pel_nomat)
            ->get();

        return view('tugasanPelajar', compact('tugasans', 'rakanSigs', 'pelajar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tugasan_id' => 'required|exists:tugasan,fld_tgs_id',
            'tugasan_file' => 'required|file|mimes:pdf,doc,docx,zip,rar|max:51200',
            'group_members' => 'nullable|array',
            'group_members.*' => 'exists:pelajar,fld_pel_nomat'
        ]);

        $userId = Auth::user()->fld_user_id;
        $pelajar = pelajar::where('fld_user_id', $userId)->first();

        if (!$pelajar) {
            return back()->with('error', 'Rekod pelajar tidak dijumpai.');
        }

        // --- STEP 1: Validate new group members (check for OTHER groups' submissions) ---
        $membersToSave = [];
        if ($request->has('group_members')) {
            foreach ($request->group_members as $nomat) {
                $hasSubmitted = penghantaran::where('fld_tgs_id', $request->tugasan_id)
                                            ->where('fld_pel_nomat', $nomat)
                                            ->exists();
                if ($hasSubmitted) {
                    return back()->with('error', "Pelajar dengan no matrik $nomat telah pun berada dalam kumpulan lain atau telah menghantar tugasan ini. Sila buang pelajar tersebut daripada senarai ahli.");
                }
                $membersToSave[] = $nomat;
            }
        }

        // --- STEP 3: Upload new file ---
        $filename = null;
        if ($request->hasFile('tugasan_file')) {
            $file = $request->file('tugasan_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('lampiran_penghantaran'), $filename);
        }

        // --- STEP 4: Save fresh records ---
        try {
            // Save submitter's record
            $penghantaran = new penghantaran();
            $penghantaran->fld_tgs_id = $request->tugasan_id;
            $penghantaran->fld_pel_nomat = $pelajar->fld_pel_nomat;
            $penghantaran->fld_pgh_fail = $filename;
            $penghantaran->save();

            // Save record for each tagged group member (same file)
            foreach ($membersToSave as $nomat) {
                $memberPenghantaran = new penghantaran();
                $memberPenghantaran->fld_tgs_id = $request->tugasan_id;
                $memberPenghantaran->fld_pel_nomat = $nomat;
                $memberPenghantaran->fld_pgh_fail = $filename;
                $memberPenghantaran->save();
            }

            return redirect()->route('tugasanPelajar')->with('success', 'Tugasan berjaya dihantar!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DB Save error: ' . $e->getMessage());
            return back()->with('error', 'Ralat menyimpan ke pangkalan data: ' . $e->getMessage());
        }
    }

    public function destroySubmission(Request $request, $id)
    {
        $userId = Auth::user()->fld_user_id;
        $pelajar = pelajar::where('fld_user_id', $userId)->first();

        if (!$pelajar) {
            return response()->json(['success' => false, 'message' => 'Rekod pelajar tidak dijumpai.']);
        }

        $existing = penghantaran::where('fld_tgs_id', $id)
                                ->where('fld_pel_nomat', $pelajar->fld_pel_nomat)
                                ->first();

        if ($existing) {
            $oldFile = $existing->fld_pgh_fail;

            // Delete the physical file from disk
            if ($oldFile && file_exists(public_path('lampiran_penghantaran/' . $oldFile))) {
                @unlink(public_path('lampiran_penghantaran/' . $oldFile));
            }

            // Delete ALL penghantaran records sharing the same old file for this tugasan
            penghantaran::where('fld_tgs_id', $id)
                        ->where('fld_pgh_fail', $oldFile)
                        ->delete();

            return response()->json(['success' => true, 'message' => 'Penghantaran lama berjaya dipadam.']);
        }

        // If no record found, it's already deleted, so we can still proceed
        return response()->json(['success' => true, 'message' => 'Tiada rekod lama, boleh teruskan.']);
    }
}
