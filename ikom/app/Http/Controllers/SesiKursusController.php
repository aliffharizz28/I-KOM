<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\kursus;
use App\Models\penyelaraskursus;

class SesiKursusController extends Controller
{
    /**
     * Show the Tetapan Semester page.
     * Displays all past sessions and lets Penyelaras Kursus activate one.
     */
    public function index()
    {
        $sesiList = kursus::orderBy('fld_krs_tahun', 'desc')
                          ->orderBy('fld_krs_semester', 'desc')
                          ->get();

        $sesiAktif = kursus::getActive();

        return view('sesiKursus', compact('sesiList', 'sesiAktif'));
    }

    /**
     * Create a new session record and optionally activate it immediately.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kursus' => 'required|in:Inovasi Digital,Komuniti Digital',
            'semester'    => 'required|in:Semester 1,Semester 2',
            'tahun'       => 'required',
        ]);

        $exists = kursus::where('fld_krs_nama', $request->nama_kursus)
            ->where('fld_krs_semester', $request->semester)
            ->where('fld_krs_tahun', $request->tahun)
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()
                ->with('error', 'Sesi ini sudah wujud dalam sistem.');
        }

        $sesi = kursus::create([
            'fld_krs_nama'     => $request->nama_kursus,
            'fld_krs_semester' => $request->semester,
            'fld_krs_tahun'    => $request->tahun,
            'fld_krs_aktif'    => false,
        ]);

        return redirect()->route('sesiKursus.index')
            ->with('success', 'Sesi kursus berjaya dicipta. Sila aktifkan sesi bila bersedia.');
    }

    /**
     * Activate a specific session.
     * Deactivates all others first, then links the Penyelaras Kursus to this session.
     */
    public function aktif(Request $request, $id)
    {
        $sesi = kursus::findOrFail($id);

        // Deactivate every other session
        kursus::where('fld_krs_aktif', true)->update(['fld_krs_aktif' => false]);

        // Activate the chosen session
        $sesi->fld_krs_aktif = true;
        $sesi->save();

        // Link the currently logged-in Penyelaras Kursus to this session
        $user = Auth::user();
        penyelaraskursus::updateOrCreate(
            ['fld_user_id' => $user->fld_user_id],
            ['fld_krs_id'  => $sesi->fld_krs_id]
        );

        return redirect()->route('sesiKursus.index')
            ->with('success', "Sesi \"{$sesi->fld_krs_nama} – {$sesi->fld_krs_semester} {$sesi->fld_krs_tahun}\" kini aktif.");
    }
}
