<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport">
    <meta name="description" content="Simple Bank">
    <meta name="author" content="mj">
    <!-- Favicon -->
    <!-- Title -->
    <title>Login</title>
    <!---Style css-->
    <link href="{{ asset ('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset ('assets/css/custom-style.css') }}" rel="stylesheet">
</head>

<body>
    <!-- Loader -->
    <div id="global-loader">
        <img src="{{ asset ('assets/img/loader.svg') }}" class="loader-img" alt="Loader">
    </div>
    <!-- End Loader -->
    <div class="page main-signin-wrapper">
        <div class="row text-center pl-0 pr-0 ml-0 mr-0">
            <div class="col-lg-3 d-block mx-auto">
                <div class="text-center mb-2">
                    <h3>{{ env('BOOK_NAME') }}</h3>
                    <!-- <img src="{{ asset ('assets/images/logo.png') }}" class="header-brand-img" alt="logo"> -->
                </div>
                <div class="card custom-card">
                    <div class="card-body">
                        <form action="{{ url('login') }}" method="post">
                            @if (Session::has('success'))
                            <div class="alert alert-success">{{ Session::get('success') }}</div>
                            @endif
                            @if (Session::has('fail'))
                            <div class="alert  alert-info alert-dismissible fade show" role="alert">
                                {{ Session::get('fail') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            @endif
                            @csrf
                            <div class="form-group text-left">
                                <label>User Name</label>
                                <input class="form-control" placeholder="enter user name" value="" type="text" name="user_name" autocomplete="off" required>
                            </div>
                            <div class="form-group text-left">
                                <label>Password</label>
                                <input class="form-control" placeholder="enter your password" value="" type="password" name="password" autocomplete="off" required>
                            </div>
                            <button class="btn ripple btn-main-primary btn-block" name="signin">Log In</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    </script>
</body>

</html>