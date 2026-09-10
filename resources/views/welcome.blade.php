@extends('layouts.site')

@section('title', 'Tumpat Solutions - Telecommunication & Infrastructure Engineering')

@section('content')
    <!-- Hero Section -->
    <header class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="badge-dot"></span>
                    <span>Nationwide Telecommunication & Civil Engineering Specialists</span>
                </div>
                <h1 class="hero-title">
                    CONNECTING<br>
                    <span class="hero-gradient-text">MALAYSIA'S</span><br>
                    DIGITAL FUTURE.
                </h1>
                <p class="sub-headline">
                    Turnkey engineering, procurement, installation, and commissioning of telecommunication networks and civil infrastructure since 2004.
                </p>
                <div class="hero-actions">
                    <a href="{{ route('services') }}" class="cta-button">
                        Explore Capabilities
                        <svg class="w-4 h-4 ml-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                    <a href="{{ route('contact') }}" class="cta-button-secondary">
                        Contact Engineers
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Key Metrics / Stats Ribbon -->
    <section class="metrics-ribbon">
        <div class="metrics-container">
            <div class="metric-item">
                <div class="metric-number">20+</div>
                <div class="metric-label">Years of Engineering Excellence</div>
            </div>
            <div class="metric-item">
                <div class="metric-number">500+</div>
                <div class="metric-label">Telecom Sites Completed</div>
            </div>
            <div class="metric-item">
                <div class="metric-number">100%</div>
                <div class="metric-label">Nationwide Coverage (Peninsular & Borneo)</div>
            </div>
            <div class="metric-item">
                <div class="metric-number">CIDB G5</div>
                <div class="metric-label">ISO 9001:2015 Accredited</div>
            </div>
        </div>
    </section>

    <!-- Company Overview & Mission Bento -->
    <section class="page-shell company-intro">
        <div class="intro-header text-center mx-auto">
            <p class="eyebrow">About Tumpat Solutions</p>
            <h2 class="section-title">End-to-End Telecom Infrastructure Partners</h2>
            <p class="section-copy max-w-3xl mx-auto text-center">
                Established in May 2004, Tumpat Solutions Sdn Bhd has grown to become a cornerstone of Malaysia's telecommunications infrastructure, delivering high-stakes engineering for mobile operators, broadband providers, and technology vendors nationwide.
            </p>
        </div>

        <div class="bento-grid">
            <!-- Bento 1: Tower Engineering -->
            <div class="bento-card bento-card-featured">
                <div class="bento-icon-box bg-orange-50 text-orange-600">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h3 class="bento-title">Turnkey Tower Construction</h3>
                <p class="bento-copy">
                    Complete civil, structural, and electrical (CME) delivery for Greenfield and Rooftop sites. From soil investigation and foundation design to 4-leg lattice tower and monopole erection.
                </p>
                <div class="bento-tags">
                    <span class="tag-pill">Greenfield Sites</span>
                    <span class="tag-pill">Rooftop Towers</span>
                    <span class="tag-pill">Soil Analysis</span>
                </div>
            </div>

            <!-- Bento 2: Fiber Optic Deployment -->
            <div class="bento-card">
                <div class="bento-icon-box bg-emerald-50 text-emerald-600">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="bento-title">Fiber Optics (OSP & ISP)</h3>
                <p class="bento-copy">
                    Nationwide fiber optic network expansion, including horizontal directional drilling (HDD), micro-trenching, blown fiber, core fusion splicing, and OTDR testing.
                </p>
                <div class="bento-tags">
                    <span class="tag-pill">HDD Drilling</span>
                    <span class="tag-pill">Fusion Splicing</span>
                </div>
            </div>

            <!-- Bento 3: 5G & Microwave -->
            <div class="bento-card">
                <div class="bento-icon-box bg-blue-50 text-blue-600">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                    </svg>
                </div>
                <h3 class="bento-title">5G & Microwave Transmission</h3>
                <p class="bento-copy">
                    Installation and commissioning of base stations (BTS), active antenna systems, and microwave transmission hops with precision link budgeting.
                </p>
                <div class="bento-tags">
                    <span class="tag-pill">5G Rollout</span>
                    <span class="tag-pill">Microwave Hops</span>
                </div>
            </div>

            <!-- Bento 4: 24/7 Managed Operations -->
            <div class="bento-card">
                <div class="bento-icon-box bg-indigo-50 text-indigo-600">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="bento-title">Managed Operations & Maintenance</h3>
                <p class="bento-copy">
                    Preventive maintenance cycles, emergency power generator dispatch, site rectification, and strict SLA compliance for mission-critical connectivity.
                </p>
                <div class="bento-tags">
                    <span class="tag-pill">24/7 Emergency</span>
                    <span class="tag-pill">SLA Guarantee</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Section: Two Decades of Growth -->
    <section id="timeline" class="timeline-section">
        <div class="timeline-heading">
            <p class="eyebrow">Our History</p>
            <h2>Two Decades of Building Malaysia</h2>
            <p>From a focused installation team in 2004 to a multi-disciplinary nationwide infrastructure provider.</p>
        </div>

        <div class="timeline-container">
            <div class="timeline-block left">
                <div class="content-box">
                    <span class="timeline-chip chip-blue">2004 - 2009</span>
                    <h3 class="timeline-title">The Foundation</h3>
                    <ul class="timeline-list">
                        <li>Incorporated Tumpat Solutions Sdn Bhd in Subang Jaya.</li>
                        <li>Awarded first tier-1 telecommunication installation contracts in Klang Valley.</li>
                        <li>Formed specialized rigger and transmission engineering teams.</li>
                    </ul>
                </div>
            </div>
            
            <div class="timeline-block right">
                <div class="content-box">
                    <span class="timeline-chip chip-green">2010 - 2014</span>
                    <h3 class="timeline-title">Regional Expansion</h3>
                    <ul class="timeline-list">
                        <li>Achieved ISO 9001 Quality Management System accreditation.</li>
                        <li>Expanded workforce to 50+ engineers, technicians, and riggers.</li>
                        <li>Launched permanent operating hubs in East Malaysia (Sabah & Sarawak).</li>
                    </ul>
                </div>
            </div>

            <div class="timeline-block left">
                <div class="content-box">
                    <span class="timeline-chip chip-indigo">2015 - 2019</span>
                    <h3 class="timeline-title">4G Rollout & Civil Scale</h3>
                    <ul class="timeline-list">
                        <li>Registered with CIDB Malaysia Grade G5 for mechanical, electrical, and civil works.</li>
                        <li>Key deployment contractor for nationwide 4G LTE cellular coverage expansions.</li>
                        <li>Completed over 1,500km of inter-city and metropolitan fiber optic trenching.</li>
                    </ul>
                </div>
            </div>

            <div class="timeline-block right">
                <div class="content-box">
                    <span class="timeline-chip chip-amber">2020 - Present</span>
                    <h3 class="timeline-title">5G & Smart Infrastructure</h3>
                    <ul class="timeline-list">
                        <li>Strategic deployment partner in Malaysia's 5G single wholesale network rollout.</li>
                        <li>Adopted digital asset tracking, geo-verified attendance, and telemetry systems.</li>
                        <li>Expanding solar-hybrid power implementations for off-grid telecommunication sites.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Industry Sectors Served -->
    <section class="page-shell sectors-section">
        <div class="text-center mb-12 mx-auto">
            <p class="eyebrow">Clients & Sectors</p>
            <h2 class="section-title">Trusted Across Critical Industries</h2>
            <p class="section-copy max-w-2xl mx-auto text-center">
                We work alongside major telecommunication operators, tower companies, and government utility enterprises.
            </p>
        </div>

        <div class="sectors-grid">
            <div class="sector-card">
                <div class="sector-icon">📱</div>
                <h4 class="sector-title">Mobile Operators</h4>
                <p class="sector-desc">RAN deployment, antenna upgrades, backhaul links, and 4G/5G swap projects.</p>
            </div>
            <div class="sector-card">
                <div class="sector-icon">🗼</div>
                <h4 class="sector-title">Tower Companies</h4>
                <p class="sector-desc">Site acquisition, structural strengthening, foundation works, and multi-tenant colocations.</p>
            </div>
            <div class="sector-card">
                <div class="sector-icon">⚡</div>
                <h4 class="sector-title">Utilities & Power</h4>
                <p class="sector-desc">Optical ground wire (OPGW) stringing, substation telemetry, and SCADA links.</p>
            </div>
            <div class="sector-card">
                <div class="sector-icon">🛢️</div>
                <h4 class="sector-title">Oil & Gas / Remote</h4>
                <p class="sector-desc">Offshore comms links, hazardous-area certified wiring, and remote microwave repeaters.</p>
            </div>
        </div>
    </section>

    <!-- Call to Action Banner -->
    <section class="cta-banner-section">
        <div class="cta-banner-card">
            <div class="cta-banner-content">
                <h2 class="cta-banner-title">Need Reliable Engineering Support For Your Network?</h2>
                <p class="cta-banner-copy">
                    From single-site rectifications to multi-state fiber trenching and 5G builds, our certified engineering teams are ready to mobilize.
                </p>
                <div class="cta-banner-buttons">
                    <a href="{{ route('contact') }}" class="cta-button">
                        Request Project Consultation
                    </a>
                    <a href="tel:+60356119916" class="cta-button-ghost">
                        Call +60 3-5611 9916
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection