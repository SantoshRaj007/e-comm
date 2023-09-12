<?php
    use App\Models\Category;
    function getcategories() {
        return Category::orderBy('name','ASC')
        ->with('sub_category')
        ->orderBy('id','DESC')
        ->where('status',1)
        ->where('showHome','Yes')
        ->get();
    }

?>