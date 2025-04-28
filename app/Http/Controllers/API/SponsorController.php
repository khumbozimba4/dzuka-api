<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SponsorController extends Controller
{
    // List sponsors
    public function index()
    {
        return response(Sponsor::orderBy('CreatedAt', 'desc')->paginate(10));
    }

    // Add new sponsor
    public function store(Request $request)
    {
        $request->validate([
            'SponsorName' => 'required|string|max:255',
            'Logo' => 'required|image|mimes:jpeg,png,jpg|max:5000', // File upload
            'Description' => 'nullable|string',
            'ContactInfo' => 'nullable|string|max:255',
            'SponsorSince' => 'nullable|date',
            'IsActive' => 'nullable|boolean',
        ]);



        // Upload logo
        $imageName = time() . '_' . $request->file('Logo')->getClientOriginalName();
        $path = $request->file('Logo')->move(public_path('/images/sponsors'), $imageName);

        $logoUrl = sprintf("https://pamsika-api.dzuka-africa.org/images/sponsors/%s", $imageName);

        // Create sponsor
        $sponsor = Sponsor::create([
            'SponsorName' => $request->SponsorName,
            'LogoUrl' => $logoUrl,
            'Description' => $request->Description,
            'ContactInfo' => $request->ContactInfo,
            'SponsorSince' => $request->SponsorSince,
            'IsActive' => $request->IsActive ?? true,
        ]);

        return response($sponsor, 201);
    }

    public function update(Sponsor $sponsor, Request $request)
    {

        if (empty($request->all())) {
            Log::error('Request data is empty or null');
            return response()->json(['error' => 'Request data is empty or null'], 400);
        }
        try {
            $validated = $request->validate([
                'SponsorName' => 'sometimes|required|string|max:255',
                'Logo' => 'sometimes|image|mimes:jpeg,png,jpg|max:5000',
                'Description' => 'nullable|string|max:255',
                'ContactInfo' => 'nullable|string|max:255',
                'SponsorSince' => 'nullable|date'
            ]);
    
            if ($request->hasFile('Logo')) {
                if ($sponsor->LogoUrl) {
                    $oldPath = public_path(parse_url($sponsor->LogoUrl, PHP_URL_PATH));
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
    
                $imageName = uniqid('sponsor_') . '.' . $request->file('Logo')->getClientOriginalExtension();
                $request->file('Logo')->move(public_path('/images/sponsors'), $imageName);
                $validated['LogoUrl'] = sprintf("https://pamsika-api.dzuka-africa.org/images/sponsors/%s", $imageName);
            }

            Log::info("Validated data: ", $validated); // Log the validated data

    
            $sponsor->update($validated);
    
            Log::info('Sponsor updated successfully');
    
            return response()->json($sponsor);
    
        } catch (\Throwable $th) {
            Log::error('Sponsor update failed: ' . $th->getMessage());
            return response()->json(['error' => 'Update failed'], 500);
        }
    }
    
    

    // Delete sponsor
    public function destroy(Sponsor $sponsor)
    {
        $sponsor->delete();
        return response(null, 204);
    }
}
