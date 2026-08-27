<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'business' => config('business'),
        ]);
    }

    public function product(string $product): View
    {
        $definition = config("business.products.{$product}");

        abort_unless($definition, 404);

        return view('public.product', [
            'business' => config('business'),
            'product' => $definition,
        ]);
    }
}
