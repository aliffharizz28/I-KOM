<?php

namespace App\Http\Controllers;

use App\Models\Pelajar;
use App\Models\penyelarassig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentRegistrationController extends Controller
{
    /**
     * Display the registration page with bulk and individual toggle.
     */
    public function index()
    {
        $penyelaras = penyelarassig::where('fld_user_id', Auth::id())->first();
        $registeredStudents = [];
        
        if ($penyelaras && $penyelaras->fld_sig_id) {
            $registeredStudents = Pelajar::with('pengguna')->where('fld_sig_id', $penyelaras->fld_sig_id)->get();
        }

        return view('studentRegistration', compact('registeredStudents'));
    }

    /**
     * Fetch individual student info for the Read-Only form.
     */
    public function fetchStudent(Request $request)
    {
        $matric = $request->input('matric_number');
        $pelajar = Pelajar::with('pengguna')->where('fld_pel_nomat', $matric)->first();

        if ($pelajar) {
            // Strict directory mapping using the Matric Number
            $picUrl = asset('pic/' . $pelajar->fld_pel_nomat . '.jpg');

            return response()->json([
                'success' => true,
                'data' => [
                    'matric_number' => $pelajar->fld_pel_nomat,
                    'name' => $pelajar->pengguna ? $pelajar->pengguna->fld_user_nama : 'No Name',
                    'email' => $pelajar->pengguna ? $pelajar->pengguna->fld_user_email : '',
                    'program' => $pelajar->fld_pel_jurusan,
                    'year' => $pelajar->fld_pel_tahun,
                    'pic' => $picUrl,
                    'sig_id' => $pelajar->fld_sig_id,
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Student not found']);
    }

    /**
     * Register an individual student to the authenticated user's SIG.
     */
    public function registerIndividual(Request $request)
    {
        $matric = $request->input('matric_number');
        $pelajar = Pelajar::with('pengguna')->where('fld_pel_nomat', $matric)->first();

        if (!$pelajar) {
            return response()->json(['success' => false, 'message' => 'Pelajar tidak ditemui']);
        }

        if ($pelajar->fld_sig_id) {
            return response()->json(['success' => false, 'message' => 'Pelajar sudah didaftarkan ke SIG']);
        }

        $penyelaras = penyelarassig::where('fld_user_id', Auth::id())->first();

        if (!$penyelaras || !$penyelaras->fld_sig_id) {
            return response()->json(['success' => false, 'message' => 'Anda tidak mempunyai SIG']);
        }

        $pelajar->fld_sig_id = $penyelaras->fld_sig_id;
        $pelajar->save();

        return response()->json([
            'success' => true,
            'message' => 'Pelajar berjaya didaftarkan.',
            'data' => [
                'matric_number' => $pelajar->fld_pel_nomat,
                'name' => $pelajar->pengguna ? $pelajar->pengguna->fld_user_nama : 'No Name',
                'program' => $pelajar->fld_pel_jurusan,
                'status' => 'Registered'
            ]
        ]);
    }

    /**
     * Process bulk registration via uploaded CSV.
     */
    public function registerBulk(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        $penyelaras = penyelarassig::where('fld_user_id', Auth::id())->first();

        if (!$penyelaras || !$penyelaras->fld_sig_id) {
            return response()->json(['success' => false, 'message' => 'Anda tidak mempunyai SIG']);
        }

        $file = $request->file('csv_file');
        $fileHandle = fopen($file->getRealPath(), 'r');
        
        $registeredStudents = [];
        $errors = [];

        // Read CSV rows
        while (($row = fgetcsv($fileHandle, 1000, ',')) !== false) {
            $matric = trim($row[0]);
            if (empty($matric)) continue;

            $pelajar = Pelajar::with('pengguna')->where('fld_pel_nomat', $matric)->first();

            if ($pelajar) {
                if (!$pelajar->fld_sig_id) {
                    $pelajar->fld_sig_id = $penyelaras->fld_sig_id;
                    $pelajar->save();
                    
                    $registeredStudents[] = [
                        'matric_number' => $pelajar->fld_pel_nomat,
                        'name' => $pelajar->pengguna ? $pelajar->pengguna->fld_user_nama : 'No Name',
                        'program' => $pelajar->fld_pel_jurusan,
                        'status' => 'Registered'
                    ];
                } else {
                    $errors[] = $matric . ' (Already registered)';
                }
            } else {
                $errors[] = $matric . ' (Not found)';
            }
        }
        
        fclose($fileHandle);

        return response()->json([
            'success' => true,
            'registered' => $registeredStudents,
            'errors' => $errors
        ]);
    }
}
