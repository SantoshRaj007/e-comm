@extends('admin.layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">					
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Product Rating</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('rating.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content">
    <!-- Default box -->
    <div class="container-fluid">
        @include('admin.message')
        <form action="" method="post" id="productRatingForm" name="productRatingForm">
            
            <div class="card">
                <div class="card-body">								
                    <div class="row">   
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name">Name</label>
                                <input type="text" value="{{ $ratings->username }}" name="name" id="name" class="form-control" placeholder="Name">	
                                <p></p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option {{ ($ratings->status == 1) ? 'selected' : '' }} value="1">Active</option>
                                    <option  {{ ($ratings->status == 0) ? 'selected' : '' }} value="0">Block</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </div>
                </div>							
            </div>
        </form>
    </div>
    <!-- /.card -->
</section>
<!-- /.content -->

@endsection

@section('customJs')
<script>
    $("#productRatingForm").submit(function(event){
            event.preventDefault();

            $.ajax({
                url: '{{ route("rating.update",$ratings->id) }}',
                type: 'put',
                data: $(this).serializeArray(),
                dataType: 'json',
                success: function(response) {
                    if (response.status == true) {

                        $("#name")
                            .removeClass('is-invalid')
                            .siblings('p')
                            .removeClass('invalid-feedback')
                            .html('');

                        window.location.href = '{{ route("rating.index") }}';
                    } else {
                        var errors = response.errors;
                        if(errors.name) {
                            $("#name").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.name);
                        }else {
                            $("#name").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                        }
                    }
                }
            });
        });
    
</script>
@endsection