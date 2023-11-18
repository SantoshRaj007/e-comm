<?php
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\BrandsController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\ShippingController;
use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\DiscountCodeController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\PageController;
use App\Http\Controllers\admin\TempImagesController;
use App\Http\Controllers\admin\SubCategoryController;
use App\Http\Controllers\admin\ProductImageController;
use App\Http\Controllers\admin\ProductSubCategoryController;
use App\Http\Controllers\admin\SettingController;
use App\Http\Controllers\admin\UserController;

// Route::get('/test', function () {
//     orderEmail(18);
// });


// Route::controller(FrontController::class)->group(function(){
//     Route::get('/','index')->name('front.home');
//     Route::get('/product','product')->name('front.product');
// });

Route::get('/',[FrontController::class,'index'])->name('front.home');
Route::get('/shop/{categorySlug?}/{subCategorySlug?}',[ShopController::class,'index'])->name('front.shop');

Route::get('product/{slug}',[ShopController::class,'product'])->name('front.product');
Route::get('/cart',[CartController::class,'cart'])->name('front.cart');
Route::post('/add-to-cart',[CartController::class,'addToCart'])->name('front.addToCart');
Route::post('/update-cart',[CartController::class,'updateCart'])->name('front.updateCart');
Route::post('/delete-item',[CartController::class,'deleteItem'])->name('front.deleteItem.cart');
Route::get('/checkout',[CartController::class,'checkout'])->name('front.checkout');
Route::post('/process-checkout',[CartController::class,'processCheckout'])->name('front.processCheckout');
Route::get('/thanks/{orderId}',[CartController::class,'thankyou'])->name('front.thankyou');
Route::post('/get-order-summery',[CartController::class,'getOrderSummery'])->name('front.getOrderSummery');
Route::post('/apply-discount',[CartController::class,'applyDiscount'])->name('front.applyDiscount');
Route::post('/remove-discount',[CartController::class,'removeCoupon'])->name('front.removeCoupon');
Route::post('/add-to-wishlist',[FrontController::class,'addToWishlist'])->name('front.addToWishlist');
Route::get('/page/{slug}',[FrontController::class,'page'])->name('front.page');
Route::post('/send-contact-email',[FrontController::class,'sendContactEmail'])->name('front.sendContactEmail');

Route::get('/forgot-password',[AuthController::class,'forgotPassword'])->name('front.forgotPassword');
Route::post('/process-forgot-password',[AuthController::class,'processForgotPassword'])->name('front.processForgotPassword');
Route::get('/reset-password/{token}',[AuthController::class,'resetPassword'])->name('front.resetPassword');
Route::post('/process-reset-password',[AuthController::class,'processResetPassword'])->name('front.processResetPassword');
Route::post('/save-rating/{productId}',[ShopController::class,'saveRating'])->name('front.saveRating');



// Register Route

Route::group(['prefix' => 'account'],function(){
    Route::group(['middleware' => 'guest'],function(){
        Route::get('/register',[AuthController::class,'register'])->name('account.register');
        Route::post('/process-register',[AuthController::class,'processRegister'])->name('account.processRegister');
        Route::get('/login',[AuthController::class,'login'])->name('account.login');
        Route::post('/login',[AuthController::class,'authenticate'])->name('account.authenticate');       

    });

    Route::group(['middleware' => 'auth'],function(){
        Route::get('/profile',[AuthController::class,'profile'])->name('account.profile');
        Route::post('/update-profile',[AuthController::class,'updateProfile'])->name('account.updateProfile');
        Route::post('/update-address',[AuthController::class,'updateAddress'])->name('account.updateAddress');
        Route::get('/change-password',[AuthController::class,'showChangePasswordForm'])->name('account.changePassword');
        Route::post('/process-change-password',[AuthController::class,'changePassword'])->name('account.processChangePassword');

        Route::get('/my-orders',[AuthController::class,'orders'])->name('account.orders');
        Route::get('/my-wishlists',[AuthController::class,'wishlist'])->name('account.wishlist');
        Route::post('/remove-product-from-wishlist',[AuthController::class,'removeProductFromWishList'])->name('account.removeProductFromWishList');
        Route::get('/order-detail/{orderId}',[AuthController::class,'orderDetail'])->name('account.orderDetail');
        Route::get('/logout',[AuthController::class,'logout'])->name('account.logout');
    });

});


Route::group(['prefix' => 'admin'],function(){
    Route::group(['middleware' => 'admin.guest'],function(){
        Route::get('/login',[AdminLoginController::class,'index'])->name('admin.login');
        Route::post('/authenticate',[AdminLoginController::class,'authenticate'])->name('admin.authenticate');
        
        Route::get('/forgot-password',[AdminLoginController::class,'forgotPassword'])->name('admin.forgotPassword');
        Route::post('/process-forgot-password',[AdminLoginController::class,'processForgotPassword'])->name('admin.processForgotPassword');
        Route::get('/reset-password/{token}',[AdminLoginController::class,'resetPassword'])->name('admin.resetPassword');
        Route::post('/process-reset-password',[AdminLoginController::class,'processResetPassword'])->name('admin.processResetPassword');

    });

    Route::group(['middleware' => 'admin.auth'],function(){

        Route::get('/dashboard',[HomeController::class,'index'])->name('admin.dashboard');        
        Route::get('/logout',[HomeController::class,'logout'])->name('admin.logout');    
        
        // Category Route
        Route::get('/categories',[CategoryController::class,'index'])->name('categories.index');        
        Route::get('/categories/create',[CategoryController::class,'create'])->name('categories.create');        
        Route::get('/categories/{category}/edit',[CategoryController::class,'edit'])->name('categories.edit');        
        Route::post('/categories',[CategoryController::class,'store'])->name('categories.store'); 
        Route::put('/categories/{category}',[CategoryController::class,'update'])->name('categories.update'); 
        Route::delete('/categories/{category}',[CategoryController::class,'destroy'])->name('categories.delete');          

        // Sub-Category Route

        Route::get('/sub-categories',[SubCategoryController::class,'index'])->name('sub-categories.index'); 
        Route::get('/sub-categories/create',[SubCategoryController::class,'create'])->name('sub-categories.create');
        Route::post('/sub-categories',[SubCategoryController::class,'store'])->name('sub-categories.store');           
        Route::get('/sub-categories/{subCategory}/edit',[SubCategoryController::class,'edit'])->name('sub-categories.edit');
        Route::put('/sub-categories/{subCategory}',[SubCategoryController::class,'update'])->name('sub-categories.update'); 
        Route::delete('/sub-categories/{subCategory}',[SubCategoryController::class,'destroy'])->name('sub-categories.delete');

        // Brand Route
        Route::get('/brands',[BrandsController::class,'index'])->name('brands.index');
        Route::get('/brands/create',[BrandsController::class,'create'])->name('brands.create');
        Route::post('/brands',[BrandsController::class,'store'])->name('brands.store');     
        Route::get('/brands/{brand}/edit',[BrandsController::class,'edit'])->name('brands.edit');   
        Route::put('/brands/{brand}',[BrandsController::class,'update'])->name('brands.update'); 
        Route::delete('/brands/{brand}',[BrandsController::class,'destroy'])->name('brands.delete');

        // Product Route 
        Route::get('/products',[ProductController::class,'index'])->name('products.index');
        Route::get('/products/create',[ProductController::class,'create'])->name('products.create');
        Route::post('/products',[ProductController::class,'store'])->name('products.store');  
        Route::get('/products/{product}/edit',[ProductController::class,'edit'])->name('products.edit');
        Route::put('/products/{product}',[ProductController::class,'update'])->name('products.update'); 
        Route::delete('/products/{product}',[ProductController::class,'destroy'])->name('products.delete'); 
        Route::get('/get-products',[ProductController::class,'getProducts'])->name('products.getProducts');
        Route::get('/ratings',[ProductController::class,'productRatings'])->name('products.productRatings');
        Route::get('/change-rating-status',[ProductController::class,'changeRatingStatus'])->name('products.changeRatingStatus');


        // Product Rating

        Route::get('/product-rating',[ShopController::class,'list'])->name('rating.index');
        Route::get('/product-rating/{rating}/edit',[ShopController::class,'edit'])->name('rating.edit');
        Route::put('/product-rating/{rating}',[ShopController::class,'update'])->name('rating.update'); 
        Route::delete('/product-rating/{rating}',[ShopController::class,'destroy'])->name('rating.delete');


        Route::get('/product-subcategories',[ProductSubCategoryController::class,'index'])->name('product-subcategories.index');

        Route::post('/product-images/update',[ProductImageController::class,'update'])->name('product-images.update');  
        Route::delete('/product-images',[ProductImageController::class,'destroy'])->name('product-images.destroy');  

        // Shipping Routes

        // Route::get('/shipping',[ShippingController::class,'index'])->name('shipping.index');
        Route::get('/shipping/create',[ShippingController::class,'create'])->name('shipping.create');
        Route::post('/shipping',[ShippingController::class,'store'])->name('shipping.store');
        Route::get('/shipping/{id}/edit',[ShippingController::class,'edit'])->name('shipping.edit');
        Route::put('/shipping/{id}',[ShippingController::class,'update'])->name('shipping.update');
        Route::delete('/shipping/{id}',[ShippingController::class,'destroy'])->name('shipping.delete');
        
        // Order Routes

        Route::get('/orders',[OrderController::class,'index'])->name('orders.index');
        Route::get('/orders/{id}',[OrderController::class,'detail'])->name('orders.detail');
        Route::post('/order/change-status/{id}',[OrderController::class,'changeOrderStatus'])->name('orders.changeOrderStatus');
        Route::post('/order/send-email/{id}',[OrderController::class,'sendInvoiceEmail'])->name('orders.sendInvoiceEmail');

        // Discount Coupon Route

        Route::get('/coupons',[DiscountCodeController::class,'index'])->name('coupons.index');
        Route::get('/coupons/create',[DiscountCodeController::class,'create'])->name('coupons.create');
        Route::post('/coupons',[DiscountCodeController::class,'store'])->name('coupons.store');  
        Route::get('/coupons/{coupon}/edit',[DiscountCodeController::class,'edit'])->name('coupons.edit');
        Route::put('/coupons/{coupon}',[DiscountCodeController::class,'update'])->name('coupons.update'); 
        Route::delete('/coupons/{coupon}',[DiscountCodeController::class,'destroy'])->name('coupons.delete');

        // User Route

        Route::get('/users',[UserController::class,'index'])->name('users.index');
        Route::get('/users/create',[UserController::class,'create'])->name('users.create');
        Route::post('/users',[UserController::class,'store'])->name('users.store');     
        Route::get('/users/{user}/edit',[UserController::class,'edit'])->name('users.edit');   
        Route::put('/users/{user}',[UserController::class,'update'])->name('users.update'); 
        Route::delete('/users/{user}',[UserController::class,'destroy'])->name('users.delete');

        // Page Route

        Route::get('/pages',[PageController::class,'index'])->name('pages.index');
        Route::get('/pages/create',[PageController::class,'create'])->name('pages.create');
        Route::post('/pages',[PageController::class,'store'])->name('pages.store');     
        Route::get('/pages/{page}/edit',[PageController::class,'edit'])->name('pages.edit');   
        Route::put('/pages/{page}',[PageController::class,'update'])->name('pages.update'); 
        Route::delete('/pages/{page}',[PageController::class,'destroy'])->name('pages.delete');


        // temp-images.create 
        Route::post('/upload-temp-image',[TempImagesController::class,'create'])->name('temp-images.create');  

        // Setting Routes 

        Route::get('/change-password',[SettingController::class,'showChangePasswordForm'])->name('admin.showChangePasswordForm'); 

        // Process Change Password Route

        Route::post('/process-change-password',[SettingController::class,'processChangePassword'])->name('admin.processChangePassword'); 
        
        Route::get('/getSlug',function(Request $request){
            $slug = '';
            if (!empty($request->title)) {
                $slug = Str::slug($request->title);            
            }
            return response()->json([
                'status' => true,
                'slug' => $slug
            ]);
        })->name('getSlug');
    });
    
});