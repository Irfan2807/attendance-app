<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Tumpat Solutions')</title>
    
    <!-- Fonts & Styles -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans text-gray-900 bg-white">

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
            <li><a href="{{ route('home') }}#timeline">Our Journey</a></li>
            <li><a href="{{ route('contact') }}">Contact Us</a></li>
            
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
    <footer style="background-color: #111; color: white; padding: 60px 20px; margin-top: auto;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 50px;">
            <div style="flex: 1; min-width: 250px;">
                <div class="logo">
                    <img src="/images/logo.png" onerror="this.style.display='none'" alt="Logo" id="logo-img" style="height: 100px; margin-right: 10px;">
                </div>
                <!--<h3 style="color: var(--brand-orange); font-weight: 800; text-transform: uppercase; margin-bottom: 20px;">Tumpat Solutions</h3>-->
                <p style="color: #999; line-height: 1.6;">Building Malaysia's future since 2005.</p>
            </div>
            <div style="flex: 1; min-width: 250px;">
                <h4 style="font-weight: 700; margin-bottom: 20px;">Quick Links</h4>
                <ul style="color: #999; line-height: 2;">
                    <li><a href="{{ route('home') }}" style="color: #999; text-decoration: none;">Home</a></li>
                    <li><a href="{{ route('services') }}" style="color: #999; text-decoration: none;">Services</a></li>
                    <li><a href="{{ route('contact') }}" style="color: #999; text-decoration: none;">Contact Us</a></li>
                </ul>
            </div>
            <div style="flex: 1; min-width: 250px;">
                <h4 style="font-weight: 700; margin-bottom: 20px;">Contact</h4>
                <p style="color: #999; line-height: 1.6;">
                    123, Jalan Telekom<br>50480 Kuala Lumpur<br>Malaysia<br><br>
                    <strong>Email:</strong> info@tumpat.com
                </p>
            </div>
        </div>
        <div style="text-align: center; color: #555; margin-top: 50px; padding-top: 20px; border-top: 1px solid #333;">
            &copy; {{ date('Y') }} Tumpat Solutions Sdn Bhd. All rights reserved.
        </div>
    </footer>

</body>
</html>