<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\pelajar;
use App\Models\sig;
use App\Models\kursus;
use App\Models\penyelarassig;
use App\Models\keputusan;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Admin (Role 1)
        if ($user->fld_user_role == 1) {
            $totalPelajar = pelajar::count();
            $totalSig = sig::count();
            $sigWithPenyelaras = penyelarassig::distinct('fld_sig_id')->count('fld_sig_id');
            $totalKursus = kursus::count();

            // SIG overview with student counts and penyelaras info
            $sigs = sig::with(['penyelarassig.pengguna'])->get()->map(function ($sig) {
                $studentCount = pelajar::where('fld_sig_id', $sig->fld_sig_id)->count();
                $gradedCount = keputusan::where('fld_sig_id', $sig->fld_sig_id)->count();
                $penyelaras = $sig->penyelarassig->first();
                
                return [
                    'id' => $sig->fld_sig_id,
                    'nama' => $sig->fld_sig_nama,
                    'penyelaras' => $penyelaras && $penyelaras->pengguna ? $penyelaras->pengguna->fld_user_nama : null,
                    'student_count' => $studentCount,
                    'graded_count' => $gradedCount,
                ];
            });

            // SIGs without penyelaras
            $unassignedSigs = $sigs->filter(fn($s) => $s['penyelaras'] === null);

            return view('dashboard', compact(
                'totalPelajar',
                'totalSig',
                'sigWithPenyelaras',
                'totalKursus',
                'sigs',
                'unassignedSigs'
            ));
        }

        // Penyelaras SIG (Role 2)
        if ($user->fld_user_role == 2) {
            $penyelaras = penyelarassig::where('fld_user_id', $user->fld_user_id)->first();
            $sigId = $penyelaras ? $penyelaras->fld_sig_id : null;

            // Total students in this SIG
            $totalPelajarSig = $sigId ? pelajar::where('fld_sig_id', $sigId)->count() : 0;

            // Grading progress
            $gradedCount = $sigId ? keputusan::where('fld_sig_id', $sigId)->count() : 0;

            // Active assignments count
            $tugasanAktif = $sigId ? \App\Models\tugasan::where('fld_sig_id', $sigId)
                ->where('fld_tgs_status', 'Aktif')->count() : 0;

            // Recent assignments with submission counts
            $recentTugasan = $sigId ? \App\Models\tugasan::where('fld_sig_id', $sigId)
                ->withCount('penghantaran')
                ->orderBy('fld_tgs_tarikh', 'desc')
                ->take(5)
                ->get() : collect();

            // Students not yet graded
            $ungradedStudents = $sigId ? pelajar::where('fld_sig_id', $sigId)
                ->whereNotIn('fld_pel_nomat', function ($query) use ($sigId) {
                    $query->select('fld_pel_nomat')
                          ->from('keputusan')
                          ->where('fld_sig_id', $sigId);
                })
                ->with('pengguna')
                ->get() : collect();

            return view('dashboard', compact(
                'totalPelajarSig',
                'gradedCount',
                'tugasanAktif',
                'recentTugasan',
                'ungradedStudents',
                'totalPelajarSig'
            ));
        }

        // For Role 3, just return the view (existing logic is in blade)
        return view('dashboard');
    }
}
