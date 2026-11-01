<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InquiryController extends Controller
{
    /**
     * GET /api/v1/inquiries/options
     * Values for the "Product line" dropdown of the contact form.
     */
    public function options()
{
    return response()->json([
        'product_lines' => Category::active()->roots()->ordered()->get(['id', 'name', 'slug'])
            ->map(fn ($c) => ['value' => $c->name, 'label' => $c->name, 'slug' => $c->slug])
            ->push(['value' => 'Other', 'label' => 'Other / not sure', 'slug' => 'other'])
            ->values(),
    ]);
}

    /**
     * POST /api/v1/inquiries
     * Receives the website contact form ("Send a request").
     */
    public function store(Request $request)
    {
        // Honeypot: real visitors never fill a hidden "website" field.
        if (filled($request->input('website'))) {
            return response()->json(['message' => 'Thank you. We reply within one working day.'], 201);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:180'],
            'whatsapp' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\-\s.]{6,40}$/'],
            'product_line' => ['required', 'string', 'max:120'],
            'quantity' => ['nullable', 'string', 'max:60'],
            'destination_country' => ['required', 'string', 'max:120'],
            'customisation' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'source_url' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Please tell us who we are speaking to.',
            'email.required' => 'Please add your email so we can send the quote.',
            'product_line.required' => 'Please choose a product line.',
            'destination_country.required' => 'Please tell us where the order ships.',
            'message.required' => 'Please tell us what you need made.',
            'whatsapp.regex' => 'Please enter a valid WhatsApp number including country code.',
        ]);

        $category = Category::where('name', $data['product_line'])
            ->orWhere('slug', Str::slug($data['product_line']))
            ->first();

        $inquiry = Inquiry::create($data + [
            'category_id' => $category?->id,
            'status' => 'new',
            'source_url' => $data['source_url'] ?? $request->headers->get('referer'),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 490, ''),
        ]);

        return response()->json([
            'message' => 'Thank you. We reply within one working day.',
            'data' => ['id' => $inquiry->id],
        ], 201);
    }
}
