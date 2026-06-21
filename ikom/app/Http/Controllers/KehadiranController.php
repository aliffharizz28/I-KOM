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
        $fileName  = 'Laporan_Kehadiran_' . str_replace(' ', '_', $sigName) . '_' . date('Ymd_His') . '.xls';
        $perjumpaans = perjumpaan::where('fld_sig_id', $sigId)
            ->when($sesiAktif, fn($q) => $q->where('fld_krs_id', $sesiAktif->fld_krs_id))
            ->orderBy('fld_meet_tarikh', 'asc')->get();

        $pelajars = pelajar::with(['pengguna', 'kehadiran'])
            ->where('fld_sig_id', $sigId)->get();

        $headers = [
            "Content-type"        => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($pelajars, $perjumpaans, $sigName, $sesiAktif) {
            $totalCols = 3 + count($perjumpaans);
            
            // Output standard Excel HTML header with UTF-8 BOM
            echo "\xEF\xBB\xBF";
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head>';
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            echo '<style>';
            echo '
                table {
                    border-collapse: collapse;
                    font-family: \'Segoe UI\', Arial, sans-serif;
                }
                th {
                    background-color: #0284c7;
                    color: #ffffff;
                    border: 1px solid #cbd5e1;
                    padding: 10px;
                    font-weight: bold;
                    text-align: center;
                    font-size: 11pt;
                }
                td {
                    border: 1px solid #e2e8f0;
                    padding: 8px;
                    font-size: 10pt;
                    vertical-align: middle;
                }
                .text-center {
                    text-align: center;
                }
                .text-left {
                    text-align: left;
                }
                .zebra {
                    background-color: #f8fafc;
                }
                .title-header {
                    font-size: 16pt;
                    font-weight: bold;
                    color: #0284c7;
                    text-align: center;
                }
                .subtitle-header {
                    font-size: 11pt;
                    color: #64748b;
                    text-align: center;
                }
                .status-hadir {
                    color: #15803d;
                    background-color: #f0fdf4;
                    font-weight: bold;
                    text-align: center;
                }
                .status-tidak-hadir {
                    color: #b91c1c;
                    background-color: #fef2f2;
                    font-weight: bold;
                    text-align: center;
                }
                .status-tiada {
                    color: #64748b;
                    text-align: center;
                }
            ';
            echo '</style>';
            echo '</head>';
            echo '<body>';
            
            echo '<table>';
            
            // Title Rows
            echo '<tr><td colspan="' . $totalCols . '" class="title-header" style="border:none;">LAPORAN KEHADIRAN PELAJAR</td></tr>';
            echo '<tr><td colspan="' . $totalCols . '" class="subtitle-header" style="border:none;">KUMPULAN SIG: ' . htmlspecialchars(strtoupper($sigName)) . '</td></tr>';
            if ($sesiAktif) {
                echo '<tr><td colspan="' . $totalCols . '" class="subtitle-header" style="border:none;">SESI: ' . htmlspecialchars($sesiAktif->fld_krs_sesi) . ' (' . htmlspecialchars($sesiAktif->fld_krs_semester) . ')</td></tr>';
            }
            echo '<tr><td colspan="' . $totalCols . '" class="subtitle-header" style="border:none; font-style:italic;">Tarikh Dijana: ' . date('d/m/Y H:i:s') . '</td></tr>';
            echo '<tr><td colspan="' . $totalCols . '" style="border:none; height:15px;"></td></tr>';
            
            // Header
            echo '<thead>';
            echo '<tr>';
            echo '<th style="background-color: #0284c7; color: #ffffff;">No. Matrik</th>';
            echo '<th style="background-color: #0284c7; color: #ffffff;">Nama Pelajar</th>';
            foreach ($perjumpaans as $p) {
                $meetDate = \Carbon\Carbon::parse($p->fld_meet_tarikh)->format('d/m/Y');
                echo '<th style="background-color: #0284c7; color: #ffffff;">' . htmlspecialchars($p->fld_meet_topik) . ' (' . $meetDate . ')</th>';
            }
            echo '<th style="background-color: #0369a1; color: #ffffff;">Peratusan Keseluruhan (%)</th>';
            echo '</tr>';
            echo '</thead>';
            
            // Body
            echo '<tbody>';
            $isZebra = false;
            foreach ($pelajars as $pelajar) {
                $zebraStyle = $isZebra ? ' class="zebra"' : '';
                $isZebra = !$isZebra;
                
                echo '<tr' . $zebraStyle . '>';
                echo '<td class="text-center" style="vnd.ms-excel.numberformat:@">' . htmlspecialchars($pelajar->fld_pel_nomat) . '</td>';
                echo '<td class="text-left">' . htmlspecialchars($pelajar->pengguna->fld_user_nama ?? '-') . '</td>';
                
                $studentAttendances = $pelajar->kehadiran->keyBy('fld_meet_id');
                foreach ($perjumpaans as $p) {
                    $hdr = $studentAttendances->get($p->fld_meet_id);
                    if ($hdr) {
                        $status = trim($hdr->fld_hdr_status);
                        if (strtolower($status) === 'hadir') {
                            echo '<td class="status-hadir">Hadir</td>';
                        } else {
                            echo '<td class="status-tidak-hadir">Tidak Hadir</td>';
                        }
                    } else {
                        echo '<td class="status-tiada">Tiada Rekod</td>';
                    }
                }
                
                echo '<td class="text-center" style="font-weight:bold; background-color:#f0fdf4;">' . htmlspecialchars($pelajar->peratusan_kehadiran) . '%</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '</body>';
            echo '</html>';
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
