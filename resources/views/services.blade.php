@extends('layouts.site')

@section('title', 'Our Services - Tumpat Solutions')

@section('content')
    <!-- Mini Header -->
    <div style="background-color: #111; padding-top: 120px; padding-bottom: 60px; text-align: center; color: white;">
        <h1 style="font-size: 3rem; font-weight: 800; text-transform: uppercase;">Our Services</h1>
        <p style="color: #ccc; max-width: 600px; margin: 0 auto;">Comprehensive engineering solutions for modern infrastructure.</p>
    </div>

    <!-- Content Grid -->
    <div style="max-width: 1000px; margin: 60px auto; padding: 0 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            
            <!-- Service 1 -->
            <div class="content-box" style="padding: 40px; border-radius: 12px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 style="color: var(--brand-green); font-size: 1.5rem; font-weight: 700; margin-bottom: 15px;">Tower Construction</h3>
                <p style="color: #666; line-height: 1.6;">Full turnkey solutions for telecommunication towers, from soil investigation to final erection and commissioning.</p>
            </div>

            <!-- Service 2 -->
            <div class="content-box" style="padding: 40px; border-radius: 12px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 style="color: var(--brand-orange); font-size: 1.5rem; font-weight: 700; margin-bottom: 15px;">Fiber Optics</h3>
                <p style="color: #666; line-height: 1.6;">Nationwide fiber optic network deployment, splicing, and maintenance services for high-speed connectivity.</p>
            </div>

            <!-- Service 3 -->
            <div class="content-box" style="padding: 40px; border-radius: 12px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 style="color: #333; font-size: 1.5rem; font-weight: 700; margin-bottom: 15px;">Civil Infrastructure</h3>
                <p style="color: #666; line-height: 1.6;">Roadworks, drainage, and foundation works supporting major utility installations across Malaysia.</p>
            </div>

        </div>
    </div>
@endsection