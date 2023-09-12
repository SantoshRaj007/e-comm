<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandsController extends Controller
{
    // Index Function

    public function index(Request $request){
        // $brands = Brand::latest();
        $brands = Brand::orderBy('id');
        if (!empty($request->get('keyword'))){
            $brands = $brands->where('name','like','%'.$request->get('keyword').'%');
        }
        
        $brands = $brands->paginate(10);
        
        return view('admin.brands.brands',compact('brands'));
    }

    // Create Function

    public function create(){
        return view('admin.brands.create');
    }

    // Store Data Function

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands'
        ]);

        if ($validator->passes()){
            $brands = new Brand();
            $brands->name = $request->name;
            $brands->slug = $request->slug;
            $brands->status = $request->status;
            $brands->save();

            session()->flash('success','Brand Category added successfully..!!!');

            return response([
                'status' => true,
                'errors' => 'Brand Category added successfully..!!!'
            ]);

        } else {
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // Edit Data Function

    public function edit($id, Request $request){
        $brands = Brand::find($id);
        if(empty($brands)){
            return redirect()->route('brands.index');
        }
        return view('admin.brands.edit',compact('brands'));
    }

    // Update Record & Data Function

    public function update($id, Request $request){
        $brands = Brand::find($id);

        if(empty($brands)){
            $request->session()->flash('error','Category not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not found'
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,'.$brands->id.',id',
        ]);
        if($validator->passes()){
            
            $brands->name = $request->name;
            $brands->slug = $request->slug;
            $brands->status = $request->status;
            $brands->save();

            // $request->session()->flash('success','Category updated successfully');
            session()->flash('success','Brands updated successfully');

            return response()->json([
                'status' => true,
                'errors' => 'Brands updated successfully'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // Delete Record & Data Function

    public function destroy($id, Request $request){
        $brands = Brand::find($id);
        if(empty($brands)){
            $request->session()->flash('error','Brand not found');
            return response()->json([
                'status' => true,
                'message' => 'Brand not found'
            ]);
        }

        $brands->delete();

        $request->session()->flash('success','Brand deleted successfully');
        return response()->json([
            'status' => true,
            'message' => 'Brand deleted successfully'
        ]);
    }
}
