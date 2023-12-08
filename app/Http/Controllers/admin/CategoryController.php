<?php

namespace App\Http\Controllers\admin;

use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request){
        // $categories = Category::latest();
        $categories = Category::orderBy('id');
        if (!empty($request->get('keyword'))){
            $categories = $categories->where('name','like','%'.$request->get('keyword').'%');
        }
        
        $categories = $categories->paginate(10);
        
        return view('admin.category.category',compact('categories'));
    }

    public function create(){
        return view('admin.category.create');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);
        if($validator->passes()){
            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showHome = $request->showHome;
            $category->save();

            // Save Image
            if(!empty($request->image_id)){
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.',$tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id.'.'.$ext;
                $sPath = public_path().'/temp/'.$tempImage->name;
                $dPath = public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath,$dPath);

                // Generate Image Thumbnail
                $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
                $img = Image::make($sPath);
                // $img->resize(450, 600);
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($dPath);


                $category->image = $newImageName;
                $category->save();
            }

            // $request->session()->flash('success','Category added successfully');
            session()->flash('success','Category added successfully');

            return response()->json([
                'status' => true,
                'errors' => 'Category added successfully'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($categoryId, Request $request){
        $category = Category::find($categoryId);
        if(empty($category)){
            return redirect()->route('categories.index');
        }
        return view('admin.category.edit',compact('category'));
    }

    public function update($categoryId, Request $request){

        $category = Category::find($categoryId);

        if(empty($category)){
            session()->flash('error','Category not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not found'
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'.$category->id.',id',
        ]);
        if($validator->passes()){
            
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showHome = $request->showHome;
            $category->save();

            $oldImage = $category->image;

            // Save Image
            if(!empty($request->image_id)){
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.',$tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id.'-'.time().'.'.$ext;
                $sPath = public_path().'/temp/'.$tempImage->name;
                $dPath = public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath,$dPath);

                // Generate Image Thumbnail
                $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
                $img = Image::make($sPath);
                // $img->resize(450, 600);
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($dPath);


                $category->image = $newImageName;
                $category->save();

                // Delete old images

                File::delete(public_path().'/uploads/category/'.$oldImage);
                File::delete(public_path().'/uploads/category/thumb/'.$oldImage);
            }

            // $request->session()->flash('success','Category updated successfully');
            session()->flash('success','Category updated successfully');

            return response()->json([
                'status' => true,
                'errors' => 'Category updated successfully'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($categoryId, Request $request){
        $category = Category::find($categoryId);
        if(empty($category)){
            session()->flash('error','Category not found');
            return response()->json([
                'status' => true,
                'message' => 'Category not found'
            ]);
        }

        File::delete(public_path().'/uploads/category/'.$category->image);
        File::delete(public_path().'/uploads/category/thumb/'.$category->image);

        $category->delete();

        session()->flash('success','Category deleted successfully');
        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully'
        ]);

    }
}
