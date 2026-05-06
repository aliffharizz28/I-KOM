<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\kriteria;
use App\Models\subkriteria;
use App\Models\subkriteriaDesc;

class subkriteriaController extends Controller
{
    public function index()
    {
        $kriterias = kriteria::with('subkriteria.descriptions')->get();
        $subkriterias = subkriteria::with('descriptions')->get();
        return view('subkriteria', compact('kriterias', 'subkriterias'));
    }

    public function store(Request $request)
    {
        if ($request->has('sub_id')) {
            foreach ($request->sub_id as $index => $sub_id) {
                if (!empty($sub_id)) {
                    $krit_id = $request->krit_id[$index];
                    $markah = $request->markah[$index];

                    $sub = subkriteria::where('fld_sub_id', $sub_id)->first();
                    if ($sub) {
                        $sub->fld_krit_id = $krit_id;
                        $sub->fld_sub_markah = $markah;
                        $sub->save();
                    }
                }
            }
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
                'fld_sub_id' => $sub->fld_sub_id,
                'fld_desc_text' => $desc['text'],
                'fld_desc_markah' => $desc['markah'],
            ]);
        }

        // Reload with descriptions
        $sub->load('descriptions');

        return response()->json([
            'success' => true,
            'message' => 'Subkriteria berjaya dicipta!',
            'subkriteria' => $sub,
        ]);
    }
}
