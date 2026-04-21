<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\kursus;

class CourseregController extends Controller
{
    public function index()
    {
        $courses = kursus::all();
        return view('coursereg', compact('courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kursus' => 'required|in:Inovasi Digital,Komuniti Digital',
            'semester' => 'required|in:Semester 1,Semester 2',
            'tahun' => 'required',
        ]);

        $exists = kursus::where('fld_krs_nama', $request->nama_kursus)
            ->where('fld_krs_semester', $request->semester)
            ->where('fld_krs_tahun', $request->tahun)
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'Pendaftaran gagal. Kursus sudah wujud.');
        }

        $kursus = new kursus();
        $kursus->fld_krs_nama = $request->nama_kursus;
        $kursus->fld_krs_semester = $request->semester;
        $kursus->fld_krs_tahun = $request->tahun;
        $kursus->save();

        return redirect()->route('coursereg')->with('success', 'Pendaftaran kursus berjaya disimpan.');
    }
}