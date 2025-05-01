<!DOCTYPE html>
<html lang="en">
<head>
    {{-- Header include --}}
    @include('layouts.header')
</head>
<body>

    <!--*******************
        Preloader start
    ********************-->
    {{-- <div id="preloader">
        <div>
            <img src="images/pre.gif" alt=""> 
        </div>
    </div> --}}
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">
        {{-- Navbar include --}}
        @include('layouts.navbar')

        {{-- Sidebar include --}}
        @include('layouts.sidebar')
        
        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            @yield('content')
        </div>
        
        {{-- Footer include --}}
        @include('layouts.footer')

    </div>

    {{-- Add this line just before the closing body tag --}}
    @stack('scripts')

</body>
</html>
