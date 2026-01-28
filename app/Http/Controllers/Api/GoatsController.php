<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Goats;
use App\Models\Users;
use App\Models\ItemInvestasi;
use App\Models\Cage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GoatsController extends Controller
{
    public function index()
    {
        $goats = Goats::with('cage')
            ->orderBy('created_at', 'desc')
            ->get();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data kambing berhasil di tampilkan',
                'data' => $goats
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th,
                'data' => $goats
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $FILE_SIZE = 1024 * 5;
        try {
            $validator = Validator::make($request->all(), [
                'kode_product' => 'required|unique:product,kode_product',
                'jenis_product' => 'required|string',
                'type_product' => 'required|string',
                'gender' => 'required|string',
                'birth_date' => 'nullable|date',
                'bobot' => 'required|integer',
                'harga_jual' => 'nullable|integer',
                'harga_beli' => 'nullable|integer',
                'photo1' => "required|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'photo2' => "required|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'photo3' => "required|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'status' => 'required|string',
                'mother_id' => 'nullable|exists:product,id_product',
                'father_id' =>'nullable|exists:product,id_product',
                // 'users_id' => 'nullable|exists:users,kode_unik',
                'kandang_id' => 'nullable|exists:kandang,kode_kandang',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Gagal menambahkan data kambing',
                    'error' => $validator->errors()
                ], 422);
            }
    
            $result = $validator->validated();
            $photos = ['photo1','photo2','photo3'];
            
            if (!empty($result['kandang_id'])) {
                $kandang = Cage::where('kode_kandang', $result['kandang_id'])->firstOrFail();
                $result['kandang_id'] = $kandang->id_kandang;
            } else {
                $result['kandang_id'] = null;
            }
    
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
    
            $goats = goats::create($result);
    
            return response()->json([
                'success' => true,
                'message' => 'Data kambing berhasil ditambahkan.',
                'data' => $goats
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data kambing gagal ditambahkan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }


    public function show($kode_product)
    {
        $goats = Goats::with([
            'children',
            'health',
            'mother:id_product,kode_product',
            'father:id_product,kode_product',
            'breedingAsFemale.male:id_product,kode_product',
            'breedingAsFemale.female:id_product,kode_product',
            'timbangan'
        ])
        ->withSum('investments as total_investasi', 'item_investment.jumlah_investasi')
        ->where('kode_product', $kode_product)
        ->first();

        try {
            if (!$goats) {
                return response([
                    'message' => 'ID tidak ditemukan'
                ], 404);
            }

            // ===============================
            // FINANCE COMPUTATION (ADD ONLY)
            // ===============================
            $hargaJual = (int) $goats->harga_jual;
            $hargaBeli = (int) $goats->harga_beli;

            $feeMarketing = 500000;
            $profitBersih = max($hargaJual - $feeMarketing, 0);

            $customerShare = (int) ($profitBersih * 0.3);

            $goats->finance = [
                'harga_beli'     => $hargaBeli,
                'harga_jual'     => $hargaJual,
                'fee_marketing'  => $feeMarketing,
                'profit_bersih'  => $profitBersih,
                'customer_share' => $customerShare,
            ];

            return response([
                'success' => true,
                'message' => 'Data berhasil ditemukan',
                'data' => $goats,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data gagal ditemukan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, string $kode_product)
    {
        $FILE_SIZE = 1024 * 5;
        $goats = Goats::where('kode_product', $kode_product)->first();
        
        try {
            if (!$goats) {
                return response()->json([
                    'message' => 'ID tidak ditemukan',
                ], 404);
            }
            $validator = Validator::make($request->all(), [
                    'kode_product' => 'nullable|unique:product,kode_product',
                    'jenis_product' => 'required|string',
                    'type_product' => 'required|string',
                    'gender' => 'required|string',
                    'birth_date' => 'nullable|date',
                    'bobot' => 'required|integer',
                    'harga_jual' => 'nullable|integer',
                    'harga_beli' => 'nullable|integer',
                    'photo1' => "nullable|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                    'photo2' => "nullable|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                    'photo3' => "nullable|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                    'status' => 'required|string',
                    'mother_id' => 'nullable|exists:product,id_product',
                    'father_id' =>'nullable|exists:product,id_product',
                    'kandang_id' => 'nullable|exists:kandang,kode_kandang',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Gagal mengubah data kambing',
                    'error' => $validator->errors()
                ], 422);
            }

            $result = $validator->validated();
            $jenis  = strtolower($result['jenis_product']);
            $gender = strtolower($result['gender']);

            if ($jenis === 'anakan') {
                $prefix = 'AQ';
            } else {
                $prefix = $gender === 'male' ? 'DQ' : 'BQ';
            }

            $oldKode = $goats->kode_product;
            if (str_contains($oldKode, '-')) {
                [$oldPrefix, $number] = explode('-', $oldKode);
            } else {
                $number = '001';
            }

            $needRegenerateKode =
                strtolower($goats->jenis_product) !== $jenis ||
                strtolower($goats->gender) !== $gender;

            if ($needRegenerateKode) {
                $result['kode_product'] = $prefix . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
            }
            
            $kandang = Cage::where('kode_kandang', $result['kandang_id'])->firstOrFail();
    
            $result['kandang_id'] = $kandang->id_kandang;
            
            $photos = ['photo1','photo2','photo3'];
            foreach ($photos as $photo) {
                if ($request->hasFile($photo)) {

                    //Update foto lama
                    if (!empty($goats[$photo])) {
                        try {
                            $publicId = $this->getPublicIdFromCloudinaryUrl($goats[$photo]);
                        } catch (\Throwable $th) {
                            return $th->getMessage();
                        }
                    }

                    //Upload foto baru
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
            
            $goats->update($result);
            return response()->json([
                'success' => true,
                'messsage' => 'Data kambing has successfully updated',
                'data' =>  $goats
            ], 200);
            
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data kambing gagal diubah.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($kode_product)
    {
        $goats = Goats::where('kode_product',$kode_product)->first();

        try {
            if (!$goats) {
                return response([
                    'message' => 'ID tidak ditemukan.'
                ], 404);
            }
    
            $goats->delete();
            return response([
                'success' => true,
                'message' => 'Berhasil menghapus goats',
                'data' => $goats
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data kambing.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function getTotalOfProduct()
    {
        $goats = Goats::count();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data kambing berhasil di tampilkan',
                'data' => $goats
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th,
                'data' => $goats
            ], 500);
        }
    }

    public function fetchGoatByUser($id_users)
    {
        try {
            $goats = Goats::whereHas('investments', function ($q) use ($id_users) {
                $q->where('users_id', $id_users);
            })
            ->get();
    
            return response()->json([
                'success' => true,
                'data' => $goats
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kambing.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    
    public function getDashboardStats($userId)
    {
        try {

            $totalDomba = DB::table('item_investment')
                ->join('investasi', 'investasi.id_investasi', '=', 'item_investment.investasi_id')
                ->where('investasi.users_id', $userId)
                ->distinct('item_investment.product_id')
                ->count('item_investment.product_id');

            $totalAnakan = Goats::where('users_id', $userId)
                ->where('jenis_product', 'anakan')
                ->count();

            $aktifBreeding = Goats::where('users_id', $userId)
                ->where('jenis_product', 'indukan')
                ->whereHas('breedingAsFemale', function ($q) {
                    $q->where('status', 'pregnant');
                })
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Dashboard stats berhasil diambil',
                'data' => [
                    'total_domba'    => $totalDomba,
                    'total_anakan'   => $totalAnakan,
                    'aktif_breeding' => $aktifBreeding,
                ]
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil dashboard stats',
                'data' => $th->getMessage(),
            ], 500);
        }
    }


    public function recentSales()
    {
        $items = ItemInvestasi::with('product')
            ->latest()
            ->take(10)
            ->get()
            ->unique('product_id')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $items->map(function ($item) {
                return [
                    'id_product' => $item->product->id_product,
                    'kode_product' => $item->product->kode_product,
                    'type_product' => $item->product->type_product,
                    'jumlah_investasi' => $item->jumlah_investasi,
                ];
            })
        ]);
    }

    public function bestSelling()
    {
        $data = ItemInvestasi::join(
                'product',
                'item_investment.product_id',
                '=',
                'product.id_product'
            )
            ->select(
                'product.type_product',
                DB::raw('COUNT(item_investment.product_id) as total_transaksi')
            )
            ->groupBy('product.type_product')
            ->orderByDesc('total_transaksi')
            ->get();

        $max = $data->max('total_transaksi');

        $result = $data->map(function ($item) use ($max) {
            return [
                'type_product' => $item->type_product,
                'total_transaksi' => $item->total_transaksi,
                'percentage' => $max > 0
                    ? round(($item->total_transaksi / $max) * 100)
                    : 0
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
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