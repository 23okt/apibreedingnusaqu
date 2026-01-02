<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Birth;
use App\Models\Breeding;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Validator;

class BirthController extends Controller
{
    public function index()
    {
        $birth = Birth::get();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data kelahiran berhasil di tampilkan',
                'data' => $birth
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data kelahiran gagal ditampilkan',
                'data' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        
        $FILE_SIZE = 1024 * 5;
        $rand_number = rand(000, 999);

        try {
            $validator = Validator::make($request->all(), [
                'breeding_id' => 'exists:perkawinan,kode_breeding',
                'birth_date' => 'required|date',
                'offspring_count' => 'required|int',
                'photos' => "required|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'notes' => 'nullable|string'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Failed to add data birth',
                    'error' => $validator->errors()
                ], 422);
            }
    
            $result = $validator->validated();
            $breed = Breeding::where('kode_breeding', $result['breeding_id'])->firstOrFail();
            $result['breeding_id'] = $breed->id_breeding;
    
            if ($request->hasFile('photos')) {
                try {
                    $file = $request->file('photos')->getRealPath();
                    $cloudinary = new Cloudinary();
    
                    $uploadFile = Cloudinary::uploadApi()->upload($file, [
                        'folder' => 'nusaqu'
                    ]);
    
                    $result["photos"] = $uploadFile['secure_url'];
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Failed to upload image to Cloudinary',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
    
            $birth = Birth::create(array_merge($result, [
                'kode_kelahiran' => 'BIR-' . $rand_number,
            ]));
    
            return response()->json([
                'success' => true,
                'message' => 'Data kelahiran berhasil ditambahkan.',
                'data' => $birth
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data kelahiran gagal ditambahkan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }


    public function show($kode_kelahiran)
    {
        $birth = Birth::where('kode_kelahiran', $kode_kelahiran)->first();

        try {
            if (!$birth) {
                return response([
                    'success' => false,
                    'message' => 'ID tidak ditemukan'
                ], 404);
            }
    
            return response([
                'success' => true,
                'message' => 'Data kelahiran berhasil ditemukan.',
                'data' => $birth,
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'success' => false,
                'message' => 'Data kelahiran gagal ditemukan.',
                'data' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $kode_kelahiran)
    {
        $FILE_SIZE = 1024 * 5;
        $birth = Birth::where('kode_kelahiran', $kode_kelahiran)->first();

        try {
            if (!$birth) {
                return response([
                    'success' => false,
                    'message' => 'ID tidak ditemukan',
                ], 404);
            }
            $validated = $request->validate([
                'breeding_id' => 'exists:perkawinan,id_breeding',
                'birth_date' => 'required|date',
                'offspring_count' => 'required|int',
                'photos' => "nullable|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'notes' => 'nullable|string'
            ]);
            
            // $breedingCode = $validated['breeding_id'];
            // $bCode = Breeding::where('kode_breeding', $breedingCode)->firstOrFail();

            // $validated['breeding_id'] = $bCode->id_breeding;
            if ($request->hasFile('photos')) {
                if (!empty($birth->photos)) {
                    try {
                        $publicId = $this->getPublicIdFromCloudinaryUrl($birth->photos);
                        Cloudinary::uploadApi()->destroy($publicId);
                    } catch (\Throwable $th) {
                        return $th->getMessage();
                    }
                }
                try {
                    $file = $request->file('photos')->getRealPath();
                    $cloudinary = new Cloudinary();
    
                    $uploadFile = Cloudinary::uploadApi()->upload($file, [
                        'folder' => 'nusaqu'
                    ]);
    
                    $validated["photos"] = $uploadFile['secure_url'];
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Failed to upload image to Cloudinary',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }

            $birth->update($validated);
            return response()->json([
                'messsage' => 'Data Kelahiran berhasil diubah.',
                'data' =>  $birth
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data kelahiran gagal diubah.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($kode_kelahiran)
    {
        $birth = Birth::where('kode_kelahiran', $kode_kelahiran)->first();

        try {
            if (!$birth) {
                return response([
                    'message' => 'ID Tidak ditemukan'
                ], 404);
            }
    
            $birth->delete();
            return response([
                'success' => true,
                'message' => 'Berhasil menghapus data kelahiran',
                'data' => $birth
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data kelahiran',
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