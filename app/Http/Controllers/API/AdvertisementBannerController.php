<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AdvertisementBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdvertisementBannerController extends Controller
{
    public function index()
    {
        return response(AdvertisementBanner::orderBy('CreatedAt', 'desc')->paginate(10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Title' => 'required|string|max:255',
            'BannerImage' => 'required|image|mimes:jpeg,png,jpg|max:5000',
            'LinkUrl' => 'nullable|string|max:255',
            'Description' => 'nullable|string',
            'StartDate' => 'nullable|date',
            'EndDate' => 'nullable|date',
            'IsActive' => 'nullable',
        ]);

        $imageName = time() . '.' . $request->file('BannerImage')->extension();
        $path = $request->file('BannerImage')->move(public_path('/images/banners'), $imageName);
        $bannerImageUrl = sprintf("https://pamsika-api.dzuka-africa.org/images/banners/%s", $imageName);

        return response(AdvertisementBanner::create([
            'Title' => $request->get('Title'),
            'BannerImageUrl' => $bannerImageUrl,
            'LinkUrl' => $request->get('LinkUrl'),
            'Description' => $request->get('Description'),
            'IsActive' => $request->get('IsActive', 1),
            'StartDate' => $request->get('StartDate'),
            'EndDate' => $request->get('EndDate'),
        ]));
    }

    public function update(AdvertisementBanner $advertisementBanner, Request $request)
    {
        Log::info("The submitted data is:", $request->all());
        $request->validate([
            'Title' => 'required|string|max:255',
            'BannerImage' => 'sometimes|image|mimes:jpeg,png,jpg|max:5000',
            'LinkUrl' => 'nullable|string|max:255',
            'Description' => 'nullable|string',
            'StartDate' => 'nullable|date',
            'EndDate' => 'nullable|date',
            'IsActive' => 'nullable',
        ]);

        $advertisementBanner->update([
            'Title' => $request->get('Title'),
            'LinkUrl' => $request->get('LinkUrl'),
            'Description' => $request->get('Description'),
            'IsActive' => $request->get('IsActive'),
            'StartDate' => $request->get('StartDate'),
            'EndDate' => $request->get('EndDate'),
        ]);

        if ($request->hasFile('BannerImage')) {
            $request->validate([
                'BannerImage' => 'image|mimes:jpeg,png,jpg|max:5000',
            ]);

            $imageName = time() . '.' . $request->file('BannerImage')->extension();
            $path = $request->file('BannerImage')->move(public_path('/images/banners'), $imageName);
            $bannerImageUrl = sprintf("https://pamsika-api.dzuka-africa.org/images/banners/%s", $imageName);

            $advertisementBanner->update([
                'BannerImageUrl' => $bannerImageUrl,
            ]);
        }

        return response($advertisementBanner);
    }

    public function destroy(AdvertisementBanner $advertisementBanner)
    {
        return response($advertisementBanner->delete());
    }
}
