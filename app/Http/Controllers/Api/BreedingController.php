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
        $breeding = Breeding::get();

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
        $FILE_SIZE = 1024 * 5;
        $rand_number = rand(000,999);

        try {
            $validator = Validator::make($request->all(), [
                'female_id' => 'required|string|exists:product,kode_product',
                'male_id' => 'string|exists:product,kode_product',
                'tanggal_perkiraan_lahir' => 'required|date|min:1',
                'status' => 'required|string|min:1',
                'photo1' => "required|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'photo2' => "required|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'photo3' => "required|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
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
            $mother = Goats::where('kode_product', $result['female_id'])->firstOrFail();
            $father = Goats::where('kode_product', $result['male_id'])->firstOrFail();

            $result['female_id'] = $mother->id_product;
            $result['male_id'] = $father->id_product;
            // ==============================
            // HANDLE FOTO CLOUDINARY
            // ==============================
            
            $photos = ['photo1','photo2','photo3'];
            foreach ($photos as $photo) {
                if ($request->hasFile($photo)) {
                    try {
                        $file = $request->file($photo)->getRealPath();
                        $cloudinary = new Cloudinary();
        
                        $uploadFile = Cloudinary::uploadApi()->upload($file, [
                            'folder' => 'nusaqu'
                        ]);
        
                        $result[$photo] = $uploadFile['secure_url'];
                    } catch (\Exception $e) {
                        return response()->json([
                            'message' => 'Failed to upload image to Cloudinary',
                            'error' => $e->getMessage()
                        ], 500);
                    }
                }
            }
    
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
            'female:id_product,kode_product,nama_product',
            'male:id_product,kode_product,nama_product'
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
        $FILE_SIZE = 1024 * 5;
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
                'tanggal_perkiraan_lahir' => 'required|date|min:1',
                'status' => 'required|string|min:1',
                'photo1' => "nullable|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'photo2' => "nullable|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'photo3' => "nullable|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'notes' => 'nullable|string'
            ]);

            $photos = ['photo1','photo2','photo3'];
                    
            foreach ($photos as $p ) {
                if (!$request->hasFile($p)) {
                    continue;
                }

                if (!empty($breeding[$p])) {
                    $publicId = $this->getPublicIdFromCloudinaryUrl($breeding[$p]);
                    Cloudinary::uploadApi()->destroy($publicId);
                }
                
                try {
                    $file = $request->file($p)->getRealPath();
                    $upload = Cloudinary::uploadApi()->upload($file, [
                        "folder" => "nusaqu"
                    ]);
                    $validated[$p] = $upload['secure_url'];
                } catch (\Throwable $th) {
                    return response()->json([
                        'success' => false,
                        'message' => "Gagal upload photo ke Cloudinary",
                        'error' => $th->getMessage()
                    ], 500);
                }
            }
            
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