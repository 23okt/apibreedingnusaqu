<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pregnant;
use App\Models\Breeding;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Validator;

class PregnantController extends Controller
{
    public function index()
    {
        $pregnant = Pregnant::with([
            'breed',
            'breed.female:id_product,kode_product',
            'breed.male:id_product,kode_product'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data kehamilan berhasil di tampilkan',
                'data' => $pregnant
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th,
                'data' => $pregnant
            ], 500);
        }
    }

    public function store(Request $request)
    {
        
        $FILE_SIZE = 1024 * 5;
        $rand_number = rand(000, 999);

        try {
            $validator = Validator::make($request->all(), [
                'breeding_id' => 'string|exists:perkawinan,id_breeding',
                'check_date' => 'required|date',
                'status' => 'required|string',
                // 'photos' => "required|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'notes' => 'nullable|string'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan data kehamilan.',
                    'error' => $validator->errors()
                ], 422);
            }
            
            $result = $validator->validated();
            
            //     if ($request->hasFile('photos')) {
            //         try {
            //             $file = $request->file('photos')->getRealPath();
            //             $cloudinary = new Cloudinary();
        
            //             $uploadFile = Cloudinary::uploadApi()->upload($file, [
            //                 'folder' => 'nusaqu'
            //             ]);
        
            //             $result["photos"] = $uploadFile['secure_url'];
            //         } catch (\Exception $e) {
            //             return response()->json([
            //                 'message' => 'Failed to upload image to Cloudinary',
            //                 'error' => $e->getMessage()
            //             ], 500);
            //         }
            // }
    
            $pregnant = pregnant::create(array_merge($result, [
                'kode_kehamilan' => 'PRE-' . $rand_number,
            ]));
    
            return response()->json([
                'success' => true,
                'message' => 'Data kehamilan berhasil ditambahkan.',
                'data' => $pregnant
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data kehamilan gagal ditambahkan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }


    public function show($kode_kehamilan)
    {
        $pregnant = Pregnant::where('kode_kehamilan',$kode_kehamilan)
        ->with('breed','breed.female:id_product,kode_product','breed.male:id_product,kode_product')
        ->first();

        try {
            if (!$pregnant) {
                return response([
                    'message' => 'ID tidak ditemukan.'
                ], 404);
            }
    
            return response([
                'success' => true,
                'message' => 'Data kehamilan berhasil ditemukan.',
                'data' => $pregnant,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data kehamilan gagal ditemukan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $kode_kehamilan)
    {
        $FILE_SIZE = 1024 * 5;
        $pregnant = Pregnant::where('kode_kehamilan', $kode_kehamilan)->first();

        try {
            if (!$pregnant) {
                return response([
                    'success' => false,
                    'message' => 'ID tidak ditemukan',
                ], 404);
            }

            $validated = $request->validate([
                'breeding_id' => 'required|exists:perkawinan,id_breeding',
                'check_date' => 'required|date',
                'status' => 'required|string',
                // 'photos' => "nullable|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'notes' => 'nullable|string'
            ]);

            // if ($request->hasFile('photos')) {
            //     //Hapus photo lama di Cloudinary
            //         if (!empty($pregnant->photos)) {
            //             try {
            //                 $publicId = $this->getPublicIdFromCloudinaryUrl($pregnant->photos);
            //                 Cloudinary::uploadApi()->destroy($publicId);
            //             } catch (\Throwable $th) {
            //                 return $th->getMessage();
            //             }
            //         }
            //     try {
            //         $file = $request->file('photos')->getRealPath();
            //         $cloudinary = new Cloudinary();
    
            //         $uploadFile = Cloudinary::uploadApi()->upload($file, [
            //             'folder' => 'nusaqu'
            //         ]);
    
            //         $validated["photos"] = $uploadFile['secure_url'];
            //     } catch (\Exception $e) {
            //         return response()->json([
            //             'message' => 'Failed to upload image to Cloudinary',
            //             'error' => $e->getMessage()
            //         ], 500);
            //     }
            // }

            $pregnant->update($validated);
            return response()->json([
                'messsage' => 'Data kehamilan berhasil diubah',
                'data' =>  $pregnant
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data kehamilan gagal diubah.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($kode_kehamilan)
    {
        $pregnant = pregnant::where('kode_kehamilan', $kode_kehamilan)->first();

        try {
            if (!$pregnant) {
                return response([
                    'message' => 'ID tidak ditemukan.'
                ], 404);
            }
    
            $pregnant->delete();
            return response([
                'success' => true,
                'message' => 'Berhasil menghapus data kehamilan',
                'data' => $pregnant
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data kehamilan.',
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