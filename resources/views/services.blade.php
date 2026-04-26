@extends('layouts.site')

@section('title', 'Our Services - Tumpat Solutions')

@section('content')
    <!-- Mini Header -->
    <div class="page-banner">
        <h1 class="page-banner-title">Our Services</h1>
        <p class="page-banner-subtitle">Comprehensive engineering solutions for modern infrastructure.</p>
    </div>

    <!-- Content Grid -->
    <div class="page-shell">
        <div class="service-grid">
            
            <!-- Service 1 -->
            <div class="content-box service-card">
                <h3 class="service-title service-title-green">Tower Construction</h3>
                <p class="service-copy">Full turnkey solutions for telecommunication towers, from soil investigation to final erection and commissioning.</p>
            </div>

            <!-- Service 2 -->
            <div class="content-box service-card">
                <h3 class="service-title service-title-orange">Fiber Optics</h3>
                <p class="service-copy">Nationwide fiber optic network deployment, splicing, and maintenance services for high-speed connectivity.</p>
            </div>

            <!-- Service 3 -->
            <div class="content-box service-card">
                <h3 class="service-title service-title-default">Civil Infrastructure</h3>
                <p class="service-copy">Roadworks, drainage, and foundation works supporting major utility installations across Malaysia.</p>
            </div>

        </div>

        <div class="capability-panel content-box">
            <h2 class="section-title">Telecommunication Engineering Services</h2>
            <p class="section-copy">Our teams deliver complete engineering support, from early planning to commissioning and long-term maintenance, for communication networks and connected infrastructure.</p>
            <div class="capability-grid">
                <div class="capability-item">
                    <h4>Wireless</h4>
                    <p>Site rollout and radio network support for reliable mobile coverage.</p>
                </div>
                <div class="capability-item">
                    <h4>Microwave</h4>
                    <p>Transmission links and backbone planning for high-availability operations.</p>
                </div>
                <div class="capability-item">
                    <h4>Fiber Optic</h4>
                    <p>End-to-end fiber deployment, splicing, testing, and restoration works.</p>
                </div>
                <div class="capability-item">
                    <h4>Project Management</h4>
                    <p>Coordinated execution, compliance monitoring, and quality delivery.</p>
                </div>
            </div>
        </div>

        <div class="industry-band">
            <p>Supporting mobile operators, broadband providers, utility sectors, and oil and gas communication projects nationwide.</p>
        </div>
    </div>
@endsection