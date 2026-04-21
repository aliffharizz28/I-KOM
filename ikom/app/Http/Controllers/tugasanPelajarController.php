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

        // Ambil tugasan bagi SIG pelajar tersebut, dan check status penghantaran pelajar ini
        $tugasans = tugasan::with(['penghantaran' => function ($query) use ($pelajar) {
                $query->where('fld_pel_nomat', $pelajar->fld_pel_nomat);
            }])
            ->where('fld_sig_id', $pelajar->fld_sig_id)
            ->orderBy('fld_tgs_tarikh', 'desc')
            ->get();

        return view('tugasanPelajar', compact('tugasans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tugasan_id' => 'required|exists:tugasan,fld_tgs_id',
            'tugasan_file' => 'required|file|mimes:pdf,doc,docx,zip,rar|max:5120',
        ]);

        $userId = Auth::user()->fld_user_id;
        $pelajar = pelajar::where('fld_user_id', $userId)->first();

        if (!$pelajar) {
            return back()->with('error', 'Rekod pelajar tidak dijumpai.');
        }

        // Semak jika sudah ada penghantaran
        $existing = penghantaran::where('fld_tgs_id', $request->tugasan_id)
                                ->where('fld_pel_nomat', $pelajar->fld_pel_nomat)
                                ->first();
                                
        if ($existing) {
            $penghantaran = $existing;
            if ($penghantaran->fld_pgh_fail && file_exists(public_path('lampiran_penghantaran/' . $penghantaran->fld_pgh_fail))) {
                @unlink(public_path('lampiran_penghantaran/' . $penghantaran->fld_pgh_fail));
            }
        } else {
            $penghantaran = new penghantaran();
            $penghantaran->fld_tgs_id = $request->tugasan_id;
            $penghantaran->fld_pel_nomat = $pelajar->fld_pel_nomat;
        }

        if ($request->hasFile('tugasan_file')) {
            $file = $request->file('tugasan_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('lampiran_penghantaran'), $filename);
            $penghantaran->fld_pgh_fail = $filename;
        }

        $penghantaran->save();

        return redirect()->route('tugasanPelajar')->with('success', 'Tugasan berjaya dihantar!');
    }
}
