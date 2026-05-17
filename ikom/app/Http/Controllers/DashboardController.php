<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\pelajar;
use App\Models\sig;
use App\Models\kursus;
use App\Models\penyelarassig;
use App\Models\keputusan;
use App\Models\PendaftaranPelajar;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Admin (Role 1)
        if ($user->fld_user_role == 1) {
            $sesiAktif = kursus::getActive();
            $krsId     = $sesiAktif?->fld_krs_id;

            // Total enrolled students in this session across all SIGs
            $totalPelajar      = $krsId ? PendaftaranPelajar::where('fld_krs_id', $krsId)->count() : 0;
            $totalSig          = sig::count();
            $sigWithPenyelaras = penyelarassig::distinct('fld_sig_id')->count('fld_sig_id');
            $totalKursus       = kursus::count();

            $sigs = sig::with(['penyelarassig.pengguna'])->get()->map(function ($sig) use ($krsId) {
                $studentCount = $krsId
                    ? PendaftaranPelajar::where('fld_sig_id', $sig->fld_sig_id)->where('fld_krs_id', $krsId)->count()
                    : 0;
                $gradedCount = $krsId
                    ? keputusan::where('fld_sig_id', $sig->fld_sig_id)->where('fld_krs_id', $krsId)->count()
                    : 0;
                $penyelaras = $sig->penyelarassig->first();
                return [
                    'id'            => $sig->fld_sig_id,
                    'nama'          => $sig->fld_sig_nama,
                    'penyelaras'    => $penyelaras && $penyelaras->pengguna ? $penyelaras->pengguna->fld_user_nama : null,
                    'student_count' => $studentCount,
                    'graded_count'  => $gradedCount,
                ];
            });

            $unassignedSigs = $sigs->filter(fn($s) => $s['penyelaras'] === null);

            return view('dashboard', compact(
                'totalPelajar', 'totalSig', 'sigWithPenyelaras',
                'totalKursus', 'sigs', 'unassignedSigs', 'sesiAktif'
            ));
        }

        // Penyelaras SIG (Role 2)
        if ($user->fld_user_role == 2) {
            $penyelaras = penyelarassig::where('fld_user_id', $user->fld_user_id)->first();
            $sigId      = $penyelaras ? $penyelaras->fld_sig_id : null;
            $sesiAktif  = kursus::getActive();
            $krsId      = $sesiAktif?->fld_krs_id;

            // Students enrolled in this SIG for the active session
            $totalPelajarSig = ($sigId && $krsId)
                ? PendaftaranPelajar::where('fld_sig_id', $sigId)->where('fld_krs_id', $krsId)->count()
                : 0;

            $gradedCount = ($sigId && $krsId)
                ? keputusan::where('fld_sig_id', $sigId)->where('fld_krs_id', $krsId)->count()
                : 0;

            $tugasanAktif = ($sigId && $krsId)
                ? \App\Models\tugasan::where('fld_sig_id', $sigId)->where('fld_krs_id', $krsId)
                    ->where('fld_tgs_status', 'Aktif')->count()
                : 0;

            $recentTugasan = ($sigId && $krsId)
                ? \App\Models\tugasan::where('fld_sig_id', $sigId)->where('fld_krs_id', $krsId)
                    ->withCount('penghantaran')->orderBy('fld_tgs_tarikh', 'desc')->take(5)->get()
                : collect();

            // Students in this session who have not yet been graded
            $enrolledIds = ($sigId && $krsId)
                ? PendaftaranPelajar::where('fld_sig_id', $sigId)->where('fld_krs_id', $krsId)->pluck('fld_pel_nomat')
                : collect();
            $gradedIds = ($sigId && $krsId)
                ? keputusan::where('fld_sig_id', $sigId)->where('fld_krs_id', $krsId)->pluck('fld_pel_nomat')
                : collect();
            $ungradedStudents = pelajar::with('pengguna')
                ->whereIn('fld_pel_nomat', $enrolledIds)
                ->whereNotIn('fld_pel_nomat', $gradedIds)
                ->get();

            return view('dashboard', compact(
                'totalPelajarSig', 'gradedCount', 'tugasanAktif',
                'recentTugasan', 'ungradedStudents', 'sesiAktif'
            ));
        }

        // For Role 3, just return the view (existing logic is in blade)
        return view('dashboard');
    }
}
