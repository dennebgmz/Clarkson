<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf-token" content="{!! csrf_token() !!}">
    <meta content="text/html; charset=utf-8">
    <meta name="keywords" content="UP Provident Fund Inc. Members Portal">
    <meta name="description" content="UP Provident Fund Inc. Members Portal">
    <meta name="author" content="White Widget">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>UP-PROVIDENT FUND INC.</title>
    <link href="{{ asset('/dist/favicon.ico') }}" rel="icon">
    <link href="//cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.4.1/css/simple-line-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="{!! asset('dist/style.css') !!}" rel="stylesheet">
    <link href="{!! asset('dist/font-awesome-4.7.0/css/font-awesome.min.css') !!}" rel="stylesheet">
    <link href="{!! asset('dist/select2-4.0.13/css/select2.min.css') !!}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.css">
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        WebFont.load({
            google: {
                families: ['Fira Sans:300,400,500,600,700']
            }
        });
    </script>
    <script src="{{ asset('/dist/vendor.js') }}"></script>
    <script src="{{ asset('/dist/dashboard.js') }}"></script>
    <script src="{{ asset('/dist/select2-4.0.13/js/select2.min.js') }}"></script>
    <style>
        #loading {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            display: block;
            /* opacity: 0.7; */
            background-color: rgba(255, 255, 255, 0.9);
            z-index: 150;
        }

        #loading-image {
            position: absolute;
            margin-right:-50px;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            margin: auto;
            width: 200px;
           
        }

    </style>





</head>

<body id="uppfi">
    <div id="loading" class="mx-auto" style="display:none;">
        <img id="loading-image" src="{{ asset('/img/logo_gif_blue.gif') }}" alt="Loading..." />
    </div>
    @section('content')
    @show
    @yield('scripts')
</body>

</html>
