<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport">
    <meta name="description" content="Simple Bank">
    <meta name="author" content="mj">

    <!-- Title -->
    <title>Change Password</title>

    <!---Style css-->
    <link href="{{ asset ('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset ('assets/css/custom-style.css') }}" rel="stylesheet">
</head>

<body class="main-body">

    <!-- Loader -->
    <div id="global-loader">
        <img src="{{ asset ('assets/img/loader.svg') }}" class="loader-img" alt="Loader">
    </div>
    <!-- End Loader -->

    <!-- Page -->
    <div class="page main-signin-wrapper">

        <!-- Row -->
        <div class="row text-center pl-0 pr-0 ml-0 mr-0">
            <div class="col-lg-3 col-md-12 d-block mx-auto">
                <div class="card custom-card">
                    <div class="card-body">
                        <h4 class="text-center">Change Password</h4>
                        <div class="message"></div>
                        <form action="{{ url('change_password') }}" class="ajax-form-submit" method="post">
                            @csrf
                            <div class="form-group text-left mb-4">
                                <label>Old Password</label>
                                <input class="form-control" placeholder="Enter old password" name="password" type="text" autocomplete="off">
                                <span class="float-left tx-danger error_text password_error"></span>
                            </div>
                            <div class="form-group text-left mb-4">
                                <label>New Password</label>
                                <input class="form-control" placeholder="Enter new password" name="npassword" type="password" autocomplete="off">
                                <span class="float-left tx-danger error_text npassword_error"></span>
                            </div>
                            <div class="form-group text-left mb-4">
                                <label>Confirm Password</label>
                                <input class="form-control" placeholder="Confirm new password" name="cpassword" type="password" autocomplete="off">
                                <span class="float-left tx-danger error_text cpassword_error"></span>
                            </div>
                            <div class="error-msg tx-danger"></div>
                            <div class="form_proccessing tx-success float-left"></div>
                            <button class="btn ripple btn-main-primary btn-block" id="save_data" type="submit">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Row -->

    </div>
    <!-- End Page -->

    <!-- Jquery js-->
    <script src="{{ asset ('assets/plugins/jquery/jquery.min.js') }}"></script>

    <!-- Bootstrap js-->
    <script src="{{ asset ('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Ionicons js-->
    <script src="{{ asset ('assets/plugins/ionicons/ionicons.js') }}"></script>

    <!-- Rating js-->
    <script src="{{ asset ('assets/plugins/rating/jquery.rating-stars.js') }}"></script>

    <!-- Custom js-->
    <script src="{{ asset ('assets/js/custom.js') }}"></script>

    <script type="text/javascript">
        $('.ajax-form-submit').on('submit', function(e) {
            $('#save_data').prop('disabled', true);
            $('.error-msg').html('');
            $('.form_proccessing').html('Please wait...');
            e.preventDefault();
            var aurl = $(this).attr('action');
            var form = $(this);
            var formdata = false;
            if (window.FormData) {
                formdata = new FormData(form[0]);
            }
            $.ajax({
                type: "POST",
                url: aurl,
                cache: false,
                contentType: false,
                processData: false,
                data: formdata ? formdata : form.serialize(),
                success: function(response) {
                    if (response.st == 'success') {
                        $('.message').html('<div class="alert alert-success">' + response.msg + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
                        window.location.href = "{{URL::to('/party_list')}}"
                    } else if (response.st == 'failed') {
                        $('.message').html('<div class="alert alert-info">' + response.msg + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
                        $('.form_proccessing').html('');
                        $('#save_data').prop('disabled', false);
                    } else {
                        $('.form_proccessing').html('');
                        $('#save_data').prop('disabled', false);
                        $.each(response.error, function(prefix, val) {
                            $('span.' + prefix + '_error').text('This field is required').show().delay(5000).fadeOut();
                        });
                    }
                },
                error: function() {
                    $('#save_data').prop('disabled', false);
                    alert('Error');
                }
            });
            return false;
        });
    </script>

</body>

</html>