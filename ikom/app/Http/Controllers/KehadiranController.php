<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\perjumpaan;
use App\Models\kehadiran;
use App\Models\pelajar;
use App\Models\kursus;
use App\Models\PendaftaranPelajar;

class KehadiranController extends Controller
{
    public function index()
    {
        $user      = Auth::user();
        $sesiAktif = kursus::getActive();
        $sigId     = null;

        if ($user->fld_user_role == 2) {
            $sigId = $user->penyelarassig->fld_sig_id ?? null;
        } elseif ($user->fld_user_role == 3) {
            $sigId = $user->pelajar->fld_sig_id ?? null;
        }

        $perjumpaans = [];
        if ($sigId && $sesiAktif) {
            $perjumpaans = perjumpaan::where('fld_sig_id', $sigId)
                ->where('fld_krs_id', $sesiAktif->fld_krs_id)
                ->orderBy('fld_meet_tarikh', 'desc')->get();
        }

        return view('kehadiran', compact('perjumpaans', 'sigId', 'sesiAktif'));
    }

    public function storePerjumpaan(Request $request)
    {
        $request->validate(['topik' => 'required|string|max:255', 'tarikh' => 'required|date']);
        $user      = Auth::user();
        $sesiAktif = kursus::getActive();
        if (!$sesiAktif) return redirect()->back()->with('error', 'Tiada sesi kursus aktif.');
        $sigId = $user->pelajar->fld_sig_id ?? null;
        if (!$sigId) return redirect()->back()->with('error', 'Kumpulan SIG tidak ditemui untuk sesi semasa.');
        perjumpaan::create([
            'fld_meet_topik'  => $request->topik,
            'fld_meet_tarikh' => $request->tarikh,
            'fld_meet_verify' => 0,
            'fld_sig_id'      => $sigId,
            'fld_krs_id'      => $sesiAktif->fld_krs_id,
        ]);
        return redirect()->route('kehadiran')->with('success', 'Perjumpaan berjaya dicipta.');
    }

    public function rekodKehadiran($id)
    {
        $perjumpaan = perjumpaan::findOrFail($id);
        $user       = Auth::user();
        $sesiAktif  = kursus::getActive();
        $sigId      = null;
        if ($user->fld_user_role == 2) {
            $sigId = $user->penyelarassig->fld_sig_id ?? null;
        } elseif ($user->fld_user_role == 3) {
            $sigId = $user->pelajar->fld_sig_id ?? null;
        }
        if ($perjumpaan->fld_sig_id !== $sigId) {
            return redirect()->route('kehadiran')->with('error', 'Akses ditolak.');
        }

        $pelajars = pelajar::with(['pengguna', 'kehadiran' => fn($q) => $q->where('fld_meet_id', $id)])
            ->where('fld_sig_id', $sigId)->get();
        return view('rekodKehadiran', compact('perjumpaan', 'pelajars'));
    }

    public function simpanKehadiran(Request $request, $id)
    {
        $perjumpaan = perjumpaan::findOrFail($id);
        $kehadirans = $request->input('kehadiran', []); 

        foreach ($kehadirans as $nomat => $status) {
            kehadiran::updateOrCreate(
                ['fld_meet_id' => $id, 'fld_pel_nomat' => $nomat],
                ['fld_hdr_status' => $status]
            );
        }

        return redirect()->route('kehadiran')->with('success', 'Kehadiran berjaya disimpan.');
    }

    public function sahkanKehadiran($id)
    {
        $perjumpaan = perjumpaan::findOrFail($id);
        $user = Auth::user();

        if ($user->fld_user_role != 2) {
            return redirect()->route('kehadiran')->with('error', 'Akses ditolak.');
        }

        $perjumpaan->fld_meet_verify = 1;
        $perjumpaan->save();

        return redirect()->route('kehadiran')->with('success', 'Kehadiran bagi perjumpaan ini telah berjaya disahkan.');
    }

    public function exportCSV()
    {
        $user = Auth::user();
        if ($user->fld_user_role != 2) {
            return redirect()->route('kehadiran')->with('error', 'Akses ditolak.');
        }

        $sigId     = $user->penyelarassig->fld_sig_id ?? null;
        if (!$sigId) return redirect()->back()->with('error', 'Kumpulan SIG tidak ditemui.');
        $sesiAktif = kursus::getActive();
        $sigName   = $user->penyelarassig->sig->fld_sig_nama ?? 'SIG';
        $fileName  = 'Laporan_Kehadiran_' . str_replace(' ', '_', $sigName) . '_' . date('Ymd_His') . '.csv';
        $perjumpaans = perjumpaan::where('fld_sig_id', $sigId)
            ->when($sesiAktif, fn($q) => $q->where('fld_krs_id', $sesiAktif->fld_krs_id))
            ->orderBy('fld_meet_tarikh', 'asc')->get();

        $pelajars = pelajar::with(['pengguna', 'kehadiran'])
            ->where('fld_sig_id', $sigId)->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($pelajars, $perjumpaans) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel to read special characters correctly
            fputs($file, "\xEF\xBB\xBF");

            // Define header columns
            $columns = ['No. Matrik', 'Nama Pelajar'];
            foreach ($perjumpaans as $p) {
                $columns[] = $p->fld_meet_topik . ' (' . \Carbon\Carbon::parse($p->fld_meet_tarikh)->format('d/m/Y') . ')';
            }
            $columns[] = 'Peratusan Keseluruhan (%)';

            fputcsv($file, $columns);

            // Add data rows
            foreach ($pelajars as $pelajar) {
                $row = [];
                $row[] = $pelajar->fld_pel_nomat;
                $row[] = $pelajar->pengguna->fld_user_nama ?? '-';

                // Map student attendances by meeting ID for fast lookup
                $studentAttendances = $pelajar->kehadiran->keyBy('fld_meet_id');

                foreach ($perjumpaans as $p) {
                    $hdr = $studentAttendances->get($p->fld_meet_id);
                    if ($hdr) {
                        $row[] = $hdr->fld_hdr_status;
                    } else {
                        $row[] = 'Tiada Rekod';
                    }
                }

                $row[] = $pelajar->peratusan_kehadiran . '%';
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroyPerjumpaan($id)
    {
        $user = Auth::user();
        if ($user->fld_user_role != 3) {
            return redirect()->route('kehadiran')->with('error', 'Akses ditolak.');
        }

        $perjumpaan = perjumpaan::findOrFail($id);

        // Hanya boleh padam jika belum disahkan
        if ($perjumpaan->fld_meet_verify == 1) {
            return redirect()->route('kehadiran')->with('error', 'Perjumpaan telah disahkan dan tidak boleh dipadam.');
        }

        $sigId = $user->pelajar->fld_sig_id ?? null;
        if ($perjumpaan->fld_sig_id !== $sigId) {
            return redirect()->route('kehadiran')->with('error', 'Akses ditolak.');
        }

        // Delete associated kehadirans first
        kehadiran::where('fld_meet_id', $id)->delete();
        $perjumpaan->delete();

        return redirect()->route('kehadiran')->with('success', 'Perjumpaan berjaya dipadam.');
    }
}
