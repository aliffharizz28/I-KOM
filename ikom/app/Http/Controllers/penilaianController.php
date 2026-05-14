<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\pelajar;
use App\Models\penilaian;
use App\Models\keputusan;
use App\Models\penyelarassig;
use App\Models\kriteria;
use App\Models\SigSubkriteria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class penilaianController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get the lecturer's SIG ID via penyelarassig table
        $penyelaras = penyelarassig::where('fld_user_id', $user->fld_user_id)->first();

        if (!$penyelaras) {
            return view('penilaian', [
                'pelajars' => collect(),
                'sigNama' => 'Tiada SIG',
                'sigLogo' => null,
                'sigId' => null,
            ]);
        }

        $sigId = $penyelaras->fld_sig_id;

        // Get all students in the same SIG, eager-load the pengguna (user) relationship for names
        $pelajars = pelajar::with('pengguna')
            ->where('fld_sig_id', $sigId)
            ->get();

        // Get the SIG details
        $sig = $penyelaras->sig;
        $sigNama = $sig ? $sig->fld_sig_nama : 'SIG';
        $sigLogo = $sig && $sig->fld_sig_logo
            ? str_replace('\\', '/', preg_replace('#^public[\\\\/]#', '', $sig->fld_sig_logo))
            : null;
        $publishStatus = $sig ? $sig->fld_publish_status : 0;

        return view('penilaian', compact('pelajars', 'sigNama', 'sigLogo', 'sigId', 'publishStatus'));
    }

    public function markah($nomat)
    {
        $user = Auth::user();
        $penyelaras = penyelarassig::where('fld_user_id', $user->fld_user_id)->first();

        if (!$penyelaras) {
            return redirect()->route('penilaian')->with('error', 'Anda tidak mempunyai SIG.');
        }

        // Find the student and ensure they belong to the same SIG
        $pelajar = pelajar::with('pengguna')
            ->where('fld_pel_nomat', $nomat)
            ->where('fld_sig_id', $penyelaras->fld_sig_id)
            ->firstOrFail();

        // Get SIG details
        $sig = $penyelaras->sig;
        $sigNama = $sig ? $sig->fld_sig_nama : 'SIG';
        $sigId = $penyelaras->fld_sig_id;

        // Get all criteria with their SIG-specific subkriteria + descriptions
        $kriterias = kriteria::with(['sigSubkriteria' => function($q) use ($sigId) {
            $q->where('fld_sig_id', $sigId)->with('subkriteria.descriptions');
        }])->get();

        // Map sigSubkriteria to subkriteria for view compatibility
        foreach ($kriterias as $k) {
            $k->subkriteria = $k->sigSubkriteria->map(function($sigSub) {
                $sub = $sigSub->subkriteria;
                if ($sub) {
                    $sub->fld_sub_markah = $sigSub->fld_sub_markah;
                }
                return $sub;
            })->filter();
        }

        // Load existing marks for this student from the JSON detail fields
        $penilaianRows = penilaian::where('fld_pel_nomat', $nomat)
            ->whereNotNull('fld_krit_id')
            ->get();
            
        $existingMarks = [];
        foreach ($penilaianRows as $row) {
            $details = $row->fld_markah_detail; // Casted as array in model
            if (is_array($details)) {
                foreach ($details as $descId => $mark) {
                    $existingMarks[$descId] = $mark;
                }
            }
        }

        $keputusan = keputusan::where('fld_pel_nomat', $nomat)->first();
        $existingKomen = $keputusan ? $keputusan->fld_nilai_komen : '';

        // Fetch assignment marks (penghantaran) mapped to subkriteria
        $tugasans = \App\Models\tugasan::where('fld_sig_id', $sigId)->get();
        $penghantarans = \App\Models\penghantaran::where('fld_pel_nomat', $nomat)->get()->keyBy('fld_tgs_id');
        $assignmentScores = [];
        
        // Fetch attendance score
        $attendancePercentage = $pelajar->peratusan_kehadiran;
        
        foreach ($kriterias as $k) {
            foreach ($k->subkriteria as $sub) {
                if (strtolower(trim($sub->fld_sub_nama)) === 'kehadiran') {
                    $assignmentScores[$sub->fld_sub_id] = [
                        'score' => $attendancePercentage,
                        'max' => 100,
                        'is_attendance' => true
                    ];
                    continue;
                }

                $matchedTgs = $tugasans->firstWhere('fld_tgs_nama', $sub->fld_sub_nama);
                if ($matchedTgs) {
                    $pgh = $penghantarans->get($matchedTgs->fld_tgs_id);
                    $score = $pgh ? floatval($pgh->fld_pgh_markah) : 0;
                    $assignmentScores[$sub->fld_sub_id] = [
                        'score' => $score,
                        'max' => 10,
                        'is_attendance' => false
                    ];
                }
            }
        }

        return view('penilaianMarkah', compact('pelajar', 'kriterias', 'sigNama', 'sigId', 'existingMarks', 'existingKomen', 'assignmentScores'));
    }

    /**
     * Save marks for a student.
     * Each description input is saved as a row in the penilaian table.
     * The kriteria-level percentage is calculated server-side.
     */
    public function simpan(Request $request, $nomat)
    {
        $user = Auth::user();
        $penyelaras = penyelarassig::where('fld_user_id', $user->fld_user_id)->first();

        if (!$penyelaras) {
            return response()->json(['success' => false, 'message' => 'Anda tidak mempunyai SIG.'], 403);
        }

        $sigId = $penyelaras->fld_sig_id;

        // Ensure the student belongs to the same SIG
        $pelajar = pelajar::where('fld_pel_nomat', $nomat)
            ->where('fld_sig_id', $sigId)
            ->first();

        if (!$pelajar) {
            return response()->json(['success' => false, 'message' => 'Pelajar tidak dijumpai dalam SIG anda.'], 404);
        }

        $marks = $request->input('marks', []);
        $komen = $request->input('komen', '');

        // Load all kriterias with SIG-specific subkriteria and descriptions for percentage calculation
        $kriterias = kriteria::with(['sigSubkriteria' => function($q) use ($sigId) {
            $q->where('fld_sig_id', $sigId)->with('subkriteria.descriptions');
        }])->get();

        // Map and key by kriteria ID
        foreach ($kriterias as $k) {
            $k->subkriteria = $k->sigSubkriteria->map(function($sigSub) {
                $sub = $sigSub->subkriteria;
                if ($sub) {
                    $sub->fld_sub_markah = $sigSub->fld_sub_markah;
                }
                return $sub;
            })->filter();
        }
        $kriterias = $kriterias->keyBy('fld_krit_id');

        // Fetch assignment marks (penghantaran) mapped to subkriteria
        $tugasans = \App\Models\tugasan::where('fld_sig_id', $sigId)->get();
        $penghantarans = \App\Models\penghantaran::where('fld_pel_nomat', $nomat)->get()->keyBy('fld_tgs_id');

        DB::beginTransaction();
        try {
            // Group marks by krit_id
            $marksByKriteria = [];
            foreach ($marks as $mark) {
                $kritId = $mark['krit_id'];
                if (!isset($marksByKriteria[$kritId])) {
                    $marksByKriteria[$kritId] = [];
                }
                $marksByKriteria[$kritId][$mark['desc_id']] = floatval($mark['markah']);
            }

            // Remove old marks for this student to maintain the 7-row flat structure
            penilaian::where('fld_pel_nomat', $nomat)->delete();

            $overallScore = 0;

            foreach ($kriterias as $kriteria) {
                $kritId = $kriteria->fld_krit_id;
                $kriteriaEarned = 0;
                $descMarksForKriteria = $marksByKriteria[$kritId] ?? [];

                foreach ($kriteria->subkriteria as $sub) {
                    $subWeight = $sub->fld_sub_markah; 
                    
                    $attendancePercentage = $pelajar->peratusan_kehadiran;
                    $matchedTgs = $tugasans->firstWhere('fld_tgs_nama', $sub->fld_sub_nama);

                    if (strtolower(trim($sub->fld_sub_nama)) === 'kehadiran') {
                        // Attendance is out of 100%
                        $subPercentage = ($attendancePercentage / 100) * $subWeight;
                    } else if ($matchedTgs) {
                        // It is an assignment. Pull mark from penghantaran.
                        $pgh = $penghantarans->get($matchedTgs->fld_tgs_id);
                        $tgsScore = $pgh ? floatval($pgh->fld_pgh_markah) : 0;
                        
                        // Assignment is out of 10
                        $subPercentage = ($tgsScore / 10) * $subWeight;
                    } else {
                        // It is a standard subkriteria with descriptions
                        $descTotalMax = 0;
                        $descTotalEarned = 0;

                        foreach ($sub->descriptions as $desc) {
                            $descTotalMax += $desc->fld_desc_markah;
                            $descId = $desc->fld_desc_id;
                            
                            if (isset($descMarksForKriteria[$descId])) {
                                $descTotalEarned += $descMarksForKriteria[$descId];
                            }
                        }

                        if ($descTotalMax > 0) {
                            $subPercentage = ($descTotalEarned / $descTotalMax) * $subWeight;
                        } else {
                            $subPercentage = 0;
                        }
                    }

                    $kriteriaEarned += $subPercentage;
                }

                $overallScore += $kriteriaEarned;

                // Save ONE row per kriteria, injecting the raw marks into fld_markah_detail
                penilaian::create([
                    'fld_pel_nomat' => $nomat,
                    'fld_krit_id' => $kritId,
                    'fld_nilai_markah' => round($kriteriaEarned, 2),
                    'fld_sig_id' => $sigId,
                    'fld_markah_detail' => empty($descMarksForKriteria) ? null : $descMarksForKriteria,
                ]);
            }

            // Determine grade based on overall score
            $grade = $this->calculateGrade($overallScore);

            // Save the overall result and comment to the keputusan table
            keputusan::updateOrCreate(
                ['fld_pel_nomat' => $nomat],
                [
                    'fld_total_markah' => round($overallScore, 2),
                    'fld_nilai_gred' => $grade,
                    'fld_nilai_komen' => $komen,
                    'fld_sig_id' => $sigId,
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Markah berjaya disimpan!',
                'overallScore' => round($overallScore, 2),
                'grade' => $grade,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ralat berlaku: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate grade based on overall score
     */
    private function calculateGrade($score)
    {
        if ($score >= 90) return 'A+';
        if ($score >= 80) return 'A';
        if ($score >= 75) return 'A-';
        if ($score >= 70) return 'B+';
        if ($score >= 65) return 'B';
        if ($score >= 60) return 'B-';
        if ($score >= 55) return 'C+';
        if ($score >= 50) return 'C';
        if ($score >= 45) return 'C-';
        if ($score >= 40) return 'D';
        return 'F';
    }

    public function updatePublishStatus(Request $request)
    {
        $user = Auth::user();
        $penyelaras = penyelarassig::where('fld_user_id', $user->fld_user_id)->first();
        if (!$penyelaras || !$penyelaras->sig) {
            return response()->json(['success' => false, 'message' => 'Anda tidak mempunyai SIG.']);
        }

        $sig = $penyelaras->sig;
        $sig->fld_publish_status = $request->status;
        $sig->save();

        return response()->json(['success' => true]);
    }

    public function exportCSV()
    {
        $user = Auth::user();
        if ($user->fld_user_role != 2) {
            return redirect()->route('penilaian')->with('error', 'Akses ditolak.');
        }

        $penyelaras = penyelarassig::where('fld_user_id', $user->fld_user_id)->first();
        if (!$penyelaras || !$penyelaras->sig) {
            return redirect()->route('penilaian')->with('error', 'Kumpulan SIG tidak ditemui.');
        }

        $sigId = $penyelaras->fld_sig_id;
        $sigName = $penyelaras->sig->fld_sig_nama;
        $fileName = 'Laporan_Penilaian_' . str_replace(' ', '_', $sigName) . '_' . date('Ymd_His') . '.csv';

        // Get students with necessary relationships
        $pelajars = pelajar::with(['pengguna', 'penilaian'])
                           ->where('fld_sig_id', $sigId)
                           ->orderBy('fld_pel_nomat', 'asc')
                           ->get();
                           
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
                $columns[] = $k->fld_krit_nama .  '(' . $k->fld_krit_markah . '%)';
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
}
