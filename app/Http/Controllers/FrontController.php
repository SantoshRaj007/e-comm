<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index() {
        $products = Product::where('is_featured','Yes')->orderBy('id','DESC')->where('status',1)->take(4)->get();
        $data['featuredProducts'] = $products;

        $letestProducts = Product::orderBy('id','DESC')->where('status',1)->take(4)->get();
        $data['letestProducts'] = $letestProducts;
        return view('front.home',$data);
    }
}
