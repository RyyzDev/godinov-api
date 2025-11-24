<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Portfolio;
use App\Http\Resources\PortfolioResource;
use Cloudinary\Cloudinary;

class PortfolioController extends Controller
{


	public function index(){

    	$portfolio = Portfolio::All();
    	return (PortfolioResource::collection($portfolio))
            ->additional([
                'status_code' => 200,
                'message' => 'Data Berhasil Diambil!'
            ]);

	}

    public function detail($id){
        $portfolio = Portfolio::findOrFail($id);
        return (new PortfolioResource($portfolio))
        ->additional([
                'status_code' => 200,
                'message' => 'Data Berhasil Diambil'
        ]);
    }



    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|image|max:2048',
            'link_projects' => 'nullable|url'
        ]);

        if (!$request->hasFile('image')) {
            return response()->json([
                'status' => 'no_file',
                'message' => 'File image tidak ditemukan'
            ], 400);
        }

        try {
            // Inisialisasi Cloudinary dengan kredensial dari config
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud_name'),
                    'api_key'    => config('cloudinary.api_key'),
                    'api_secret' => config('cloudinary.api_secret')
                ],
                'url' => [
                    'secure' => true
                ]
            ]);

            // Upload file ke Cloudinary
            $uploadResult = $cloudinary->uploadApi()->upload(
                $request->file('image')->getRealPath(),
                [
                    'folder' => 'godinov_portfolio-image',
                    'transformation' => [
                        'quality' => 'auto',
                        'fetch_format' => 'auto'
                    ]
                ]
            );

            // Ambil URL dari response
            $imageUrl = $uploadResult['secure_url'];

            // Simpan ke database
            $portfolio = Portfolio::create([
                'title' => $request->title,
                'description' => $request->description,
                'image_url' => $imageUrl,
                'link_projects' => $request->link_projects
            ]);

            return response()->json([
                'message' => 'Portfolio berhasil ditambahkan',
                'data' => $portfolio
            ], 201);

        } catch (\Cloudinary\Api\Exception\ApiError $e) {
            return response()->json([
                'status' => 'cloudinary_api_error',
                'message' => 'Cloudinary API Error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'upload_failed',
                'message' => 'Upload gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id){
        $validated = $request->validate([
            'title' => 'required|max:225',
            'description' => 'required|max:255',
            'link_projects' => 'nullable|url'
        ]);

        $portfolio = Portfolio::findOrFail($id);
        $portfolio->update($request->all());
        return (new PortfolioResource($portfolio))
            ->additional([
                'status_code' => 200,
                'message' => 'Status berhasil diperbarui!'
            ]);
    }

    public function delete($id){
        $portfolio = Portfolio::findOrFail($id);
        $portfolio->delete();
        return (new PortfolioResource($portfolio))
        ->additional([
            'status_code' => 200,
            'message' => 'Data berhasil dibuang!'
        ]);

    }




}
