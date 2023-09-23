<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Country;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\CustomerAddress;
use App\Models\DiscountCoupon;
use App\Models\ShippingCharge;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addToCart(Request $request) {
        $product = Product::with('product_images')->find($request->id);

        if ($product == null) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ]);
        }


        if (Cart::count() > 0) {
            //echo "Product already in cart";
            //Products found in cart
            //Check if this product already in the cart
            //Return as message that product already added in your cart
            //if product not found in the cart then add product in cart

            $cartContent = Cart::content();
            $productAlreadyExist = false;

            foreach($cartContent  as $item) {
                if ($item->id == $product->id) {
                    $productAlreadyExist = true;
                }
            }
            
            if ($productAlreadyExist  == false) {
                Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);

                $status = true;
                $message = '<strong>'.$product->title.'</strong> added in your cart successfully';
                session()->flash('success',$message);
                
            } else {
                $status = false;
                $message = $product->title.' adready added in cart ';
            }

        } else {
            
            
            Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);

            $status = true;
            $message = '<strong>'.$product->title.'</strong> added in your cart successfully';

            session()->flash('success',$message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);      

    }

    public function cart() {

        $cartContent = Cart::content();
        // dd($cartContent);
        $data['cartContent'] = $cartContent;
        return view('front.cart',$data);
    }

    public function updateCart(Request $request) {
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);

        $product = Product::find($itemInfo->id);

        // Check qty availabel in stock

        if($product->track_qty == 'Yes'){
            if ($qty <= $product->qty) {
                Cart::update($rowId, $qty);
                $message = 'Cart update successfully';
                $status = true;
            } else {
                $message = 'Requested qty('.$qty.') not available in stock';
                $status = false;
                session()->flash('error',$message);
            }
        } else {
            Cart::update($rowId, $qty);
            $message = 'Cart update successfully';
            $status = true;
            session()->flash('success',$message);
        }

        
        return response ()->json([
            'status' => true,
            'message' => $message
        ]);
    } 

    public function deleteItem(Request $request){

        $itemInfo = Cart::get($request->rowId);

        if ($itemInfo == null) {
            $errorMessage = 'Item not found in  cart';
            session()->flash('error',$errorMessage);
            return response ()->json([
                'status' => false,
                'message' => $errorMessage
            ]);
        }

        Cart::remove($request->rowId);

        $message = 'Item remove from cart successfully';

        session()->flash('success',$message);
        return response ()->json([
            'status' => true,
            'message' => $message
        ]);
    }

    public function checkout(){

        $discount = 0;

        // if cart is empty redirect to cart page
        if (Cart::count() == 0) {
            return redirect()->route('front.cart');
        }

        // -- If user in not logged-in then redirect to login page

        if (Auth::check() == false) {
            if(!session()->has('url.intended')){
                session(['url.intended' => url()->current()]);
            }            
            return redirect()->route('account.login');
        }

        $customerAddress = CustomerAddress::where('user_id',Auth::user()->id)->first();

        session()->forget('url.intended');

        $countries = Country::orderBy('name','ASC')->get();

        $subTotal = Cart::subtotal(2,'.','');

        // Apply discount here

        if (session()->has('code')) {
            $code = session()->get('code');
            if($code->type == 'percent'){
                $discount = ($code->discount_amount/100)*$subTotal;
            } else {
                $discount = $code->discount_amount;
            }
        }

        // Calculate shipping here

        if($customerAddress != ''){
            $userCountry = $customerAddress->country_id;

            $shippingInfo = ShippingCharge::where('country_id',$userCountry)->first();

            // echo $shippingInfo->amount;
            $totalQty = 0;
            $totalShippingCharge = 0;
            $grandTotal = 0;
            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }

            $totalShippingCharge = $totalQty*$shippingInfo->amount;
            $grandTotal = ($subTotal-$discount)+$totalShippingCharge;
        } else {
            $grandTotal = ($subTotal-$discount);
            $totalShippingCharge = 0;
        }

        


        return view('front.checkout',[
            'countries' => $countries,
            'customerAddress' => $customerAddress,
            'totalShippingCharge' => $totalShippingCharge,
            'discount' => $discount,
            'grandTotal' => $grandTotal
        ]);
    }

    public function processCheckout(Request $request) {

        // step - 1 user Apply validation

        $validator = Validator::make($request->all(),[
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            // 'address' => 'required|min:30',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'mobile' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please fix the error',
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        // step - 2 save user address
        
        // $customerAddress = CustomerAddress::find();
        $user = Auth::user();
        CustomerAddress::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country,
                'address' => $request->address,
                'apartment' => $request->apartment,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
            ]
        );

        // step - 3 store data in orders table

        if ($request->payment_method == 'cod') {

            $discountCodeId = '';
            $promoCode = '';
            $shipping = 0;
            $discount = 0;
            $subTotal = Cart::subtotal(2,'.','');

            // Apply discount here

            if (session()->has('code')) {
                $code = session()->get('code');
                if($code->type == 'percent'){
                    $discount = ($code->discount_amount/100)*$subTotal;
                } else {
                    $discount = $code->discount_amount;
                }

                $discountCodeId = $code->id;
                $promoCode = $code->code;
            }

            //  Calculate shipping
            $shippingInfo = ShippingCharge::where('country_id',$request->country)->first();
            $totalQty = 0;
            foreach (Cart::content() as $item){
                $totalQty += $item->qty;
            }

            if($shippingInfo != null) {
                $shipping = $totalQty*$shippingInfo->amount;
                $grandTotal = ($subTotal-$discount)+$shipping;
                
            } else {
 
                $shippingInfo = ShippingCharge::where('country_id','rest_of_world')->first();
                $shipping = $totalQty*$shippingInfo->amount;
                $grandTotal = ($subTotal-$discount)+$shipping;
            } 
                    
            
            $order = new Order;
            $order->subtotal = $subTotal;
            $order->shipping = $shipping;
            $order->grand_total = $grandTotal;
            $order->discount = $discount;
            $order->coupon_code_id = $discountCodeId;
            $order->coupon_code = $promoCode;
            $order->user_id = $user->id;
            $order->first_name = $request->first_name;
            $order->last_name = $request->last_name;
            $order->email = $request->email;
            $order->mobile = $request->mobile;
            $order->address = $request->address;
            $order->apartment = $request->apartment;
            $order->state = $request->state;
            $order->city = $request->city;
            $order->zip = $request->zip;
            $order->notes = $request->order_notes;
            $order->country_id = $request->country;
            $order->save();

            // step - 4 store order item in order_item table

            foreach (Cart::content() as $item) {
                $orderItem = new OrderItem;
                $orderItem->product_id = $item->id;
                $orderItem->order_id = $order->id;
                $orderItem->name = $item->name;
                $orderItem->qty = $item->qty;
                $orderItem->price = $item->price;
                $orderItem->total = $item->price*$item->qty;
                $orderItem->save();
            }

            session()->flash('success','You have successfully placed your order.');
            
            Cart::destroy();

            session()->forget('code');

            return response()->json([
                'message' => 'Order saved successfully',
                'orderId' => $order->id,
                'status' => true
            ]);

            

        } else {
            //
        }

        

    }

    public function thankyou($id){
        return view('front.thanks',[
            'id' => $id
        ]);
    }

    public function getOrderSummery(Request $request){
        $subTotal = Cart::subtotal(2,'.','');
        $discount = 0;
        $discountString = '';
        // Apply discount here

        if (session()->has('code')) {
            $code = session()->get('code');
            if($code->type == 'percent'){
                $discount = ($code->discount_amount/100)*$subTotal;
            } else {
                $discount = $code->discount_amount;
            }

            $discountString = '<div class="mt-4" id="discount-response">
                <strong>'.session()->get('code')->code.'</strong>
                <a class="btn btn-sm btn-danger" id="remove-discount"><i class="fa fa-times"></i></a>
            </div>';
        }       


        if($request->country_id > 0) {            
            $shippingInfo = ShippingCharge::where('country_id',$request->country_id)->first();

            $totalQty = 0;
            foreach (Cart::content() as $item){
                $totalQty += $item->qty;
            }

            if($shippingInfo != null) {
                $shippingCharge = $totalQty*$shippingInfo->amount;
                $grandTotal = ($subTotal-$discount)+$shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal,2),
                    'discount' => $discount,
                    'discountString' => $discountString,
                    'shippingCharge' => number_format($shippingCharge,2),
                ]);
            } else {

                $shippingInfo = ShippingCharge::where('country_id','rest_of_world')->first();

                $shippingCharge = $totalQty*$shippingInfo->amount;
                $grandTotal = ($subTotal-$discount)+$shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal,2),
                    'discount' => $discount,
                    'discountString' => $discountString,
                    'shippingCharge' => number_format($shippingCharge,2),
                ]);
            }

        } else {
            return response()->json([
                'status' => true,
                'grandTotal' => number_format(($subTotal-$discount),2),
                'discount' => $discount,
                'discountString' => $discountString,
                'shippingCharge' => number_format(0,2),
            ]);
        }
    }

    public function applyDiscount(Request $request){
        // dd($request->code);

        $code = DiscountCoupon::where('code',$request->code)->first();

        if($code == null) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid discount coupon',
            ]);
        }

        // check if coupon start date is valid or not

        $now = Carbon::now();
        // echo $now->format('Y-m-d H:i:s');

        if($code->starts_at != "") {
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s',$code->starts_at);

            if($now->lt($startDate)){
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon',
                ]);
            }
        }

        if($code->expires_at != "") {
            $endtDate = Carbon::createFromFormat('Y-m-d H:i:s',$code->expires_at);

            if($now->gt($endtDate)){
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon',
                ]);
            }
        }

        session()->put('code',$code);

        return $this->getOrderSummery($request);


    }

    public function removeCoupon(Request $request) {
        session()->forget('code');
        return $this->getOrderSummery($request);
    }
}
 