<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Tumpat Solutions Sdn Bhd - Engineering, procurement, installation, and commissioning of telecommunication and civil infrastructure across Malaysia since 2004.">
    <meta name="theme-color" content="#13181c">

    <title>@yield('title', 'Tumpat Solutions - Telecommunication & Infrastructure Engineering')</title>
    
    <!-- Google / Bunny Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800|space-grotesk:500,600,700" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans text-gray-900 bg-slate-50 site-shell flex flex-col min-h-screen">

    <!-- Top Announcement Bar -->
    <div class="top-bar">
        <div class="top-bar-container">
            <div class="top-bar-item">
                <svg class="top-bar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                <span>+60 3-5611 9916</span>
            </div>
            <div class="top-bar-item">
                <svg class="top-bar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span>enquiry@tumpatsolutions.com</span>
            </div>
            <div class="top-bar-item top-bar-badge">
                <span>ISO 9001:2015 &bull; CIDB Malaysia Certified</span>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="navbar" id="site-navbar">
        <div class="nav-container">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="nav-brand" aria-label="Tumpat Solutions Homepage">
                <img src="/images/logo.png" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'" alt="Tumpat Solutions Logo" class="nav-logo-img">
                <div class="nav-fallback-logo" style="display: none;">
                    <span class="brand-green">TUMPAT</span><span class="brand-orange">SOLUTIONS</span>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <ul class="nav-links">
                <li>
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}">
                        Home
                    </a>
                </li>
                <li>
                    <a href="{{ route('services') }}" class="nav-link {{ request()->routeIs('services') ? 'nav-link-active' : '' }}">
                        Services
                    </a>
                </li>
                <li>
                    <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'nav-link-active' : '' }}">
                        Contact Us
                    </a>
                </li>
            </ul>

            <!-- Right Actions (Auth / Portal) -->
            <div class="nav-actions">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ url('/admin') }}" class="portal-badge portal-badge-admin" title="Admin Dashboard">
                            <span class="portal-indicator"></span>
                            Admin Portal
                        </a>
                    @else
                        <a href="{{ url('/staff') }}" class="portal-badge portal-badge-staff" title="Staff Dashboard">
                            <span class="portal-indicator"></span>
                            Staff Portal
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="nav-btn-login">
                        <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Portal Login
                    </a>
                @endauth

                <!-- Mobile Menu Button -->
                <button type="button" class="mobile-toggle" id="mobile-toggle-btn" aria-label="Toggle mobile menu" aria-expanded="false">
                    <svg class="w-6 h-6 icon-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg class="w-6 h-6 icon-close" style="display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div class="mobile-menu" id="mobile-menu" style="display: none;">
            <ul class="mobile-nav-list">
                <li>
                    <a href="{{ route('home') }}" class="mobile-nav-link {{ request()->routeIs('home') ? 'mobile-nav-link-active' : '' }}">
                        Home
                    </a>
                </li>
                <li>
                    <a href="{{ route('services') }}" class="mobile-nav-link {{ request()->routeIs('services') ? 'mobile-nav-link-active' : '' }}">
                        Services
                    </a>
                </li>
                <li>
                    <a href="{{ route('contact') }}" class="mobile-nav-link {{ request()->routeIs('contact') ? 'mobile-nav-link-active' : '' }}">
                        Contact Us
                    </a>
                </li>
                <li class="mobile-portal-item">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ url('/admin') }}" class="mobile-portal-btn mobile-portal-admin">
                                Go to Admin Portal
                            </a>
                        @else
                            <a href="{{ url('/staff') }}" class="mobile-portal-btn mobile-portal-staff">
                                Go to Staff Portal
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="mobile-portal-btn mobile-portal-guest">
                            Portal Login
                        </a>
                    @endauth
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Rich Corporate Footer -->
    <footer class="site-footer">
        <div class="site-footer-grid">
            <!-- Col 1: Company Profile -->
            <div class="site-footer-col">
                <div class="footer-brand">
                    <img src="/images/logo.png" onerror="this.style.display='none'; this.nextElementSibling.style.display='block'" alt="Logo" class="footer-logo-img">
                    <h3 class="footer-fallback-title" style="display: none;">Tumpat Solutions</h3>
                </div>
                <p class="site-footer-muted">
                    Engineering, procurement, installation, and commissioning of telecommunication and civil infrastructure across Malaysia since 2004.
                </p>
                <div class="footer-certifications">
                    <span class="cert-pill">ISO 9001:2015</span>
                    <span class="cert-pill">CIDB Grade G5</span>
                    <span class="cert-pill">NIOSH Compliant</span>
                </div>
            </div>

            <!-- Col 2: Core Services -->
            <div class="site-footer-col">
                <h4 class="site-footer-title">Engineering Services</h4>
                <ul class="site-footer-list">
                    <li><a href="{{ route('services') }}#towers">Tower Construction & CME</a></li>
                    <li><a href="{{ route('services') }}#fiber">Fiber Optic OSP / ISP Rollout</a></li>
                    <li><a href="{{ route('services') }}#wireless">5G & Microwave Transmission</a></li>
                    <li><a href="{{ route('services') }}#civil">Civil & Utility Infrastructure</a></li>
                    <li><a href="{{ route('services') }}#maintenance">24/7 Managed Site Operations</a></li>
                </ul>
            </div>

            <!-- Col 3: Navigation Links -->
            <div class="site-footer-col">
                <h4 class="site-footer-title">Quick Links</h4>
                <ul class="site-footer-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('services') }}">Our Capabilities</a></li>
                    <li><a href="{{ route('contact') }}">Contact Engineers</a></li>
                    <li><a href="{{ route('login') }}">Staff & Contractor Portal</a></li>
                </ul>
            </div>

            <!-- Col 4: Corporate Contact -->
            <div class="site-footer-col">
                <h4 class="site-footer-title">Headquarters</h4>
                <div class="footer-contact-info">
                    <p class="site-footer-muted">
                        <strong>Tumpat Solutions Sdn Bhd</strong><br>
                        No. 12, Jalan TP 5, Taman Perindustrian UEP,<br>
                        47600 Subang Jaya, Selangor, Malaysia
                    </p>
                    <p class="site-footer-muted mt-3">
                        <strong>Phone:</strong> <a href="tel:+60356119916" class="hover:text-amber-400">+60 3-5611 9916</a><br>
                        <strong>Email:</strong> <a href="mailto:enquiry@tumpatsolutions.com" class="hover:text-amber-400">enquiry@tumpatsolutions.com</a><br>
                        <strong>Hours:</strong> Mon - Fri, 8:30 AM - 5:30 PM
                    </p>
                </div>
            </div>
        </div>

        <div class="site-footer-bottom">
            <div class="footer-bottom-container">
                <p>&copy; {{ date('Y') }} Tumpat Solutions Sdn Bhd (Co. Reg: 651234-X). All rights reserved.</p>
                <div class="footer-bottom-links">
                    <span>Telecommunication Engineering Excellence</span>
                    <button type="button" class="back-to-top" id="back-to-top-btn" aria-label="Back to top">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                        </svg>
                        Top
                    </button>
                </div>
            </div>
        </div>
    </footer>

    <!-- Vanilla Client Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile Menu Toggle
            const toggleBtn = document.getElementById('mobile-toggle-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const iconOpen = toggleBtn ? toggleBtn.querySelector('.icon-open') : null;
            const iconClose = toggleBtn ? toggleBtn.querySelector('.icon-close') : null;

            if (toggleBtn && mobileMenu) {
                toggleBtn.addEventListener('click', function () {
                    const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
                    toggleBtn.setAttribute('aria-expanded', !isExpanded);
                    if (isExpanded) {
                        mobileMenu.style.display = 'none';
                        if (iconOpen) iconOpen.style.display = 'block';
                        if (iconClose) iconClose.style.display = 'none';
                    } else {
                        mobileMenu.style.display = 'block';
                        if (iconOpen) iconOpen.style.display = 'none';
                        if (iconClose) iconClose.style.display = 'block';
                    }
                });
            }

            // Navbar background elevation on scroll
            const navbar = document.getElementById('site-navbar');
            if (navbar) {
                window.addEventListener('scroll', function () {
                    if (window.scrollY > 20) {
                        navbar.classList.add('navbar-scrolled');
                    } else {
                        navbar.classList.remove('navbar-scrolled');
                    }
                }, { passive: true });
            }

            // Back to Top button
            const backToTopBtn = document.getElementById('back-to-top-btn');
            if (backToTopBtn) {
                backToTopBtn.addEventListener('click', function () {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }
        });
    </script>
</body>
</html>