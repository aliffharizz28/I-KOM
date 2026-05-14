<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\tugasan;
use App\Models\penyelarassig;
use App\Models\subkriteria;
use Illuminate\Support\Facades\Auth;

class tugasanController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->fld_user_id;
        $penyelaras = penyelarassig::where('fld_user_id', $userId)->first();
        $tugasans = collect();
        
        if ($penyelaras) {
            // Check for past due assignments and update their status to "Tidak Aktif"
            tugasan::where('fld_sig_id', $penyelaras->fld_sig_id)
                   ->where('fld_tgs_status', 'Aktif')
                   ->whereDate('fld_tgs_tarikh', '<', \Carbon\Carbon::today())
                   ->update(['fld_tgs_status' => 'Tidak Aktif']);

            $tugasans = tugasan::withCount('penghantaran')
                               ->where('fld_sig_id', $penyelaras->fld_sig_id)
                               ->get();
        }
        
        return view('tugasan', compact('tugasans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tugasan_title' => 'required|string|max:255',
            'tugasan_desc' => 'required|string',
            'due_date' => 'required|date',
            'tugasan_jenis' => 'required|in:Individu,Berkumpulan',
            'tugasan_file' => 'nullable|file|mimes:pdf,doc,docx,zip,jpeg,png,jpg|max:5120',
        ]);

        $userId = Auth::id(); // Use Auth::id() for consistency with primary key type (string)
        $penyelaras = penyelarassig::where('fld_user_id', $userId)->first();
        
        if (!$penyelaras) {
            return back()->with('error', 'Akaun anda tidak dikaitkan dengan mana-mana SIG. Tidak boleh mencipta tugasan.');
        }

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $penyelaras) {
                $tugasan = new tugasan();
                $tugasan->fld_tgs_nama = $request->tugasan_title;
                $tugasan->fld_tgs_desc = $request->tugasan_desc;
                $tugasan->fld_tgs_tarikh = $request->due_date;
                $tugasan->fld_tgs_jenis = $request->tugasan_jenis;
                $tugasan->fld_sig_id = $penyelaras->fld_sig_id;

                // Status automatically determined by due date
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

                $tugasan->is_published = 0;
                $tugasan->save();

                // Check if a subkriteria with this name already exists to avoid duplication
                $existingSub = \App\Models\subkriteria::where('fld_sub_nama', $request->tugasan_title)->first();
                if (!$existingSub) {
                    $subkriteria = new \App\Models\subkriteria();
                    $subkriteria->fld_sub_nama = $request->tugasan_title;
                    // Note: In this system, subkriteria are often linked to SIGs via sig_subkriteria pivot.
                    // For now we just create the global entry if it doesn't exist.
                    $subkriteria->save();
                }

                return redirect()->route('tugasan')->with('success', 'Tugasan berjaya ditambah dan disimpan (Status: Disembunyikan).');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Tugasan Store Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan tugasan. Sila cuba lagi. ' . $e->getMessage());
        }
    }

    public function togglePublish(Request $request, $id)
    {
        $tugasan = tugasan::findOrFail($id);
        $wasPublished = $tugasan->is_published;
        
        $tugasan->is_published = !$wasPublished;
        $tugasan->save();

        if ($tugasan->is_published) {
            // Send email since it's just published
            $pelajars = \App\Models\pelajar::with('pengguna')->where('fld_sig_id', $tugasan->fld_sig_id)->get();
            foreach ($pelajars as $pel) {
                if ($pel->pengguna && !empty($pel->pengguna->fld_user_email)) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($pel->pengguna->fld_user_email)->send(new \App\Mail\NewAssignmentMail($tugasan));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Failed to send mail: ' . $e->getMessage());
                    }
                }
            }
            return back()->with('success', 'Tugasan telah disiarkan. Emel makluman telah dihantar kepada pelajar!');
        } else {
            return back()->with('success', 'Tugasan telah disembunyikan daripada pelajar.');
        }
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
