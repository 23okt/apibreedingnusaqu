<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use Illuminate\Support\Facades\Validator;


class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $supplier = $request->query('id_supplier');

        if ($supplier) {
            $supplier = Supplier::where('id_supplier', $id_supplier)->get();
        }else {
            $supplier = Supplier::get();
        }

        return response()->json([
            'message' => 'Successfull get all Supplier',
            'data' => $supplier
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $rand_number = rand(000,999);
            $validator = Validator::make($request->all(), [
                'nama_supplier' => 'required|string',
                'no_supplier' => 'required|string',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Failed to add Supplier',
                    'error' => $validator->errors()
                ], 422);
            }
    
            $result = $validator->validated();
    
            $supplier = Supplier::create(array_merge($result, [
                'kode_supplier' => 'SUP-' . $rand_number,
            ]));
    
            return response()->json([
                'success' => true,
                'message' => 'Data supplier berhasil ditambahkan',
                'data' => $supplier
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data supplier gagal ditambahkan',
                'data' => $th,
            ], 500);
        }
    }

    public function show($kode_supplier)
    {
        try {
            $supplier = Supplier::where('kode_supplier', $kode_supplier)->first();
            if (!$supplier) {
                return response([
                    'success' => false,
                    'message' => 'Data Supplier tidak ada.',
                ], 404);
            }
    
            return response([
                'success' => true,
                'message' => 'Data Supplier berhasil ditemukan',
                'data' => $supplier
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'successs' => false,
                'message' => 'Data Supplier tidak ditemukan',
                'data' => $th
            ], 500);
        }
    }

    public function update(Request $request, string $kode_supplier)
    {

        try {
            $supplier = Supplier::where('kode_supplier', $kode_supplier)->first();
    
            if (!$supplier) {
                return response([
                    'message' => 'ID tidak ditemukan.',
                ], 404);
            }
    
            $validated = $request->validate([
                'nama_supplier' => 'required|min:1',
                'no_supplier' => 'required|min:1|string',
            ]);
    
            $supplier->update($validated);
    
            return response([
                'success' => true,
                'message' => 'Data supplier berhasil diubah',
                'data' => $supplier
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'success' => false,
                'message' => 'Data supplier gagal diubah',
                'data' => $th,
            ], 500);
        }
    }

    public function destroy($kode_supplier){
        
        try {
            $supplier = Supplier::where('kode_supplier', $kode_supplier)->first();
    
            if (!$supplier) {
                return response([
                    'message' => 'Data gagal dihapus',
                ], 404);
            }
    
            $supplier->delete();
            return response([
                'success' => true,
                'message' => 'Data berhasil di hapus',
                'data' => $supplier
            ], 201);
        } catch (\Throwable $th) {
            return response([
                'success' => false,
                'message' => 'Data gagal di hapus',
                'data' => $th,
            ], 500);
        }
    }
}