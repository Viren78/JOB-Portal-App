@extends('front.layouts.app')

@section('main')
<section class="section-5">
    <div class="container my-5">
        <div class="py-lg-2">&nbsp;</div>
        <div class="row d-flex justify-content-center">
            <div class="col-md-5">
                <div class="card shadow border-0 p-5">
                    <h1 class="h3">Register</h1>
                    <form action="" name="registrationForm" id="registrationForm">
                        {{-- @csrf --}}
                        <div class="mb-3">
                            <label for="" class="mb-2">Name*</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Enter Name">
                            <p></p>
                        </div> 
                        <div class="mb-3">
                            <label for="" class="mb-2">Email*</label>
                            <input type="text" name="email" id="email" class="form-control" placeholder="Enter Email">
                            <p></p>
                        </div> 
                        <div class="mb-3">
                            <label for="" class="mb-2">Password*</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter Password">
                            <p></p>
                        </div> 
                        <div class="mb-3">
                            <label for="" class="mb-2">Confirm Password*</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Please Confirm Password">
                            <p></p>
                        </div> 
                        <button type="submit" class="btn btn-primary mt-2">Register</button>
                    </form>                    
                </div>
                <div class="mt-4 text-center">
                    <p>Have an account? <a  href="{{ route('account.login') }}">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('customJS')
    <script>
        $('#registrationForm').submit(function(e){
            e.preventDefault();

            $.ajax({
                type: "post",
                url: "{{ route('account.processRegistration') }}",
                data: $(this).serialize(), // Serialize form data
                dataType: "json",
                success: function (response) {
                    if(response.status == false){
                        var errors = response.errors;
                        $('#name').toggleClass('is-invalid', errors.name)
                                .siblings('p').html(errors.name || '');
                        $('#email').toggleClass('is-invalid', errors.email)
                                .siblings('p').html(errors.email || '');
                        $('#password').toggleClass('is-invalid', errors.password)
                                    .siblings('p').html(errors.password || '');
                        $('#confirm_password').toggleClass('is-invalid', errors.confirm_password)
                                            .siblings('p').html(errors.confirm_password || '');
                    } else {
                        $('#name').removeClass('is-invalid')
                        .siblings('p')  
                        .removeClass('invalid-feedback')
                        .html('')

                        $('#email').removeClass('is-invalid')
                        .siblings('p')  
                        .removeClass('invalid-feedback')
                        .html('')

                        $('#password').removeClass('is-invalid')
                        .siblings('p')  
                        .removeClass('invalid-feedback')
                        .html('')

                        $('#confirm_password').removeClass('is-invalid')
                        .siblings('p')  
                        .removeClass('invalid-feedback')
                        .html('')
                        window.location.href='{{ route("account.login") }}'
                    }
                }
            });
        });

    </script>
@endsection