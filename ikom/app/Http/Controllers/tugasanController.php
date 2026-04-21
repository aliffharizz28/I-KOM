<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\tugasan;
use App\Models\penyelarassig;
use Illuminate\Support\Facades\Auth;

class tugasanController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->fld_user_id;
        $penyelaras = penyelarassig::where('fld_user_id', $userId)->first();
        $tugasans = collect();
        
        if ($penyelaras) {
            // Check for past due assignments and update their status to "Tidak Aktif"
            tugasan::where('fld_sig_id', $penyelaras->fld_sig_id)
                   ->where('fld_tgs_status', 'Aktif')
                   ->whereDate('fld_tgs_tarikh', '<', \Carbon\Carbon::today())
                   ->update(['fld_tgs_status' => 'Tidak Aktif']);

            $tugasans = tugasan::withCount('penghantaran')
                               ->where('fld_sig_id', $penyelaras->fld_sig_id)
                               ->get();
        }
        
        return view('tugasan', compact('tugasans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tugasan_title' => 'required|string|max:255',
            'tugasan_desc' => 'required|string',
            'due_date' => 'required|date',
            'tugasan_file' => 'nullable|file|mimes:pdf,doc,docx,zip,jpeg,png,jpg|max:5120',
        ]);

        $userId = Auth::user()->fld_user_id;

        $penyelaras = penyelarassig::where('fld_user_id', $userId)->first();
        
        if (!$penyelaras) {
            return back()->with('error', 'Anda tidak ditugaskan kepada mana-mana SIG. Tidak boleh mencipta tugasan.');
        }

        $tugasan = new tugasan();
        $tugasan->fld_tgs_nama = $request->tugasan_title;
        $tugasan->fld_tgs_desc = $request->tugasan_desc;
        $tugasan->fld_tgs_tarikh = $request->due_date;
        $tugasan->fld_sig_id = $penyelaras->fld_sig_id;

        // Automatically determine status based on due date
        if (\Carbon\Carbon::parse($request->due_date)->startOfDay()->lt(\Carbon\Carbon::today())) {
            $tugasan->fld_tgs_status = 'Tidak Aktif';
        } else {
            $tugasan->fld_tgs_status = 'Aktif';
        }

        if ($request->hasFile('tugasan_file')) {
            $file = $request->file('tugasan_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('lampiran_tugasan'), $filename);
            $tugasan->fld_tgs_file = $filename;
        }

        $tugasan->save();

        // Get all pelajar registered under this SIG to notify them
        $pelajars = \App\Models\pelajar::with('pengguna')->where('fld_sig_id', $penyelaras->fld_sig_id)->get();
        foreach ($pelajars as $pel) {
            if ($pel->pengguna && !empty($pel->pengguna->fld_user_email)) {
                try {
                    // It is generally safer to queue emails in production, 
                    // but for testing send() works synchronously.
                    \Illuminate\Support\Facades\Mail::to($pel->pengguna->fld_user_email)->send(new \App\Mail\NewAssignmentMail($tugasan));
                } catch (\Exception $e) {
                    // Log or handle any mailing errors silently so it doesn't break the application
                    \Illuminate\Support\Facades\Log::error('Failed to send mail: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('tugasan')->with('success', 'Tugasan berjaya ditambah dan emel makluman telah dihantar kepada pelajar!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tugasan_title' => 'required|string|max:255',
            'tugasan_desc' => 'required|string',
            'due_date' => 'required|date',
            'tugasan_file' => 'nullable|file|mimes:pdf,doc,docx,zip,jpeg,png,jpg|max:5120',
        ]);

        $tugasan = tugasan::findOrFail($id);
        $tugasan->fld_tgs_nama = $request->tugasan_title;
        $tugasan->fld_tgs_desc = $request->tugasan_desc;
        $tugasan->fld_tgs_tarikh = $request->due_date;

        // Automatically determine status based on updated due date
        if (\Carbon\Carbon::parse($request->due_date)->startOfDay()->lt(\Carbon\Carbon::today())) {
            $tugasan->fld_tgs_status = 'Tidak Aktif';
        } else {
            $tugasan->fld_tgs_status = 'Aktif';
        }

        if ($request->hasFile('tugasan_file')) {
            $file = $request->file('tugasan_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('lampiran_tugasan'), $filename);
            $tugasan->fld_tgs_file = $filename;
        }

        $tugasan->save();

        return redirect()->route('tugasan')->with('success', 'Tugasan berjaya dikemaskini!');
    }

    public function destroy($id)
    {
        $tugasan = tugasan::findOrFail($id);
        $tugasan->delete();

        return redirect()->route('tugasan')->with('success', 'Tugasan berjaya dipadam!');
    }
}
