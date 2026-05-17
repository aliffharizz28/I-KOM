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
        $fileName  = 'Laporan_Markah_Pelajar_' . str_replace(' ', '_', $sigName) . '_' . date('Ymd_His') . '.csv';
        $pelajarIds = $sesiAktif
            ? PendaftaranPelajar::where('fld_sig_id', $sigId)
                ->where('fld_krs_id', $sesiAktif->fld_krs_id)->pluck('fld_pel_nomat')
            : collect();
        $pelajars = pelajar::with(['pengguna', 'penilaian'])
                           ->whereIn('fld_pel_nomat', $pelajarIds)
                           ->orderBy('fld_pel_nomat', 'asc')->get();
        $kriterias = kriteria::orderBy('fld_krit_id', 'asc')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($pelajars, $kriterias) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM for UTF-8 Excel

            // Define header columns
            $columns = ['No. Matrik', 'Nama Pelajar', 'Tahun', 'Jurusan'];
            foreach ($kriterias as $k) {
                $columns[] = $k->fld_krit_nama .  ' (' . $k->fld_krit_markah . '%)';
            }
            $columns[] = 'Markah Keseluruhan (%)';
            $columns[] = 'Gred';

            fputcsv($file, $columns);

            // Populate rows
            foreach ($pelajars as $pelajar) {
                $row = [];
                $row[] = $pelajar->fld_pel_nomat;
                $row[] = $pelajar->pengguna->fld_user_nama ?? '-';
                $row[] = $pelajar->fld_pel_tahun;
                $row[] = $pelajar->fld_pel_jurusan;

                $studentMarks = $pelajar->penilaian->keyBy('fld_krit_id');

                foreach ($kriterias as $k) {
                    $mark = $studentMarks->get($k->fld_krit_id);
                    $row[] = $mark ? $mark->fld_nilai_markah : '0';
                }

                $keputusan = \App\Models\keputusan::where('fld_pel_nomat', $pelajar->fld_pel_nomat)->first();
                $row[] = $keputusan ? $keputusan->fld_total_markah : '0';
                $row[] = $keputusan ? $keputusan->fld_nilai_gred : '-';

                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
