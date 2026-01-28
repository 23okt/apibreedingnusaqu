<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Breeding;
use App\Models\Goats;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class BreedingController extends Controller
{
    public function index()
    {
        $breeding = Breeding::with(
            'female:id_product,kode_product,type_product',
            'male:id_product,kode_product,type_product'
        )->orderBy('created_at','desc')
        ->get();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data breeding berhasil di tampilkan',
                'data' => $breeding,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data breeding gagal di tampilkan',
                'data' => $th
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // $FILE_SIZE = 1024 * 5;
        $rand_number = rand(000,999);

        try {
            $validator = Validator::make($request->all(), [
                'female_id' => 'required|string|exists:product,id_product',
                'male_id' => 'string|exists:product,id_product',
                'tanggal_pkb' => 'required|date|min:1',
                'status' => 'required|string|min:1',
                'notes' => 'nullable|string'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data perkawinan gagal ditambahkan.',
                    'error' => $validator->errors()
                ], 422);
            }
    
            $result = $validator->validated();
    
            $breeding = Breeding::create(array_merge($result,[
                'kode_breeding' => 'BRE-' . $rand_number,
            ]));
    
    
            return response()->json([
                'message' => 'Data perkawinan berhasil di tambahkan.',
                'data' => $breeding
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data perkawinan gagal di tambahkan.',
                'data' => $th->getMessage(),
            ], 500);
        }
    }

    public function show($kode_breeding)
    {
        $breeding = Breeding::with([
            'pregnant',
            'birth',
            'female:id_product,kode_product,type_product',
            'male:id_product,kode_product,type_product'
        ])
        ->where('kode_breeding', $kode_breeding)
        ->first();

        try {
            if (!$breeding) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID tidak ditemukan'
                ], 404);
            }
    
            return response()->json([
                'successs' => true,
                'message' => 'Data Breeding berhasil ditemukan',
                'data' => $breeding,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data Breeding gagal ditemukan',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $kode_breeding)
    {
        $breeding = Breeding::where('kode_breeding', $kode_breeding)->first();

        try {
            if (!$breeding) {
                return response([
                    'message' => 'ID tidak ditemukan'
                ], 404);
            }
            
            $validated = $request->validate([
                'female_id' => 'required|string|exists:product,id_product',
                'male_id' => 'string|exists:product,id_product',
                'tanggal_pkb' => 'required|date|min:1',
                'status' => 'required|string|min:1',
                'notes' => 'nullable|string'
            ]);

            $breeding->update($validated);
            return response([
                'message' => 'Data Perkawinan berhasil diubah.',
                'data' => $breeding,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data Perkawinan gagal diubah.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($kode_breeding)
    {
        $breeding = Breeding::where('kode_breeding',$kode_breeding)->first();

        try {
            if (!$breeding) {
                return response([
                    'message' => 'ID Tidak ditemukan.'
                ], 404);
            }

            $breeding->delete();
            return response([
                'success' => true,
                'message' => 'Data breeding berhasil di hapus',
                'data' => $breeding
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data breeding gagal dihapus.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil public_id dari URL Cloudinary
     */
    private function getPublicIdFromCloudinaryUrl($url)
    {
        $parts = explode('/', parse_url($url, PHP_URL_PATH));
        $file = end($parts);
        return pathinfo($file, PATHINFO_FILENAME);
    }
}