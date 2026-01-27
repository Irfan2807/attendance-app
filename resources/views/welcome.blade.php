@extends('layouts.site')

@section('title', 'Home - Tumpat Solutions')

@section('content')
    <!-- Hero Section -->
    <header class="hero" style="
        background-color: #222; 
        background-image: 
            linear-gradient(to right, rgba(0, 0, 0, 0.6) 0%, rgba(0, 0, 0, 0.2) 100%), 
            url('/images/hero-bg.jpg'); 
        ">
        <div class="hero-container">
            <div class="hero-content">
                <h1>CONNECTING<br>MALAYSIA'S<br>FUTURE.</h1>
                <p class="sub-headline">Expert Engineering & Civil Infrastructure Since 2005.</p>
                <a href="{{ route('services') }}" class="cta-button">Explore Our Services</a>
            </div>
        </div>
    </header>

    <!-- Timeline Section -->
    <section id="timeline" class="timeline-section">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 style="font-size: 2.5rem; font-weight: 800; color: #111;">Our Journey</h2>
            <p style="color: #666; font-size: 1.1rem; margin-top: 10px;">Two decades of building the nation's infrastructure.</p>
        </div>

        <div class="timeline-container">
            <!-- Timeline Items -->
            <div class="timeline-block left">
                <div class="content-box">
                    <span style="display:inline-block; padding: 4px 12px; background: #DBEAFE; color: #1E40AF; border-radius: 99px; font-weight: bold; font-size: 0.8rem; margin-bottom: 10px;">2005 - 2010</span>
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: #111;">The Foundation</h3>
                    <ul style="padding-left: 20px; list-style-type: disc; color: #555;">
                        <li>Established Tumpat Solutions Sdn Bhd.</li>
                        <li>Secured first major installation contracts.</li>
                    </ul>
                </div>
            </div>
            
            <div class="timeline-block right">
                <div class="content-box">
                    <span style="display:inline-block; padding: 4px 12px; background: #DCFCE7; color: #166534; border-radius: 99px; font-weight: bold; font-size: 0.8rem; margin-bottom: 10px;">2010 - 2015</span>
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: #111;">Building Expertise</h3>
                    <ul style="padding-left: 20px; list-style-type: disc; color: #555;">
                        <li>Achieved ISO 9001 Certification.</li>
                        <li>Expanded engineering team to 50+ staff.</li>
                    </ul>
                </div>
            </div>

            <div class="timeline-block left">
                <div class="content-box">
                    <span style="display:inline-block; padding: 4px 12px; background: #F3E8FF; color: #6B21A8; border-radius: 99px; font-weight: bold; font-size: 0.8rem; margin-bottom: 10px;">2015 - 2020</span>
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: #111;">National Impact</h3>
                    <ul style="padding-left: 20px; list-style-type: disc; color: #555;">
                        <li>Attained CIDB Grade G5 status.</li>
                        <li>Led nationwide 4G network rollout.</li>
                    </ul>
                </div>
            </div>

             <div class="timeline-block right">
                <div class="content-box">
                    <span style="display:inline-block; padding: 4px 12px; background: #FFEDD5; color: #9A3412; border-radius: 99px; font-weight: bold; font-size: 0.8rem; margin-bottom: 10px;">2020 - 2025</span>
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: #111;">Future Ready</h3>
                    <ul style="padding-left: 20px; list-style-type: disc; color: #555;">
                        <li>Celebrating 20 Years of Excellence.</li>
                        <li>Key partner in 5G network deployment.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endsection