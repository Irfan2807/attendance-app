@extends('layouts.site')

@section('title', 'Home - Tumpat Solutions')

@section('content')
    <!-- Hero Section -->
    <header class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1>CONNECTING<br>MALAYSIA'S<br>FUTURE.</h1>
                <p class="sub-headline">Expert Engineering & Civil Infrastructure Since 2005.</p>
                <a href="{{ route('services') }}" class="cta-button">Explore Our Services</a>
            </div>
        </div>
    </header>

    <section class="page-shell company-intro">
        <div class="split-card-grid">
            <article class="content-box intro-card">
                <p class="eyebrow">Who We Are</p>
                <h2 class="section-title">Tumpat Solutions Sdn Bhd</h2>
                <p class="section-copy">We provide telecommunication network engineering services across design, installation, maintenance, consultancy, and project management for modern communication systems.</p>
                <p class="section-copy">Established in May 2004, our teams support nationwide projects including Sabah and Sarawak for mobile operators, broadband providers, and technology vendors.</p>
            </article>
            <article class="content-box intro-card">
                <p class="eyebrow">Core Strengths</p>
                <ul class="feature-list">
                    <li>Wireless engineering for mobile and transmission networks.</li>
                    <li>Optical communications expertise across ISP and OSP infrastructure.</li>
                    <li>Network management system planning and implementation.</li>
                    <li>Engineering services for utility and oil and gas communication rollout.</li>
                </ul>
            </article>
        </div>
    </section>

    <!-- Timeline Section -->
    <section id="timeline" class="timeline-section">
        <div class="timeline-heading">
            <h2>Our Journey</h2>
            <p>Two decades of building the nation's infrastructure.</p>
        </div>

        <div class="timeline-container">
            <!-- Timeline Items -->
            <div class="timeline-block left">
                <div class="content-box">
                    <span class="timeline-chip chip-blue">2005 - 2010</span>
                    <h3 class="timeline-title">The Foundation</h3>
                    <ul class="timeline-list">
                        <li>Established Tumpat Solutions Sdn Bhd.</li>
                        <li>Secured first major installation contracts.</li>
                    </ul>
                </div>
            </div>
            
            <div class="timeline-block right">
                <div class="content-box">
                    <span class="timeline-chip chip-green">2010 - 2015</span>
                    <h3 class="timeline-title">Building Expertise</h3>
                    <ul class="timeline-list">
                        <li>Achieved ISO 9001 Certification.</li>
                        <li>Expanded engineering team to 50+ staff.</li>
                    </ul>
                </div>
            </div>

            <div class="timeline-block left">
                <div class="content-box">
                    <span class="timeline-chip chip-indigo">2015 - 2020</span>
                    <h3 class="timeline-title">National Impact</h3>
                    <ul class="timeline-list">
                        <li>Attained CIDB Grade G5 status.</li>
                        <li>Led nationwide 4G network rollout.</li>
                    </ul>
                </div>
            </div>

             <div class="timeline-block right">
                <div class="content-box">
                    <span class="timeline-chip chip-amber">2020 - 2025</span>
                    <h3 class="timeline-title">Future Ready</h3>
                    <ul class="timeline-list">
                        <li>Celebrating 20 Years of Excellence.</li>
                        <li>Key partner in 5G network deployment.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endsection