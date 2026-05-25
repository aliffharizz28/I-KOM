<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\kriteria;
use App\Models\subkriteria;
use App\Models\subkriteriaDesc;
use App\Models\SigSubkriteria;
use App\Models\penyelarassig;

class subkriteriaController extends Controller
{
    /**
     * Get the SIG ID of the cur4
     * recently logged-in penyelaras.
     */
    private function getCurrentSigId(): ?string
    {
        $penyelaras = penyelarassig::where('fld_user_id', Auth::id())->first();
        return $penyelaras?->fld_sig_id;
    }

    public function index()
    {
        $sigId = $this->getCurrentSigId();

        // Load all kriteria; for each kriteria also eager-load this SIG's allocations
        $kriterias = kriteria::with(['sigSubkriteria' => function ($q) use ($sigId) {
            $q->where('fld_sig_id', $sigId)->with('subkriteria.descriptions');
        }])->get();

        // All available subkriteria as the global pool for the dropdown
        $subkriterias = subkriteria::with('descriptions')->get();

        return view('subkriteria', compact('kriterias', 'subkriterias', 'sigId'));
    }

    public function store(Request $request)
    {
        $sigId = $this->getCurrentSigId();

        if (!$sigId) {
            return redirect()->back()->withErrors('Akaun anda tidak dikaitkan dengan mana-mana SIG.');
        }

        // Build a map: krit_id => [sub_ids still in the form]
        $submittedSubsByKrit = [];

        // Seed from active_krit_ids so kriteria with ZERO rows is still processed
        foreach ($request->input('active_krit_ids', []) as $krit_id) {
            if (!isset($submittedSubsByKrit[$krit_id])) {
                $submittedSubsByKrit[$krit_id] = [];
            }
        }

        // Populate with actually submitted sub_ids & markah
        $submittedRows = []; // [ [krit_id, sub_id, markah], ... ]
        if ($request->has('krit_id')) {
            foreach ($request->krit_id as $index => $krit_id) {
                $sub_id = $request->sub_id[$index] ?? null;
                $markah = $request->markah[$index] ?? 0;
                if (!empty($krit_id) && !empty($sub_id)) {
                    $submittedSubsByKrit[$krit_id][] = $sub_id;
                    $submittedRows[] = compact('krit_id', 'sub_id', 'markah');
                }
            }
        }

        // Step 1: For each kriteria visible in the form, delete the SIG's old allocations
        // that are no longer submitted (handles deleted rows AND fully-cleared kriteria).
        foreach ($submittedSubsByKrit as $krit_id => $submittedSubIds) {
            $submittedSubIds = array_values(array_filter($submittedSubIds));

            $toDelete = SigSubkriteria::where('fld_sig_id', $sigId)
                ->where('fld_krit_id', $krit_id)
                ->whereNotIn('fld_sub_id', $submittedSubIds)
                ->pluck('fld_sub_id')
                ->toArray();

            if (!empty($toDelete)) {
                SigSubkriteria::where('fld_sig_id', $sigId)
                    ->where('fld_krit_id', $krit_id)
                    ->whereIn('fld_sub_id', $toDelete)
                    ->delete();
            }
        }

        // Step 2: Upsert the remaining submitted rows (insert new, update existing markah)
        foreach ($submittedRows as $row) {
            SigSubkriteria::updateOrCreate(
                [
                    'fld_sig_id'  => $sigId,
                    'fld_krit_id' => $row['krit_id'],
                    'fld_sub_id'  => $row['sub_id'],
                ],
                [
                    'fld_sub_markah' => $row['markah'],
                ]
            );
        }

        return redirect()->back()->with('success', 'Perubahan subkriteria berjaya disimpan.');
    }

    public function createSubkriteria(Request $request)
    {
        $request->validate([
            'fld_sub_nama' => 'required|string|max:255|unique:subkriteria,fld_sub_nama',
            'descriptions' => 'required|array|min:1',
            'descriptions.*.text' => 'required|string|max:500',
            'descriptions.*.markah' => 'required|integer|min:1|max:100',
        ], [
            'fld_sub_nama.required' => 'Nama subkriteria diperlukan.',
            'fld_sub_nama.unique' => 'Subkriteria ini sudah wujud.',
            'fld_sub_nama.max' => 'Nama subkriteria tidak boleh melebihi 255 aksara.',
            'descriptions.required' => 'Sila tambah sekurang-kurangnya satu penerangan.',
            'descriptions.min' => 'Sila tambah sekurang-kurangnya satu penerangan.',
            'descriptions.*.text.required' => 'Penerangan tidak boleh kosong.',
            'descriptions.*.markah.required' => 'Markah penerangan diperlukan.',
            'descriptions.*.markah.min' => 'Markah mestilah sekurang-kurangnya 1.',
        ]);

        $sub = subkriteria::create([
            'fld_sub_nama' => $request->fld_sub_nama,
        ]);

        // Create description items
        foreach ($request->descriptions as $desc) {
            subkriteriaDesc::create([
                'fld_sub_id'      => $sub->fld_sub_id,
                'fld_desc_text'   => $desc['text'],
                'fld_desc_markah' => $desc['markah'],
            ]);
        }

        // Reload with descriptions
        $sub->load('descriptions');

        return response()->json([
            'success'     => true,
            'message'     => 'Subkriteria berjaya dicipta!',
            'subkriteria' => $sub,
        ]);
    }
}
