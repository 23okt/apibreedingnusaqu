<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HealthRecord;
use App\Models\Goats;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Validator;

class HealthRecordController extends Controller
{
    //Untuk mendapatkan semua data kesehatan
    public function index()
    {
        $health = HealthRecord::with('product')->orderBy('created_at', 'desc')->get();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data kesehatan berhasil di tampilkan',
                'data' => $health
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th,
                'data' => $health
            ], 500);
        }
    }

    //Untuk post data kesehatan ke database
    public function store(Request $request)
    {
        try {
            $FILE_SIZE = 1024 * 10;
            $rand_number = rand(000,999);

            $validator = Validator::make($request->all(), [
                'check_date' => 'required|date',
                'diagnosa' => 'required|string',
                'notes' => 'required|string',
                'product_id' => 'required|exists:product,id_product',
                'status_kesehatan' => 'required',
                'photo1' => "required|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg,webp",
                'photo2' => "required|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg,webp",
                'photo3' => "required|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg,webp",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Terjadi kesalahan saat validasi data.',
                    'error' => $validator->errors()
                ], 422);
            }

            $result = $validator->validated();

            $photos = ['photo1', 'photo2', 'photo3'];
            foreach ($photos as $photo) {
                if ($request->hasFile($photo)) {
                    try {
                        $file = $request->file($photo)->getRealPath();
                        $uploadFile = Cloudinary::uploadApi()->upload($file, [
                            'folder' => 'nusaqu'
                        ]);
                        $result[$photo] = $uploadFile['secure_url'];
                    } catch (\Throwable $th) {
                        return response()->json([
                            'message' => 'Failed to upload image to Cloudinary',
                            'error' => $th->getMessage()
                        ], 400);
                    }
                }
            }

            $health = HealthRecord::create(array_merge($result,[
                'kode_kesehatan' => 'HR-' . $rand_number,
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Data Kesehatan has sucessfully added',
                'data' => $health
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data kesehatan hewan gagal ditambahkan',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    //Untuk mendapatkan detail terperinci satu data kesehatan dengan id
    public function show($kode_kesehatan)
    {
        try {
            $health = HealthRecord::with([
                'product:id_product,kode_product,type_product,status',
                'penanganan.items.obat'
            ])->where('kode_kesehatan',$kode_kesehatan)->first();
    
            if (!$health) {
                return response([
                    'successs' => false,
                    'message' => 'ID tidak ditemukan'
                ], 404);
            }
    
            return response([
                'successs' => true,
                'message' => 'Data berhasil ditemukan',
                'data' => $health,
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'success' => false,
                'message' => 'Data kesehatan gagal ditemukan',
                'data' => $th->getMessage(),
            ], 500);
        }
    }

    //Untuk update data kesehatan terbaru
    public function update(Request $request, string $kode_kesehatan)
    {
        $FILE_SIZE = 1024 * 5;

        $health = HealthRecord::where('kode_kesehatan', $kode_kesehatan)->first();

        if (!$health) {
            return response([
                'message' => 'Data kesehatan tidak ditemukan'
            ], 404);
        }

        
        $validated = $request->validate([
            'check_date' => 'required|date',
            'diagnosa' => 'required|string',
            'product_id' => 'string|exists:product,id_product',
            'notes' => 'required|string',
            'status_kesehatan' => 'required|string',
            'photo1' => "nullable|file|max:$FILE_SIZE|mimes:png,jpg,jpeg,webp",
            'photo2' => "nullable|file|max:$FILE_SIZE|mimes:png,jpg,jpeg,webp",
            'photo3' => "nullable|file|max:$FILE_SIZE|mimes:png,jpg,jpeg,webp",
        ]);


        // ==========
        //  HANDLE IMAGE
        // ==========
        $photos = ['photo1', 'photo2', 'photo3'];

        foreach ($photos as $p) {

            if ($request->hasFile($p)) {

                // Hapus foto lama di Cloudinary
                if (!empty($health[$p])) {
                    try {
                        $publicId = $this->getPublicIdFromCloudinaryUrl($health[$p]);
                        Cloudinary::uploadApi()->destroy($publicId);
                    } catch (\Throwable $th) {
                        return $th->getMessage();
                    }
                }

                // Upload foto baru
                try {
                    $file = $request->file($p)->getRealPath();

                    $upload = Cloudinary::uploadApi()->upload($file, [
                        "folder" => "nusaqu"
                    ]);

                    $validated[$p] = $upload['secure_url'];

                } catch (\Throwable $th) {
                    return response()->json([
                        "success" => false,
                        "message" => "Gagal upload foto ke Cloudinary",
                        "error" => $th->getMessage()
                    ], 500);
                }
            }
        }


        // UPDATE RECORD
        $health->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data kesehatan berhasil diperbarui',
            'data' => $health
        ], 200);
    }

    public function destroy($kode_kesehatan)
    {
        $health = HealthRecord::where('kode_kesehatan', $kode_kesehatan)->first();

        try {
            if (!$health) {
                return response([
                    'message' => 'ID Tidak ditemukan'
                ], 404);
            }
            
            $health->delete();
            return response([
                'success' => true,
                'message' => 'Berhasil menghapus data kesehatan',
                'data' => $health
            ], 201);
        } catch (\Throwable $th) {
            return response([
                'success' => false,
                'message' => 'Gagal menghapus data kesehatan',
                'data' => $th->getMessage(),
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