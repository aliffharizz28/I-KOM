<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\tugasan;
use App\Models\penyelarassig;
use App\Models\subkriteria;
use App\Models\kursus;
use App\Models\PendaftaranPelajar;
use Illuminate\Support\Facades\Auth;

class tugasanController extends Controller
{
    public function index()
    {
        $userId     = Auth::user()->fld_user_id;
        $penyelaras = penyelarassig::where('fld_user_id', $userId)->first();
        $sesiAktif  = kursus::getActive();
        $tugasans   = collect();

        if ($penyelaras && $sesiAktif) {
            // Auto-deactivate past-due tugasan for this SIG & session
            tugasan::where('fld_sig_id', $penyelaras->fld_sig_id)
                   ->where('fld_krs_id', $sesiAktif->fld_krs_id)
                   ->where('fld_tgs_status', 'Aktif')
                   ->whereDate('fld_tgs_tarikh', '<', \Carbon\Carbon::today())
                   ->update(['fld_tgs_status' => 'Tidak Aktif']);

            $tugasans = tugasan::withCount('penghantaran')
                               ->where('fld_sig_id', $penyelaras->fld_sig_id)
                               ->where('fld_krs_id', $sesiAktif->fld_krs_id)
                               ->get();
        }

        return view('tugasan', compact('tugasans', 'sesiAktif'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tugasan_title' => 'required|string|max:255',
            'tugasan_desc'  => 'required|string',
            'due_date'      => 'required|date',
            'tugasan_jenis' => 'required|in:Individu,Berkumpulan',
            'tugasan_file'  => 'nullable|file|mimes:pdf,doc,docx,zip,jpeg,png,jpg|max:5120',
        ]);

        $userId     = Auth::id();
        $penyelaras = penyelarassig::where('fld_user_id', $userId)->first();
        $sesiAktif  = kursus::getActive();

        if (!$penyelaras) {
            return back()->with('error', 'Akaun anda tidak dikaitkan dengan mana-mana SIG.');
        }

        if (!$sesiAktif) {
            return back()->with('error', 'Tiada sesi kursus aktif. Sila hubungi Penyelaras Kursus.');
        }

        try {
            $savedTugasan = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $penyelaras, $sesiAktif) {
                $tugasan = new tugasan();
                $tugasan->fld_tgs_nama   = $request->tugasan_title;
                $tugasan->fld_tgs_desc   = $request->tugasan_desc;
                $tugasan->fld_tgs_tarikh = $request->due_date;
                $tugasan->fld_tgs_jenis  = $request->tugasan_jenis;
                $tugasan->fld_sig_id     = $penyelaras->fld_sig_id;
                $tugasan->fld_krs_id     = $sesiAktif->fld_krs_id;

                $tugasan->fld_tgs_status = \Carbon\Carbon::parse($request->due_date)->startOfDay()->lt(\Carbon\Carbon::today())
                    ? 'Tidak Aktif' : 'Aktif';

                if ($request->hasFile('tugasan_file')) {
                    $uploadDir = public_path('lampiran_tugasan');
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $file     = $request->file('tugasan_file');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move($uploadDir, $filename);
                    $tugasan->fld_tgs_file = $filename;
                }

                $tugasan->is_published = 0;
                $tugasan->save();

                return $tugasan;
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Tugasan Store Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan tugasan. Sila cuba lagi.');
        }

        // Create global subkriteria entry separately (non-critical, won't fail the assignment)
        try {
            $existingSub = \App\Models\subkriteria::where('fld_sub_nama', $request->tugasan_title)->first();
            if (!$existingSub) {
                \App\Models\subkriteria::create([
                    'fld_sub_nama'  => $request->tugasan_title,
                    'fld_sub_markah' => null,
                    'fld_krit_id'   => null,
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Subkriteria auto-create skipped: ' . $e->getMessage());
        }

        return redirect()->route('tugasan')->with('success', 'Tugasan berjaya ditambah (Status: Disembunyikan).');
    }

    public function togglePublish(Request $request, $id)
    {
        $tugasan     = tugasan::findOrFail($id);
        $wasPublished = $tugasan->is_published;

        $tugasan->is_published = !$wasPublished;
        $tugasan->save();

        if ($tugasan->is_published) {
            // Notify only students enrolled in this session for this SIG
            $sesiAktif = kursus::getActive();
            $pelajarIds = $sesiAktif
                ? PendaftaranPelajar::where('fld_sig_id', $tugasan->fld_sig_id)
                    ->where('fld_krs_id', $sesiAktif->fld_krs_id)
                    ->pluck('fld_pel_nomat')
                : collect();

            \Illuminate\Support\Facades\Log::info('[togglePublish] Publishing tugasan ID=' . $tugasan->fld_tgs_id .
                ' | SIG=' . $tugasan->fld_sig_id .
                ' | KRS=' . ($sesiAktif ? $sesiAktif->fld_krs_id : 'null') .
                ' | Target pelajar count=' . $pelajarIds->count() .
                ' | MAIL_MAILER=' . config('mail.default'));

            $pelajars = \App\Models\pelajar::with('pengguna')
                ->whereIn('fld_pel_nomat', $pelajarIds)
                ->get();

            \Illuminate\Support\Facades\Log::info('[togglePublish] Pelajar loaded: ' . $pelajars->count());

            $sent   = 0;
            $failed = 0;

            foreach ($pelajars as $pel) {
                if ($pel->pengguna && !empty($pel->pengguna->fld_user_email)) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($pel->pengguna->fld_user_email)
                            ->send(new \App\Mail\NewAssignmentMail($tugasan));
                        \Illuminate\Support\Facades\Log::info('[togglePublish] Mail sent to: ' . $pel->pengguna->fld_user_email);
                        $sent++;
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('[togglePublish] Failed to send mail to ' .
                            $pel->pengguna->fld_user_email . ': ' . $e->getMessage());
                        $failed++;
                    }
                } else {
                    \Illuminate\Support\Facades\Log::warning('[togglePublish] Skipped pelajar ' .
                        $pel->fld_pel_nomat . ' — no email address found.');
                }
            }

            $msg = "Tugasan telah disiarkan. Emel dihantar: {$sent}";
            if ($failed > 0) {
                $msg .= ", Gagal: {$failed} (semak log)";
            }
            return back()->with('success', $msg);
        }

        return back()->with('success', 'Tugasan telah disembunyikan daripada pelajar.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tugasan_title' => 'required|string|max:255',
            'tugasan_desc' => 'required|string',
            'due_date' => 'required|date',
            'tugasan_jenis' => 'required|in:Individu,Berkumpulan',
            'tugasan_file' => 'nullable|file|mimes:pdf,doc,docx,zip,jpeg,png,jpg|max:5120',
        ]);

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id) {
                $tugasan = tugasan::findOrFail($id);
                $oldTitle = $tugasan->fld_tgs_nama;

                $tugasan->fld_tgs_nama = $request->tugasan_title;
                $tugasan->fld_tgs_desc = $request->tugasan_desc;
                $tugasan->fld_tgs_tarikh = $request->due_date;
                $tugasan->fld_tgs_jenis = $request->tugasan_jenis;

                // Automatically determine status based on updated due date
                if (\Carbon\Carbon::parse($request->due_date)->startOfDay()->lt(\Carbon\Carbon::today())) {
                    $tugasan->fld_tgs_status = 'Tidak Aktif';
                } else {
                    $tugasan->fld_tgs_status = 'Aktif';
                }

                if ($request->hasFile('tugasan_file')) {
                    $file = $request->file('tugasan_file');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('lampiran_tugasan'), $filename);
                    $tugasan->fld_tgs_file = $filename;
                }

                $tugasan->save();

                // Sync with subkriteria table if title changed
                if ($oldTitle !== $request->tugasan_title) {
                    subkriteria::where('fld_sub_nama', $oldTitle)
                        ->update(['fld_sub_nama' => $request->tugasan_title]);
                }

                return redirect()->route('tugasan')->with('success', 'Tugasan berjaya dikemaskini!');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Tugasan Update Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal mengemaskini tugasan. Sila cuba lagi.');
        }
    }

    public function destroy($id)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
                $tugasan = tugasan::findOrFail($id);
                $titleToDelete = $tugasan->fld_tgs_nama;

                $tugasan->delete();

                // Also delete matching subkriteria entry by name
                subkriteria::where('fld_sub_nama', $titleToDelete)->delete();
            });

            return redirect()->route('tugasan')->with('success', 'Tugasan berjaya dipadam!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Tugasan Delete Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memadam tugasan. Sila cuba lagi.');
        }
    }
}
