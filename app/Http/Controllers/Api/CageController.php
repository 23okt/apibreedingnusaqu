<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cage;
use Illuminate\Support\Facades\Validator;

class CageController extends Controller
{
    private $failedIdMessage = 'ID tidak ditemukan';

    public function index(Request $request)
    {
        $cage_id = $request->query('id_kandang');

        if($cage_id){
            $cage = Cage::where('id_kandang',$cage_id)->get();
        }else{
            $cage = Cage::get();
        }

        try {
            return response()->json([
                'success' => true,
                'message' => 'Success menampilkan data kandang',
                'data' => $cage
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menampilkan data kandang',
                'data' => $th
            ], 500);
        }
    }


    public function store(Request $request)
    {

        try {
            $rand_number = rand(000,999);
            $validator = Validator::make($request->all(), [
                'nama_kandang' => 'required|string|min:1',
                'type_kandang' => 'required|string',
                'jumlah_kambing' => 'required|integer',
                'lokasi' => 'required|string|min:1'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => $failedIdMessage,
                    'error' => $validator->errors()
                ], 422);
            }

            $result = $validator->validated();
            $cage = Cage::create(array_merge($result, [
                'kode_kandang' => 'CAGE-' . $rand_number,
            ]));

            return response()->json([
                'message' => 'Data kandang berhasil ditambahkan.',
                'data' => $cage
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data kandang gagal ditambahkan.',
                'data' => $th,
            ], 500);
        }
    }

    public function show($kode_kandang)
    {
        $cage = Cage::where('kode_kandang', $kode_kandang)->first();
        
        if (!$cage) {
            return response([
                'success' => false,
                'message' => $this->$failedIdMessage,
            ], 404);
        }

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data kandang berhasil di tambahkan.',
                'data' => $cage
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data kandang gagal di tambahkan.',
                'data' => $th
            ], 500);
        }
    }

    public function update(Request $request, string $kode_kandang){
        $cage = Cage::where('kode_kandang', $kode_kandang)->first();
        if (!$cage) {
            return response([
                'message' => $this->$failedIdMessage,
            ], 404);
        }

        try {
            $validated = $request->validate([
                'nama_kandang' => 'required|string|min:1',
                'type_kandang' => 'required|string',
                'jumlah_kambing' => 'required|integer',
                'lokasi' => 'required|string|min:1'
            ]);
    
            $cage->update($validated);
            return response([
                'message' => 'Data kandang berhasil di ubah',
                'data' => $cage
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'success' => false,
                'message' => 'Gagal mengubah data kandang',
                'data' => $th
            ], 500);
        }
    }

    public function destroy($kode_kandang){
        $cage = Cage::where('kode_kandang', $kode_kandang)->first();

        if (!$cage) {
            return response([
                'message' => $this->failedIdMessage,
            ], 404);
        }

        try {
            $cage->delete();
            return response([
                'success' => true,
                'message' => 'Data kandang berhasil dihapus.',
                'data' => $cage
            ], 201);
        } catch (\Throwable $th) {
            return response([
                'success' => false,
                'message' => 'Data kandang gagal dihapus.',
                'data' => $th
            ], 500);
        }
    }
}