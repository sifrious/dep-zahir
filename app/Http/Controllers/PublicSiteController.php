<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'business' => config('business'),
            'products' => config('products'),
        ]);
    }

    public function product(string $product): View
    {
        $definition = config("products.{$product}");

        abort_unless($definition, 404);

        return view('public.product', [
            'business' => config('business'),
            'productKey' => $product,
            'product' => $definition,
        ]);
    }

    public function documentation(string $product): View
    {
        $definition = config("products.{$product}");

        abort_unless($definition && isset($definition['documentation']), 404);

        return view('public.documentation', [
            'productKey' => $product,
            'product' => $definition,
        ]);
    }

    public function pricing(): View
    {
        return view('public.pricing', [
            'products' => config('products'),
        ]);
    }

    public function billing(): View
    {
        return view('public.billing', [
            'business' => config('business'),
            'products' => config('products'),
        ]);
    }
}
