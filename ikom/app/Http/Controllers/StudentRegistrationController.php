<?php

namespace App\Http\Controllers;

use App\Models\pelajar;
use App\Models\penyelarassig;
use App\Models\PendaftaranPelajar;
use App\Models\kursus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentRegistrationController extends Controller
{
    /**
     * Resolve the active session, returning null if none is active.
     */
    private function getActiveSesi(): ?kursus
    {
        return kursus::getActive();
    }

    /**
     * Display the registration page with student list for this session.
     */
    public function index()
    {
        $penyelaras = penyelarassig::where('fld_user_id', Auth::id())->first();
        $sesiAktif  = $this->getActiveSesi();
        $registeredStudents = collect();

        if ($penyelaras && $penyelaras->fld_sig_id && $sesiAktif) {
            // Load students enrolled in THIS session for THIS SIG
            $registeredStudents = PendaftaranPelajar::with('pelajar.pengguna')
                ->where('fld_sig_id', $penyelaras->fld_sig_id)
                ->where('fld_krs_id', $sesiAktif->fld_krs_id)
                ->get()
                ->map(fn($d) => $d->pelajar)
                ->filter();
        }

        return view('studentRegistration', compact('registeredStudents', 'sesiAktif'));
    }

    /**
     * Fetch individual student info for the read-only preview form.
     */
    public function fetchStudent(Request $request)
    {
        $matric  = $request->input('matric_number');
        $pelajar = pelajar::with('pengguna')->where('fld_pel_nomat', $matric)->first();

        if ($pelajar) {
            $picUrl = asset('pic/' . $pelajar->fld_pel_nomat . '.jpg');

            return response()->json([
                'success' => true,
                'data'    => [
                    'matric_number' => $pelajar->fld_pel_nomat,
                    'name'          => $pelajar->pengguna?->fld_user_nama ?? 'No Name',
                    'email'         => $pelajar->pengguna?->fld_user_email ?? '',
                    'program'       => $pelajar->fld_pel_jurusan,
                    'year'          => $pelajar->fld_pel_tahun,
                    'pic'           => $picUrl,
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Pelajar tidak ditemui dalam sistem.']);
    }

    /**
     * Enroll an individual student into the active session for this SIG.
     */
    public function registerIndividual(Request $request)
    {
        $matric  = $request->input('matric_number');
        $pelajar = pelajar::where('fld_pel_nomat', $matric)->first();

        if (!$pelajar) {
            return response()->json(['success' => false, 'message' => 'Pelajar tidak ditemui']);
        }

        $sesiAktif = $this->getActiveSesi();
        if (!$sesiAktif) {
            return response()->json(['success' => false, 'message' => 'Tiada sesi kursus aktif. Sila hubungi Penyelaras Kursus.']);
        }

        $penyelaras = penyelarassig::where('fld_user_id', Auth::id())->first();
        if (!$penyelaras || !$penyelaras->fld_sig_id) {
            return response()->json(['success' => false, 'message' => 'Akaun anda tidak dikaitkan dengan mana-mana SIG.']);
        }

        // Check if already enrolled in this session
        $alreadyEnrolled = PendaftaranPelajar::where('fld_pel_nomat', $matric)
            ->where('fld_krs_id', $sesiAktif->fld_krs_id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json(['success' => false, 'message' => 'Pelajar sudah didaftarkan untuk sesi ini.']);
        }

        PendaftaranPelajar::create([
            'fld_pel_nomat' => $matric,
            'fld_krs_id'    => $sesiAktif->fld_krs_id,
            'fld_sig_id'    => $penyelaras->fld_sig_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pelajar berjaya didaftarkan untuk sesi ' . $sesiAktif->fld_krs_nama . '.',
            'data'    => [
                'matric_number' => $pelajar->fld_pel_nomat,
                'name'          => $pelajar->pengguna?->fld_user_nama ?? 'No Name',
                'program'       => $pelajar->fld_pel_jurusan,
                'status'        => 'Registered'
            ]
        ]);
    }

    /**
     * Bulk enroll students from a CSV file into the active session.
     */
    public function registerBulk(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);

        $sesiAktif = $this->getActiveSesi();
        if (!$sesiAktif) {
            return response()->json(['success' => false, 'message' => 'Tiada sesi kursus aktif. Sila hubungi Penyelaras Kursus.']);
        }

        $penyelaras = penyelarassig::where('fld_user_id', Auth::id())->first();
        if (!$penyelaras || !$penyelaras->fld_sig_id) {
            return response()->json(['success' => false, 'message' => 'Akaun anda tidak dikaitkan dengan mana-mana SIG.']);
        }

        $file       = $request->file('csv_file');
        $fileHandle = fopen($file->getRealPath(), 'r');

        $registeredStudents = [];
        $errors             = [];

        while (($row = fgetcsv($fileHandle, 1000, ',')) !== false) {
            $matric = trim($row[0]);
            if (empty($matric)) continue;

            $pelajar = pelajar::with('pengguna')->where('fld_pel_nomat', $matric)->first();

            if (!$pelajar) {
                $errors[] = $matric . ' (Tidak ditemui dalam sistem)';
                continue;
            }

            $alreadyEnrolled = PendaftaranPelajar::where('fld_pel_nomat', $matric)
                ->where('fld_krs_id', $sesiAktif->fld_krs_id)
                ->exists();

            if ($alreadyEnrolled) {
                $errors[] = $matric . ' (Sudah didaftarkan untuk sesi ini)';
                continue;
            }

            PendaftaranPelajar::create([
                'fld_pel_nomat' => $matric,
                'fld_krs_id'    => $sesiAktif->fld_krs_id,
                'fld_sig_id'    => $penyelaras->fld_sig_id,
            ]);

            $registeredStudents[] = [
                'matric_number' => $pelajar->fld_pel_nomat,
                'name'          => $pelajar->pengguna?->fld_user_nama ?? 'No Name',
                'program'       => $pelajar->fld_pel_jurusan,
                'status'        => 'Registered'
            ];
        }

        fclose($fileHandle);

        return response()->json([
            'success'    => true,
            'registered' => $registeredStudents,
            'errors'     => $errors
        ]);
    }
}
