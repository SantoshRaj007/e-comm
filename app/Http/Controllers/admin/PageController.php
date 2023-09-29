<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    public function index(Request $request) {
        $pages = Page::latest();
        if(!empty($request->get('keyword'))){
            $pages = $pages->where('name','like','%'.$request->get('keyword').'%');
        }
        $pages = $pages->paginate(10);

        return view('admin.page.list',[
            'pages' => $pages
        ]);
    }

    public function create() {
        return view('admin.page.create');
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:pages',
            'content' =>'required',
        ]);

        if ($validator->passes()) {

            $page = new Page();
            $page->name = $request->name;
            $page->slug = $request->slug;
            $page->content = $request->content;
            $page->status = $request->status;
            $page->save();   
            
            $message = 'Page added successfully';
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

    public function edit($id) {
        $page = Page::find($id);

        if($page == null){
            $message = 'Page not found';
            session()->flash('error',$message);
            return redirect()->route('pages.index');
        }

        return view('admin.page.edit',[
            'page' => $page
        ]);
    }

    public function update(Request $request, $id){
        $page = Page::find($id);
        
        if($page == null){
            $message = 'Page not found';
            session()->flash('error',$message);
            
            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:pages,slug,'.$page->id.',id',
            'content' => 'required',
        ]);

        if ($validator->passes()) {
               
            $page->name = $request->name;
            $page->slug = $request->slug;           
            
            $page->content = $request->content;
            $page->status = $request->status;
            $page->save();   
            
            $message = 'Page updated successfully';
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
        $page = Page::find($id);

        if($page == null){
            $message = 'Page not found';
            session()->flash('error',$message);
            
            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        }

        $page->delete();

        $message = 'Page deleted successfully';
        session()->flash('success',$message);
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }
}
