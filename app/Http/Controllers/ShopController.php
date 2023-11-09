<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\assertNotTrue;

class ShopController extends Controller
{
    public function index(Request $request, $categorySlug = null, $subCategorySlug = null) {

        $categorySelected = '';
        $subCategorySelected = '';
        $brandArray = [];        

        $categories = Category::orderBy('name','ASC')->with('sub_category')->where('status',1)->get();
        $brands = Brand::orderBy('name','ASC')->where('status',1)->get();
        $products = Product::where('status',1);

        // Apply Filters here

        if (!empty($categorySlug)) {
            $category = Category::where('slug',$categorySlug)->first();
            $products = $products->where('category_id',$category->id);
            $categorySelected = $category->id;
        }

        if (!empty($subCategorySlug)) {
            $subCategory = SubCategory::where('slug',$subCategorySlug)->first();
            $products = $products->where('sub_category_id',$subCategory->id);
            $subCategorySelected = $subCategory->id;
        }

        if (!empty($request->get('brand'))) {
            $brandArray = explode(',',$request->get('brand'));
            $products = $products->whereIn('brand_id',$brandArray);
        }

        if ($request->get('price_max') != '' && $request->get('price_min') != '') {
            if ($request->get('price_max') == 1000) {
                $products = $products->whereBetween('price',[intval($request->get('price_min')),10000]);
            } else {
                $products = $products->whereBetween('price',[intval($request->get('price_min')),intval($request->get('price_max'))]);
            }          
        }

        if (!empty($request->get('search'))) {
            $products = $products->where('title','like','%'.$request->get('search').'%');
        }

        
        if($request->get('sort') != '') {
            if($request->get('sort') == 'letest'){
                $products =$products->orderBY('id','DESC');
            } else if ($request->get('sort') == 'price_asc'){
                $products =$products->orderBY('price','ASC');
            } else {
                $products =$products->orderBY('price','DESC');
            }
        } else {
            $products = $products->orderBy('id','DESC');
        }


        $products = $products->paginate(9);

        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['products'] = $products;
        $data['categorySelected'] = $categorySelected;
        $data['subCategorySelected'] = $subCategorySelected;
        $data['brandArray'] = $brandArray;
        $data['priceMax'] = (intval($request->get('price_max')) == 0) ? 1000 : $request->get('price_max');
        $data['priceMin'] = intval($request->get('price_min'));
        $data['sort'] = $request->get('sort');

        return view('front.shop',$data);
    }

    // Product Page 

    public function product($slug) {

        $product = Product::where('slug',$slug)
        ->withCount('product_ratings')
        ->withSum('product_ratings','rating')
        ->with('product_images','product_ratings')->first();        
        // dd($product);
        if ($product == null) {
            abort(404);
        }

        
        $relatedProducts = [];
        // Fetch related products
        if ($product->related_products != '') {
            $productArray = explode(',',$product->related_products);
            $relatedProducts = Product::whereIn('id',$productArray)->get();
        }

        $data['product'] = $product;
        $data['relatedProducts'] =$relatedProducts;

        // Rating Calculation
        //"product_ratings_count" => 1
        //"product_ratings_sum_rating" => 5.0

        $avgRating = '0.00';
        $avgRatingPer = '0';
        if($product->product_ratings_count > 0) {
            $avgRating = number_format(($product->product_ratings_sum_rating/$product->product_ratings_count),2);
            $avgRatingPer = ($avgRating*100)/5;
        }
        
        $data['avgRating'] = $avgRating;
        $data['avgRatingPer'] = $avgRatingPer;
        
        return view('front.product',$data);

    }

    public function saveRating($id,Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email',
            'rating' => 'required',
            'comment' => 'required',            
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $count = ProductRating::where('email',$request->email)->count();
        if($count > 0) {
            session()->flash('error','You already rated this product..');
            return response()->json([
                'status' => true,
            ]);
        }

        $productRating = new ProductRating;
        $productRating->product_id = $id;
        $productRating->username = $request->name;
        $productRating->email = $request->email;
        $productRating->comment = $request->comment;
        $productRating->rating = $request->rating;
        $productRating->status = 0;
        $productRating->save();

        session()->flash('success','Thanks for your rating...');

        return response()->json([
            'status' => true,
            'message' => 'Thanks for your rating...'
        ]);

    }

    public function list(Request $request) {
        $ratings = ProductRating::orderBy('id');
        if(!empty($request->get('keyword'))){
            $ratings = $ratings->where('username','like','%'.$request->get('keyword').'%');
        }
        $ratings = $ratings->paginate(10);
        return view('admin.ratings.list',[
            'ratings' => $ratings
        ]);
    }

    public function edit($id) {
        $ratings = ProductRating::find($id);

        if($ratings == null){
            $message = 'Page not found';
            session()->flash('error',$message);
            return redirect()->route('rating.index');
        }

        return view('admin.ratings.edit',[
            'ratings' => $ratings
        ]);
    }

    public function update(Request $request, $id){
        $ratings = ProductRating::find($id);
        
        if($ratings == null){
            $message = 'Page not found';
            session()->flash('error',$message);
            
            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name' => 'required',
        ]);

        if ($validator->passes()) {
               
            $ratings->username = $request->name;
            $ratings->status = $request->status;
            $ratings->save();   
            
            $message = 'Rating updated successfully';
            session()->flash('success',$message);

            return response()->json([
                'status' => true,
                'message' => $message
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($id){
        $ratings = ProductRating::find($id);

        if($ratings == null){
            $message = 'Page not found';
            session()->flash('error',$message);
            
            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        }

        $ratings->delete();

        $message = 'Rating deleted successfully';
        session()->flash('success',$message);
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }
}
