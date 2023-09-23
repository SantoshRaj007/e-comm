<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request) {
        $orders = Order::latest();
        if (!empty($request->get('keyword'))){
            $orders = $orders->where('first_name','like','%'.$request->get('keyword').'%');
        }
        
        $orders = $orders->paginate(10);
        return view('admin.order.list',compact('orders'));
    }
}
