<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Birth;
use App\Models\ItemKelahiran;
use App\Models\Breeding;
use App\Models\Goats;
use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Validator;

class BirthController extends Controller
{
    public function index()
    {
        $birth = Birth::orderBy('created_at','desc')->get();

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
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'breeding_id' => 'required|exists:perkawinan,kode_breeding',
                'offspring_codes' => 'required|array|min:1',
                'offspring_codes.*' => 'exists:product,kode_product',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Failed to add data birth',
                    'error' => $validator->errors()
                ], 422);
            }

            // Ambil breeding
            $breed = Breeding::where('kode_breeding', $request->breeding_id)->firstOrFail();

            // Ambil anak-anak
            $offsprings = Goats::whereIn('kode_product', $request->offspring_codes)->get();

            // Validasi anak sesuai induk breeding
            foreach ($offsprings as $goat) {
                if (
                    $goat->mother_id !== $breed->female_id ||
                    $goat->father_id !== $breed->male_id
                ) {
                    return response()->json([
                        'message' => 'Beberapa anakan tidak sesuai dengan induk breeding'
                    ], 422);
                }
            }

            // Simpan kelahiran
            $birth = Birth::create([
                'kode_kelahiran' => 'BIR-' . rand(100,999),
                'breeding_id' => $breed->id_breeding,
                'birth_date' => $offsprings->min('birth_date'),
                'offspring_count' => $offsprings->count(),
                'notes' => $request->notes
            ]);

            // Simpan detail anak
            foreach ($offsprings as $goat) {
                ItemKelahiran::create([
                    'kelahiran_id' => $birth->id_kelahiran,
                    'product_id' => $goat->id_product
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data kelahiran berhasil ditambahkan.',
                'data' => $birth->load([
                    'breed.female:id_product,kode_product',
                    'breed.male:id_product,kode_product',
                    'details.goat:id_product,kode_product,birth_date'
                ])
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Data kelahiran gagal ditambahkan.',
                'error' => $th->getMessage()
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