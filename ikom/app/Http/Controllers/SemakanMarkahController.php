<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\keputusan;
use App\Models\pelajar;
use App\Models\penilaian;

class SemakanMarkahController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Pastikan pengguna adalah pelajar
        if ($user->fld_user_role != 3) {
            return redirect()->route('dashboard');
        }

        $pelajar = pelajar::where('fld_user_id', $user->fld_user_id)->first();
        
        if (!$pelajar) {
            return redirect()->back()->with('error', 'Maklumat pelajar tidak dijumpai.');
        }

        $nomat = $pelajar->fld_pel_nomat;

        $keputusan = keputusan::where('fld_pel_nomat', $nomat)->first();
        
        $sig = $pelajar->sig;
        $publishStatus = $sig ? $sig->fld_publish_status : 0;

        // Dapatkan data markah terperinci mengikut kriteria
        $penilaians = penilaian::with(['kriteria'])
                                ->where('fld_pel_nomat', $nomat)
                                ->get();
                                
        $scorePhase1 = 0;
        $phase1Kriteria = [
            'idea inovasi & pemikiran kritis',
            'perancangan projek',
            'perlaksanaan projek',
            'kerjasama kolaboratif'
        ];

        foreach ($penilaians as $penilaian) {
            if ($penilaian->kriteria) {
                $nama = strtolower(trim($penilaian->kriteria->fld_krit_nama));
                if (in_array($nama, $phase1Kriteria)) {
                    $scorePhase1 += floatval($penilaian->fld_nilai_markah);
                }
            }
        }

        return view('semakanmarkah', compact('pelajar', 'keputusan', 'penilaians', 'publishStatus', 'scorePhase1'));
    }
}
