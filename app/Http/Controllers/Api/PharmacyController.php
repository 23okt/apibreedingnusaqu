<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pharmacy;
use Illuminate\Support\Facades\Validator;

class PharmacyController extends Controller
{
    public function index()
    {
        $pharmacy = Pharmacy::get();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data obat berhasil di tampilkan',
                'data' => $pharmacy,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th,
                'data' => $pharmacy
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $rand_number = rand(000,999);
            $validator = Validator::make($request->all(), [
                'nama_obat' => 'required|string',
                'stock_obat' => 'required|integer',
                'type_obat' => 'required|string',
                'isi_obat' => 'required|integer',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal Menambahkan Obat.',
                    'error' => $validator->errors()
                ], 422);
            }
    
            $result = $validator->validated();
    
            $obat = Pharmacy::create(array_merge($result,[
                'kode_obat' => 'OBT-' . $rand_number,
                'total_obat' => $result['stock_obat'] * $result['isi_obat'],
            ]));
    
            return response()->json([
                'success' => true,
                'message' => 'Berhasil Menambahkan Obat',
                'data' => $obat
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Menambahkan Obat',
                'data' => $th,
            ], 500);
        }
    }

    public function show($kode_obat)
    {
        $pharmacy = Pharmacy::where('kode_obat', $kode_obat)->first();

        if (!$pharmacy) {
            return response([
                'message' => 'ID tidak ditemukan'
            ], 404);
        }

        return response([
            'message' => 'Data berhasil ditemukan',
            'data' => $pharmacy,
        ], 200);
    }

    public function update(Request $request, string $kode_obat)
    {
        try {
            $pharmacy = Pharmacy::where('kode_obat', $kode_obat)->first();
    
            if (!$pharmacy) {
                return response([
                    'message' => 'ID tidak ditemukan'
                ], 404);
            }
            $validated = $request->validate([
                'nama_obat' => 'required|string',
                'stock_obat' => 'required|integer',
                'type_obat' => 'required|string',
                'isi_obat' => 'required|integer',
            ]);
            
            $pharmacy->update($validated);
    
            return response([
                'message' => 'Data obat berhasil diubah',
                'data' => $pharmacy,
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => 'Data obat gagal diubah',
                'data' => $th,
            ], 500);
        }
    }

    public function destroy($kode_obat)
    {
        try {
            $pharmacy = Pharmacy::where('kode_obat', $kode_obat)->first();
            if (!$pharmacy) {
                return response([
                    'message' => 'ID obat tidak ditemukan',
                ], 404);
            }
            
            $pharmacy->delete();
            return response([
                'success' => true,
                'message' => 'Berhasil menghapus obat',
                'data' => $pharmacy
            ], 201);

        } catch (\Throwable $th) {
            return response([
                'success' => false,
                'message' => 'Gagal menghapus data obat',
                'data' => $th
            ], 500);
        }
    }
}