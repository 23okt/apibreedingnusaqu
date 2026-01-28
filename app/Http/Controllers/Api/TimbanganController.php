<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Timbangan;
use App\Models\Goats;
use Illuminate\Support\Facades\Validator;

class TimbanganController extends Controller
{
    public function index()
    {
        $timbangan = Timbangan::with('goats')->orderBy('created_at', 'desc')->get();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data timbangan berhasil di tampilkan',
                'data' => $timbangan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data timbangan gagal di tampilkan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $rand_number = rand(100, 999);

        $validator = Validator::make($request->all(), [
            'bobot' => 'required|int|min:0',
            'product_id' => 'required|exists:product,kode_product',
            'tanggal' => 'required|date',
        ]);
        try {
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Failed to add data timbangan',
                    'error' => $validator->errors()
                ], 422);
            }
            
    
            $result = $validator->validated();
            $product = Goats::where('kode_product', $result['product_id'])->firstOrFail();
            $result['product_id'] = $product->id_product;
    
            // Simpan data timbangan baru
            $timbangan = Timbangan::create(array_merge($result, [
                'kode_timbangan' => 'TIM-' . $rand_number,
            ]));
    
            // Update kolom weight di tabel data_kambing agar selalu menyimpan bobot terbaru
            $goat = Goats::find($result['product_id']);
            if ($goat) {
                $goat->update([
                    'bobot' => $result['bobot']
                ]);
            }
    
            return response()->json([
                'message' => 'Data timbangan berhasil ditambahkan dan bobot kambing telah diperbarui',
                'data' => $timbangan
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data timbangan gagal ditambahkan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function show($kode_timbangan)
    {
        $timbangan = Timbangan::where('kode_timbangan', $kode_timbangan)->first();

        try {
            if (!$timbangan) {
                return response([
                    'message' => 'ID tidak ditemukan'
                ], 404);
            }
    
            return response([
                'message' => 'Data berhasil ditemukan',
                'data' => $timbangan,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data timbangan gagal ditemukan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $kode_timbangan)
{
    $timbangan = Timbangan::where('kode_timbangan', $kode_timbangan)->first();

    if (!$timbangan) {
        return response()->json([
            'message' => 'ID tidak ditemukan.'
        ], 404);
    }

    try {
        $validated = $request->validate([
            'bobot' => 'required|integer|min:0',
            'product_id' => 'required|exists:product,id_product',
            'tanggal' => 'required|date',
        ]);

        // ✅ UPDATE TIMBANGAN (INI YANG PENTING)
        $timbangan->update([
            'product_id' => $validated['product_id'],
            'bobot' => $validated['bobot'],
            'tanggal' => $validated['tanggal'],
        ]);

        // ✅ OPTIONAL: update bobot kambing
        $goat = Goats::find($validated['product_id']);
        if ($goat) {
            $goat->update([
                'bobot' => $validated['bobot']
            ]);
        }

        return response()->json([
            'message' => 'Data timbangan berhasil diubah.',
            'data' => $timbangan->fresh()
        ], 200);

    } catch (\Throwable $th) {
        return response()->json([
            'success' => false,
            'message' => 'Data timbangan gagal diubah.',
            'error' => $th->getMessage()
        ], 500);
    }
}


    public function destroy($kode_timbangan)
    {   
        $timbangan = Timbangan::where('kode_timbangan', $kode_timbangan)->first();

        try {
            if (!$timbangan) {
                return response([
                    'message' => 'ID tidak ditemukan.'
                ], 404);
            }
    
            $timbangan->delete();
            return response([
                'success' => true,
                'message' => 'Berhasil menghapus data timbangan',
                'data' => $timbangan
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data timbangan gagal dihapus.',
                'data' => $th->getMessage()
            ], 500);
        }
    }
}