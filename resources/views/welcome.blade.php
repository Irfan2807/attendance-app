@extends('layouts.site')

@section('title', 'Tumpat Solutions - Telecommunication & Infrastructure Engineering')

@section('content')
    <!-- Hero Section -->
    <header class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <!-- Accreditation & Verification Pill -->
                <div class="hero-badge">
                    <span class="badge-dot"></span>
                    <span>CIDB Malaysia Grade G5 Registered • ISO 9001:2015 Accredited</span>
                </div>

                <!-- Grounded, High-Readability Editorial Headline -->
                <h1 class="hero-title">
                    Telecommunications & Civil Infrastructure Engineering
                </h1>

                <!-- Professional Sub-Headline -->
                <p class="sub-headline">
                    Delivering turnkey civil engineering, greenfield tower construction, optical fiber networks, and radio transmission systems across Malaysia since 2004.
                </p>

                <!-- Action Buttons -->
                <div class="hero-actions">
                    <a href="{{ route('services') }}" class="cta-button">
                        Explore Capabilities
                        <svg class="w-4 h-4 ml-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                    <a href="{{ route('contact') }}" class="cta-button-secondary">
                        Project Consultation
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Key Metrics Ribbon -->
    <section class="metrics-ribbon" aria-label="Company Statistics">
        <div class="metrics-container">
            <div class="metric-item">
                <div class="metric-number">20+</div>
                <div class="metric-label">Years of Industry Track Record</div>
            </div>
            <div class="metric-item">
                <div class="metric-number">500+</div>
                <div class="metric-label">Sites & Towers Commissioned</div>
            </div>
            <div class="metric-item">
                <div class="metric-number">1,500+ km</div>
                <div class="metric-label">Fiber Optic Trenching & Splicing</div>
            </div>
            <div class="metric-item">
                <div class="metric-number">CIDB G5</div>
                <div class="metric-label">Accredited Civil & Mechanical Works</div>
            </div>
        </div>
    </section>

    <!-- Engineering Capabilities (Replaces Pastel Bento Grid) -->
    <section class="page-shell company-intro">
        <div class="intro-header">
            <p class="eyebrow">Core Engineering Capabilities</p>
            <h2 class="section-title">Specialized Turnkey Infrastructure Delivery</h2>
            <p class="section-copy max-w-3xl">
                Tumpat Solutions operates certified in-house engineering, rigging, and trenching teams delivering end-to-end scope from initial soil investigation to live carrier commissioning.
            </p>
        </div>

        <div class="capabilities-grid">
            <!-- Capability 1: Tower Engineering (CME) -->
            <article class="capability-card">
                <div class="capability-header">
                    <div class="capability-icon">
                        <svg class="w-6 h-6 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <span class="capability-index">01 / CME Works</span>
                </div>
                <h3 class="capability-title">Telecommunication Tower Construction</h3>
                <p class="capability-copy">
                    Turnkey civil, mechanical, and electrical (CME) delivery for greenfield and rooftop sites. We handle standard penetration tests (SPT), pad-and-chimney or micro-piled foundations, 4-legged lattice structures, monopoles, and stealth mounts.
                </p>
                <div class="capability-specs">
                    <h4 class="specs-heading">Scope & Deliverables:</h4>
                    <ul class="specs-list">
                        <li>Greenfield 4-leg lattice & monopoles up to 75m</li>
                        <li>Structural loading audits & member retrofitting</li>
                        <li>Low-resistance copper earthing grids (&lt;5Ω)</li>
                        <li>Aviation warning obstacle lights (AWL) & lightning arrestors</li>
                    </ul>
                </div>
                <div class="capability-tags">
                    <span class="tag-pill">Greenfield Sites</span>
                    <span class="tag-pill">Rooftop CME</span>
                    <span class="tag-pill">Foundation Casting</span>
                    <span class="tag-pill">Earthing &lt;5Ω</span>
                </div>
            </article>

            <!-- Capability 2: Fiber Optics OSP/ISP -->
            <article class="capability-card">
                <div class="capability-header">
                    <div class="capability-icon">
                        <svg class="w-6 h-6 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <span class="capability-index">02 / Optical Network</span>
                </div>
                <h3 class="capability-title">Fiber Optics & OSP / ISP Rollout</h3>
                <p class="capability-copy">
                    Comprehensive optical network construction along highway reserves, urban corridors, and suburban estates. We operate heavy trenching equipment and cleanroom splicing labs for carrier backhaul.
                </p>
                <div class="capability-specs">
                    <h4 class="specs-heading">Scope & Deliverables:</h4>
                    <ul class="specs-list">
                        <li>Horizontal Directional Drilling (HDD) & micro-trenching</li>
                        <li>Sub-duct laying, cable blowing & pulling</li>
                        <li>Cleanroom core and ribbon fusion splicing</li>
                        <li>Bidirectional OTDR, PMD, & insertion loss testing</li>
                    </ul>
                </div>
                <div class="capability-tags">
                    <span class="tag-pill">HDD Drilling</span>
                    <span class="tag-pill">Core Splicing</span>
                    <span class="tag-pill">OTDR Certification</span>
                    <span class="tag-pill">FTTH Backhaul</span>
                </div>
            </article>

            <!-- Capability 3: RAN & Microwave -->
            <article class="capability-card">
                <div class="capability-header">
                    <div class="capability-icon">
                        <svg class="w-6 h-6 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                        </svg>
                    </div>
                    <span class="capability-index">03 / RF & Transmission</span>
                </div>
                <h3 class="capability-title">Radio Access Network (5G / Microwave)</h3>
                <p class="capability-copy">
                    Installation, integration, and commissioning of carrier-grade radio base stations (BTS), Massive MIMO active antenna units, and high-capacity microwave hops across Peninsular and Borneo Malaysia.
                </p>
                <div class="capability-specs">
                    <h4 class="specs-heading">Scope & Deliverables:</h4>
                    <ul class="specs-list">
                        <li>5G NR Massive MIMO active antenna integration</li>
                        <li>Line-of-sight (LOS) path profiling & hop alignment</li>
                        <li>Coaxial feeder routing, waterproofing & VSWR sweeps</li>
                        <li>End-to-end throughput testing & operator PAC documentation</li>
                    </ul>
                </div>
                <div class="capability-tags">
                    <span class="tag-pill">5G NR Integration</span>
                    <span class="tag-pill">Microwave Hops</span>
                    <span class="tag-pill">VSWR Sweep</span>
                    <span class="tag-pill">Carrier Acceptance</span>
                </div>
            </article>

            <!-- Capability 4: Managed Operations & Power -->
            <article class="capability-card">
                <div class="capability-header">
                    <div class="capability-icon">
                        <svg class="w-6 h-6 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <span class="capability-index">04 / Operations & Power</span>
                </div>
                <h3 class="capability-title">Managed Power & Site Maintenance</h3>
                <p class="capability-copy">
                    Preventive and corrective maintenance programs ensuring mission-critical network uptime. We deploy dedicated power rectification crews and emergency mobile generator units with strict SLA compliance.
                </p>
                <div class="capability-specs">
                    <h4 class="specs-heading">Scope & Deliverables:</h4>
                    <ul class="specs-list">
                        <li>AC/DC power distribution, rectifiers & battery banks</li>
                        <li>Solar-hybrid power systems for remote off-grid towers</li>
                        <li>Emergency power generator deployment & diesel top-ups</li>
                        <li>Routine site inspection, vegetation clearance & security</li>
                    </ul>
                </div>
                <div class="capability-tags">
                    <span class="tag-pill">Solar Hybrid</span>
                    <span class="tag-pill">Rectifier Banks</span>
                    <span class="tag-pill">Emergency Dispatch</span>
                    <span class="tag-pill">24/7 SLA</span>
                </div>
            </article>
        </div>
    </section>

    <!-- Industry Sectors (Replaces Emojis with Bespoke Technical SVGs) -->
    <section class="page-shell sectors-section" aria-label="Industry Sectors">
        <div class="intro-header">
            <p class="eyebrow">Sectors & Client Ecosystem</p>
            <h2 class="section-title">Trusted Across Critical Infrastructure</h2>
            <p class="section-copy max-w-2xl">
                We work as certified subcontractors and turnkey partners for Malaysia’s tier-1 telecommunication operators, tower companies, and national utilities.
            </p>
        </div>

        <div class="sectors-grid">
            <!-- Sector 1: Mobile Network Operators -->
            <div class="sector-card">
                <div class="sector-icon-box" aria-hidden="true">
                    <svg class="w-6 h-6 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="sector-title">Mobile Cellular Operators</h3>
                <p class="sector-desc">
                    RAN installation, 4G/5G antenna upgrades, microwave backhaul links, and rapid site swaps under strict maintenance windows.
                </p>
                <div class="sector-badge">Tier-1 Telco Support</div>
            </div>

            <!-- Sector 2: Tower Infrastructure Companies -->
            <div class="sector-card">
                <div class="sector-icon-box" aria-hidden="true">
                    <svg class="w-6 h-6 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 21h18M6 21V5a2 2 0 012-2h8a2 2 0 012 2v16M9 9h6M9 13h6M9 17h6"></path>
                    </svg>
                </div>
                <h3 class="sector-title">Tower Infrastructure Companies</h3>
                <p class="sector-desc">
                    Greenfield civil construction, foundation casting, structural load assessments, and multi-tenant colocation retrofitting.
                </p>
                <div class="sector-badge">TowerCo Partners</div>
            </div>

            <!-- Sector 3: Utilities & Power Grid -->
            <div class="sector-card">
                <div class="sector-icon-box" aria-hidden="true">
                    <svg class="w-6 h-6 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="sector-title">Utilities & Public Power</h3>
                <p class="sector-desc">
                    Optical ground wire (OPGW) stringing along high-voltage pylons, electrical substation telemetry, and SCADA optical networking.
                </p>
                <div class="sector-badge">Utility Telemetry</div>
            </div>

            <!-- Sector 4: Industrial & Remote Operations -->
            <div class="sector-card">
                <div class="sector-icon-box" aria-hidden="true">
                    <svg class="w-6 h-6 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h3 class="sector-title">Industrial & Remote Facilities</h3>
                <p class="sector-desc">
                    Offshore communications links, hazardous-area certified wiring, remote repeater towers, and off-grid solar-hybrid power setups.
                </p>
                <div class="sector-badge">Hazardous & Remote Works</div>
            </div>
        </div>
    </section>

    <!-- Track Record & Milestones -->
    <section id="timeline" class="timeline-section" aria-label="Company Milestones">
        <div class="intro-header">
            <p class="eyebrow">Corporate Milestones</p>
            <h2 class="section-title">Two Decades of Engineering Delivery</h2>
            <p class="section-copy max-w-2xl">
                From a specialized installation team in 2004 to a multi-disciplinary CIDB Grade G5 telecommunication engineering contractor.
            </p>
        </div>

        <div class="milestones-grid">
            <div class="milestone-card">
                <div class="milestone-period">2004 – 2009</div>
                <h3 class="milestone-title">Foundation & Telecom Certification</h3>
                <p class="milestone-desc">
                    Incorporated in Subang Jaya, Selangor. Awarded tier-1 cellular installation and transmission rigging contracts in Klang Valley.
                </p>
            </div>
            <div class="milestone-card">
                <div class="milestone-period">2010 – 2014</div>
                <h3 class="milestone-title">Regional Expansion & ISO 9001</h3>
                <p class="milestone-desc">
                    Accredited with ISO 9001 Quality Management System. Expanded technical crews across Peninsular Malaysia and established hubs in Sabah and Sarawak.
                </p>
            </div>
            <div class="milestone-card">
                <div class="milestone-period">2015 – 2019</div>
                <h3 class="milestone-title">4G LTE Rollout & CIDB Grade G5</h3>
                <p class="milestone-desc">
                    Registered under CIDB Malaysia Grade G5 (Civil, Mechanical & Electrical). Deployed over 1,500km of inter-city and metro fiber optic cabling.
                </p>
            </div>
            <div class="milestone-card">
                <div class="milestone-period">2020 – Present</div>
                <h3 class="milestone-title">5G Rollout & Digital Telemetry</h3>
                <p class="milestone-desc">
                    Active turnkey deployment partner in Malaysia’s 5G rollout. Integrated real-time GPS fleet telemetry and geo-verified workforce attendance systems.
                </p>
            </div>
        </div>
    </section>

    <!-- Call to Action Banner -->
    <section class="cta-banner-section" aria-label="Contact Call to Action">
        <div class="cta-banner-card">
            <div class="cta-banner-content">
                <p class="cta-badge">Certified Engineering Capacity</p>
                <h2 class="cta-banner-title">Ready to Mobilize for Your Network Expansion?</h2>
                <p class="cta-banner-copy">
                    Whether you require single-site structural upgrades, regional fiber trenching, or ongoing 24/7 site maintenance SLAs, our engineering directors are ready to discuss your scope.
                </p>
                <div class="cta-banner-buttons">
                    <a href="{{ route('contact') }}" class="cta-button">
                        Request Project Consultation
                    </a>
                    <a href="tel:+60356119916" class="cta-button-secondary">
                        Call +60 3-5611 9916
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection