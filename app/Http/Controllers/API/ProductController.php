<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{

    public function index()
    {
        return \response(Product::with("category")
            ->orderBy('created_at', 'desc')
            ->paginate(10));
    }

    public function search($product)
    {
        return response(Product::where('product_name', 'like', '%' . $product . '%')
            ->with('category', 'sales')
            ->orderBy('created_at', 'desc')
            ->paginate(10));
    }

    public function store(Request $request)
    {

        Log::info($request->all());
        // Validate all required fields
        $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'SponsorId' => 'required|integer|exists:Sponsors,SponsorId', // Adjust table name as needed
            'supplier_id' => 'required|integer|exists:suppliers,id', // Adjust table name as needed
            'category_id' => 'required|integer|exists:categories,id', // Adjust table name as needed
            'product_photo' => 'required|image|mimes:jpeg,png,jpg|max:5000',
            'brochure_pdf_path' => 'sometimes|mimes:pdf|max:10000'
        ]);
    
        // Handle product photo upload
        $photo = $request->file('product_photo');
        $photoName = time() . '_' . $photo->getClientOriginalName();
        $photoPath = $photo->move(public_path('storage/images/products'), $photoName);
        $productPhotoURL = config('app.url') . "/storage/images/products/{$photoName}";
    
        // Handle brochure upload (optional)
        $brochureURL = null;
        if ($request->hasFile('brochure_pdf_path')) {
            $brochure = $request->file('brochure_pdf_path');
            $brochureName = time() . '_' . $brochure->getClientOriginalName();
            $brochure->move(public_path('storage/pdf/brochures'), $brochureName);
            $brochureURL = config('app.url') . "/storage/pdf/brochures/{$brochureName}";
        }
    
        // Create the product
        $product = Product::create([
            'product_name' => $request->get('product_name'),
            'description' => $request->get('description'),
            'price' => $request->get('price'),
            'SponsorId' => $request->get('SponsorId'),
            'supplier_id' => $request->get('supplier_id'),
            'category_id' => $request->get('category_id'),
            'product_photo_path' => $photoPath,
            'product_photo_url' => $productPhotoURL,
            'brochure_pdf_path' => $brochureURL,
        ]);
    
        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product
        ], 201);
    }

    public function update(Product $product, Request $request)
    {
    
        $request->validate([
            'product_photo' => 'sometimes|image|mimes:jpeg,png,jpg|max:5000',
            'brochure_pdf_path' => 'sometimes|mimes:pdf|max:10000'
        ]);
    
        // Initialize variables for file URLs
        $productPhotoURL = $product->product_photo_url;
        $productPhotoPath = $product->product_photo_path;
        $brochureURL = $product->brochure_pdf_path;
    
        // Handle product photo update if present
        if ($request->hasFile('product_photo')) {
            $photo = $request->file('product_photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photoPath = $photo->move(public_path('storage/images/products'), $photoName);
            $productPhotoPath = $photoPath;
            $productPhotoURL = config('app.url') . "/storage/images/products/{$photoName}";
        }
    
        // Handle brochure PDF update if present
        if ($request->hasFile('brochure_pdf_path')) {

            Log::info("Request has a file");

            $pdf = $request->file('brochure_pdf_path');
            $pdfName = time() . '_' . $pdf->getClientOriginalName();
            $pdf->move(public_path('storage/pdf/brochures'), $pdfName);
            $brochureURL = config('app.url') . "/storage/pdf/brochures/{$pdfName}";
        }
    
        // Perform update
        $product->update([
            'product_name' => $request->get('product_name'),
            'description' => $request->get('description'),
            'price' => $request->get('price'),
            'SponsorId' => $request->get('SponsorId'),
            'supplier_id' => $request->get('supplier_id'),
            'product_photo_path' => $productPhotoPath,
            'product_photo_url' => $productPhotoURL,
            'brochure_pdf_path' => $brochureURL,
        ]);
    
        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $product
        ]);
    }
    

    public function destroy(Product $product)
    {
        return response($product->delete());
    }
}
