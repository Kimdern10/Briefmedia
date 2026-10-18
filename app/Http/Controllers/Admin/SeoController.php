<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function edit()
    {
        $seo = SeoSetting::first();

        return view('admin.seo.edit', compact('seo'));
    }

    public function update(Request $request)
    {
        $seo = SeoSetting::first() ?? new SeoSetting;

        $data = $request->except(['og_image', 'twitter_image', 'favicon', 'site_logo']);

        // These columns can't be empty: use the usual defaults when the field is left blank
        $data['robots_meta'] = trim((string) $request->input('robots_meta')) ?: 'index, follow';
        $data['og_type'] = trim((string) $request->input('og_type')) ?: 'website';
        $data['twitter_card'] = trim((string) $request->input('twitter_card')) ?: 'summary_large_image';

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
