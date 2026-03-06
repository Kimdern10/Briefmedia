<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeoSetting;

class SeoController extends Controller
{
    public function edit()
    {
        $seo = SeoSetting::first();
        return view('admin.seo.edit', compact('seo'));
    }

    public function update(Request $request)
    {
        $seo = SeoSetting::first() ?? new SeoSetting();

        $data = $request->except(['og_image', 'twitter_image', 'favicon', 'site_logo']);

        // Handle file uploads
        if ($request->hasFile('og_image')) {
            $data['og_image'] = $request->file('og_image')->store('seo', 'public');
        }

        if ($request->hasFile('twitter_image')) {
            $data['twitter_image'] = $request->file('twitter_image')->store('seo', 'public');
        }

        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon')->store('seo', 'public');
        }

        if ($request->hasFile('site_logo')) {
            $data['site_logo'] = $request->file('site_logo')->store('seo', 'public');
        }

        $seo->fill($data)->save();

        return back()->with('success', 'SEO Settings Updated Successfully');
    }
}