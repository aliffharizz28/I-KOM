<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\sig;
use App\Models\pelajar;
use App\Models\kriteria;
use App\Models\kursus;
use App\Models\PendaftaranPelajar;
use Illuminate\Support\Facades\Auth;

class LaporanSIGController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->fld_user_role != 1) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }
        $sesiAktif = kursus::getActive();
        $dbSigs    = sig::with('penyelarassig.pengguna')->get();
        return view('laporanSIG', compact('dbSigs', 'sesiAktif'));
    }

    public function exportSIG($sigId)
    {
        $user = Auth::user();
        if ($user->fld_user_role != 1) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $sig = sig::find($sigId);
        if (!$sig) return redirect()->back()->with('error', 'Kumpulan SIG tidak ditemui.');
        
        $sesiAktif = kursus::getActive();
        $sigName   = $sig->fld_sig_nama;
        $fileName  = 'Laporan_Markah_Pelajar_' . str_replace(' ', '_', $sigName) . '_' . date('Ymd_His') . '.pdf';
        
        $pelajarIds = $sesiAktif
            ? PendaftaranPelajar::where('fld_sig_id', $sigId)
                ->where('fld_krs_id', $sesiAktif->fld_krs_id)->pluck('fld_pel_nomat')
            : collect();
            
        $pelajars = pelajar::with(['pengguna'])
                           ->whereIn('fld_pel_nomat', $pelajarIds)
                           ->orderBy('fld_pel_nomat', 'asc')->get();

        $keputusans = \App\Models\keputusan::where('fld_sig_id', $sigId)
            ->when($sesiAktif, fn($q) => $q->where('fld_krs_id', $sesiAktif->fld_krs_id))
            ->get()->keyBy('fld_pel_nomat');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfLaporanSIG', compact('sigName', 'pelajars', 'keputusans', 'sesiAktif'));
        
        return $pdf->download($fileName);
    }

    public function viewSIG($sigId)
    {
        $user = Auth::user();
        if ($user->fld_user_role != 1) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $sig = sig::find($sigId);
        if (!$sig) return redirect()->back()->with('error', 'Kumpulan SIG tidak ditemui.');
        $sesiAktif  = kursus::getActive();
        $pelajarIds = $sesiAktif
            ? PendaftaranPelajar::where('fld_sig_id', $sigId)
                ->where('fld_krs_id', $sesiAktif->fld_krs_id)->pluck('fld_pel_nomat')
            : collect();
        $pelajars  = pelajar::with(['pengguna', 'penilaian'])
                           ->whereIn('fld_pel_nomat', $pelajarIds)
                           ->orderBy('fld_pel_nomat', 'asc')->get();
        $kriterias = kriteria::orderBy('fld_krit_id', 'asc')->get();
        $keputusans = \App\Models\keputusan::where('fld_sig_id', $sigId)
            ->when($sesiAktif, fn($q) => $q->where('fld_krs_id', $sesiAktif->fld_krs_id))
            ->get()->keyBy('fld_pel_nomat');
        return view('viewLaporanSIG', compact('sig', 'pelajars', 'kriterias', 'keputusans', 'sesiAktif'));
    }
}
