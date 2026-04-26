<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Tumpat Solutions')</title>
    
    <!-- Fonts & Styles -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800|space-grotesk:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans text-gray-900 bg-white site-shell">

    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo">
            <img src="/images/logo.png" onerror="this.style.display='none'" alt="Logo" id="logo-img" style="height: 100px; margin-right: 10px;">
            <!--<div>
                <span style="color:var(--brand-green)">TUMPAT</span><span style="color:var(--brand-orange)">SOLUTIONS</span>
            </div>-->
        </div>

        <ul class="nav-links">
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('services') }}">Services</a></li>
            <li><a href="{{ route('contact') }}">Contact Us</a></li>
            <li><a href="{{ route('login') }}" class="nav-btn">Login</a></li>
            
            <!-- 
               SECRET LOGIC:
               Only show the "Dashboard" button if the user is ALREADY logged in.
               If they are a guest, show nothing. They must know the secret URL to log in.
            -->
           
        </ul>
    </nav>

    <!-- Page Content -->
    @yield('content')

    <!-- Footer -->
    <footer class="site-footer">
        <div class="site-footer-grid">
            <div class="site-footer-col">
                <div class="logo">
                    <img src="/images/logo.png" onerror="this.style.display='none'" alt="Logo" id="logo-img" style="height: 100px; margin-right: 10px;">
                </div>
                <!--<h3 style="color: var(--brand-orange); font-weight: 800; text-transform: uppercase; margin-bottom: 20px;">Tumpat Solutions</h3>-->
                <p class="site-footer-muted">Building Malaysia's future since 2005.</p>
            </div>
            <div class="site-footer-col">
                <h4 class="site-footer-title">Quick Links</h4>
                <ul class="site-footer-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('services') }}">Services</a></li>
                    <li><a href="{{ route('contact') }}">Contact Us</a></li>
                </ul>
            </div>
            <div class="site-footer-col">
                <h4 class="site-footer-title">Contact</h4>
                <p class="site-footer-muted">
                    Tumpat Solutions Sdn Bhd<br>Kuala Lumpur, Malaysia<br><br>
                    <strong>Phone:</strong> 03-5611 9916<br>
                    <strong>Email:</strong> enquiry@tumpatsolutions.com
                </p>
            </div>
        </div>
        <div class="site-footer-bottom">
            &copy; {{ date('Y') }} Tumpat Solutions Sdn Bhd. All rights reserved.
        </div>
    </footer>

</body>
</html>