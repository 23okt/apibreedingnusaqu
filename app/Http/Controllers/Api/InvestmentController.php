<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Investment;
use App\Models\Users;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Validation\Rule;

class InvestmentController extends Controller
{
    public function index()
    {
        $investment = Investment::with('users')
            ->orderBy('created_at', 'desc')
            ->get();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data investasi berhasil di tampilkan',
                'data' => $investment
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th,
                'data' => $investment
            ], 500);
        }
    }

    public function store(Request $request)
    {
        
        $FILE_SIZE = 1024 * 5;
        $rand_number = rand(000, 999);

        $rules = [
            'jumlah_inves' => 'required|int|min:1',
            'jumlah_inves_terbilang' => 'required|string|min:1',
            'metode_pembayaran' => 'required|string|min:1',
            'tanggal_investasi' => 'required|string',
            'description' => 'required|string|min:5',
            'users_id' => 'required|exists:users,kode_unik',
            'bukti_pembayaran' => [
                Rule::requiredIf($request->metode_pembayaran !== 'Lanjutan'),
                'file',
                "max:{$FILE_SIZE}",
                "mimes:png,jpg,jpeg"
            ]
        ];
        
        try {
            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Failed to add data investasi',
                    'error' => $validator->errors()
                ], 422);
            }
    
            $result = $validator->validated();
            $users = Users::where('kode_unik', $result['users_id'])->firstOrFail();
            $result['users_id'] = $users->id_users;
            
    
            if ($request->hasFile('bukti_pembayaran')) {
                try {
                    $file = $request->file('bukti_pembayaran')->getRealPath();
                    $cloudinary = new Cloudinary();
    
                    $uploadFile = Cloudinary::uploadApi()->upload($file, [
                        'folder' => 'nusaqu'
                    ]);
    
                    $result["bukti_pembayaran"] = $uploadFile['secure_url'];
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Failed to upload image to Cloudinary',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
    
            $investment = Investment::create(array_merge($result, [
                'status' => 'Diterima',
                'kode_investasi' => 'IVS-' . $rand_number,
            ]));
    
            return response()->json([
                'success' => true,
                'message' => 'Data investasi berhasil ditambahkan',
                'data' => $investment
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data investasi gagal ditambahkan',
                'data' => $th->getMessage()
            ], 500);
        }
    }


    public function show($kode_investasi)
    {
        try {
            $investment = Investment::with(['users', 'products.children'])
                ->where('kode_investasi', $kode_investasi)
                ->first();

            if (!$investment) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID tidak ditemukan'
                ], 404);
            }

            $feeMarketing = 500000;

            $totalCustomerShare = 0;
            $totalProfitBersih  = 0;
            $totalProyeksiKeuntungan = 0;

            $investment->products->transform(function ($product) use (
            $feeMarketing,
            &$totalCustomerShare,
            &$totalProfitBersih,
            &$totalProyeksiKeuntungan
        ) {

            // ===============================
            // INDUKAN
            // ===============================
            if ($product->status === 'Terjual') {
                $hargaJual = (int) $product->harga_jual;

                $profitBersih = max($hargaJual - $feeMarketing, 0);
                $customerShare = (int) ($profitBersih * 0.3);

                $totalProfitBersih       += $profitBersih;
                $totalCustomerShare      += $customerShare;
                $totalProyeksiKeuntungan += $customerShare;
            }

            // ===============================
            // ANAKAN
            // ===============================
            foreach ($product->children ?? [] as $child) {
                if ($child->status === 'Terjual') {
                    $hargaJualAnak = (int) $child->harga_jual;

                    $profitBersihAnak = max($hargaJualAnak - $feeMarketing, 0);
                    $customerShareAnak = (int) ($profitBersihAnak * 0.3);

                    $totalProfitBersih       += $profitBersihAnak;
                    $totalCustomerShare      += $customerShareAnak;
                    $totalProyeksiKeuntungan += $customerShareAnak;
                }
            }

            // ===============================
            // FINANCE PER INDUKAN
            // ===============================
            $product->finance = [
                'is_sold' => $product->status === 'Terjual',
                'harga_jual' => $product->harga_jual ?? 0,
            ];

            return $product;
        });


            // ===============================
            // INVESTMENT FINANCE (REKAP)
            // ===============================
            $investment->finance = [
                'total_profit_bersih'        => $totalProfitBersih,
                'customer_share'             => $totalCustomerShare,
                'total_proyeksi_keuntungan'  => $totalProyeksiKeuntungan,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil ditemukan.',
                'data' => $investment
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data gagal ditemukan.',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, string $kode_investasi)
    {
        $FILE_SIZE = 1024 * 5;
        $investment = Investment::where('kode_investasi', $kode_investasi)->first();

        try {
            if (!$investment) {
                return response()->json([
                    'message' => 'ID tidak ditemukan.'
                ], 400);
            }
    
            $validator = $request->validate([
                'kode_product' => [
                    'required',
                    'string',
                    Rule::unique('goats', 'kode_product')->ignore($goat->id_product, 'id_product')
                ],
                'jumlah_inves' => 'required|int|min:1',
                'jumlah_inves_terbilang' => 'required|string|min:1',
                'metode_pembayaran' => 'required|string|min:1',
                'bukti_pembayaran' => "nullable|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'tanggal_investasi' => 'required|string',
                'status' => 'required|string',
                'description' => 'required|string|min:5',
                'users_id' => 'required|exists:users,kode_unik'
            ]);

            $investCode = $validator['users_id'];
            $user = Users::where('kode_unik',$investCode)->firstOrFail();
            $validator['users_id'] = $user->id_users;

            if ($request->kode_product !== $goat->kode_product) {
                $rules['kode_product'] = 'required|unique:goats,kode_product';
            }


            if ($request->hasFile('bukti_pembayaran')) {
                if (!empty($investment->bukti_pembayaran)) {
                    try {
                        $publicId = $this->getPublicIdFromCloudinaryUrl($investment->bukti_pembayaran);
                        Cloudinary::uploadApi()->destroy($publicId);
                    } catch (\Throwable $th) {
                        return $th->getMessage();
                    }
                }
                try {
                    $file = $request->file('bukti_pembayaran')->getRealPath();
                    $cloudinary = new Cloudinary();

                    $uploadFile = Cloudinary::uploadApi()->upload($file, [
                        'folder' => 'nusaqu'
                    ]);

                    $validator["bukti_pembayaran"] = $uploadFile['secure_url'];
                } catch (\Throwable $th) {
                    return response()->json([
                        'message' => 'Failed to upload image to Cloudinary',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
    
            $investment->update($validator);
            return response()->json([
                'success' => true,
                'messsage' => 'Product has successfully updated',
                'data' =>  $investment
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data investasi gagal diubah.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($kode_investasi)
    {
        $investment = Investment::where('kode_investasi',$kode_investasi)->first();

        try {
            if (!$investment) {
                return response([
                    'message' => 'ID tidak ditemukan'
                ], 404);
            }
    
            $investment->delete();
            return response([
                'success' => true,
                'message' => 'Berhasil menghapus data investment',
                'data' => $investment
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data investment',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function getTotalInvestment()
    {
        $total_investasi = Investment::sum('jumlah_inves');

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data investasi berhasil di tampilkan',
                'data' => $total_investasi
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th,
                'data' => $total_investasi
            ], 500);
        }
    }

    public function getTotalInvestByUsers($users_id)
    {
        $investment = Investment::where('users_id', $users_id)
                                    ->sum('jumlah_inves');
        try {
            return response()->json([
                'success' => true,
                'message' => 'Data Total Invest berhasil diambil',
                'data' => $investment
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th,
            ], 500);
        }
    }

    public function getRiwayatInvestasi($users_id)
    {
        $investment = Investment::where('users_id', $users_id)
                                    ->orderBy('created_at', 'desc')
                                    ->get();
        try {
            return response()->json([
                'success' => true,
                'message' => 'Data Riwayat Investasi berhasil diambil',
                'data' => $investment
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th,
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