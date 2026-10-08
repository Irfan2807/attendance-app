@extends('layouts.site')

@section('title', 'Our Services & Capabilities - Tumpat Solutions')

@section('content')
    <!-- Page Header Banner -->
    <div class="page-banner">
        <div class="page-banner-container">
            <nav class="breadcrumb-nav" aria-label="Breadcrumb">
                <ol class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-separator">/</li>
                    <li class="breadcrumb-current">Services</li>
                </ol>
            </nav>
            <h1 class="page-banner-title">Engineering Capabilities</h1>
            <p class="page-banner-subtitle">
                Turnkey telecommunication, optical network, and civil engineering solutions built for mission-critical reliability across Malaysia.
            </p>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="page-shell">

        <!-- Engineering Pillars Section -->
        <div class="services-detail-list">

            <!-- Pillar 1: Tower Infrastructure -->
            <section id="towers" class="service-detail-card">
                <div class="service-detail-grid">
                    <div class="service-detail-info">
                        <div class="service-meta">
                            <span class="service-pillar-number">01</span>
                            <span class="service-badge">Civil & Structural CME</span>
                        </div>
                        <h2 class="service-detail-title">Telecommunication Tower Infrastructure</h2>
                        <p class="service-detail-copy">
                            We deliver end-to-end tower engineering from greenfield acquisition to turnkey commissioning. Our in-house civil engineering teams ensure every structure adheres strictly to local authority (PBT), MCMC, and international structural load guidelines.
                        </p>
                        <ul class="service-bullets">
                            <li><strong>Structure Configurations:</strong> 4-legged angular/tubular towers, 3-legged masts, monopoles, lamp poles, and rooftop stealth towers.</li>
                            <li><strong>Geotechnical & Civil Foundations:</strong> Soil boring investigation (SPT), pad-and-chimney or micro-piled foundations.</li>
                            <li><strong>Electrical & Lightning Protection:</strong> Low-resistance copper earthing grids (&lt;5Ω), lightning air terminals, and aviation warning obstacle lights (AWL).</li>
                            <li><strong>Audits & Retrofitting:</strong> Structural deflection checks, member strengthening, and retrofitting for 5G antenna payload additions.</li>
                        </ul>
                    </div>
                    <div class="service-spec-panel">
                        <h4 class="spec-panel-title">Technical Parameters</h4>
                        <div class="spec-param-list">
                            <div class="spec-param-item">
                                <span class="param-label">Tower Heights</span>
                                <span class="param-value">Up to 75m AGL</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Foundation Types</span>
                                <span class="param-value">Pad & Chimney / Micro-piles</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Earthing Resistance</span>
                                <span class="param-value">&lt; 5.0 Ohms</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Compliance</span>
                                <span class="param-value">CIDB G5 / MCMC / PBT</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Pillar 2: Fiber Optics -->
            <section id="fiber" class="service-detail-card">
                <div class="service-detail-grid">
                    <div class="service-detail-info">
                        <div class="service-meta">
                            <span class="service-pillar-number">02</span>
                            <span class="service-badge">Optical Network</span>
                        </div>
                        <h2 class="service-detail-title">Fiber Optics & OSP / ISP Rollout</h2>
                        <p class="service-detail-copy">
                            High-speed backhaul and access networks require precision optical deployment. We operate specialized trenching and fusion splicing crews capable of rapid deployment along highway reserves, urban centers, and suburban corridors.
                        </p>
                        <ul class="service-bullets">
                            <li><strong>Civil Trenching:</strong> Horizontal Directional Drilling (HDD), micro-trenching, open-cut excavations, and sub-duct laying.</li>
                            <li><strong>Cable Installation:</strong> Blown fiber systems, high-strand armored optical cable pulling through ducts and aerial spans.</li>
                            <li><strong>Splicing & Termination:</strong> Cleanroom core fusion splicing, optical distribution frames (ODF), and patch panel terminations.</li>
                            <li><strong>Testing & Certification:</strong> Bidirectional Optical Time Domain Reflectometer (OTDR) verification, optical insertion loss, and PMD testing.</li>
                        </ul>
                    </div>
                    <div class="service-spec-panel">
                        <h4 class="spec-panel-title">Technical Parameters</h4>
                        <div class="spec-param-list">
                            <div class="spec-param-item">
                                <span class="param-label">Trenching Methods</span>
                                <span class="param-value">HDD / Micro-trench / Open Cut</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Fiber Capacities</span>
                                <span class="param-value">12-core to 288-core Single-Mode</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Verification</span>
                                <span class="param-value">Bidirectional OTDR Loss Certification</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Deployment Scope</span>
                                <span class="param-value">Peninsular & Borneo Malaysia</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Pillar 3: Wireless & Microwave -->
            <section id="wireless" class="service-detail-card">
                <div class="service-detail-grid">
                    <div class="service-detail-info">
                        <div class="service-meta">
                            <span class="service-pillar-number">03</span>
                            <span class="service-badge">Radio Frequency</span>
                        </div>
                        <h2 class="service-detail-title">Wireless & Microwave Transmission</h2>
                        <p class="service-detail-copy">
                            Our telecommunications teams are certified to install and integrate carrier-grade radio equipment for leading vendors (Ericsson, Huawei, Nokia, ZTE). We ensure minimal bit-error rates and maximum link availability.
                        </p>
                        <ul class="service-bullets">
                            <li><strong>Cellular Systems:</strong> 4G LTE and 5G Massive MIMO active antenna installations, Remote Radio Units (RRU), and Baseband units.</li>
                            <li><strong>Microwave Transmission:</strong> Line-of-sight (LOS) path profiling, parabolic dish mounting, precision alignment, and hop commissioning.</li>
                            <li><strong>Feeder & Waveguide:</strong> Coaxial feeder routing, grounding kits, waterproof sealing, and VSWR return-loss sweep analysis.</li>
                            <li><strong>Testing & Acceptance:</strong> Throughput validation, BER testing, and official carrier provisional acceptance (PAC).</li>
                        </ul>
                    </div>
                    <div class="service-spec-panel">
                        <h4 class="spec-panel-title">Technical Parameters</h4>
                        <div class="spec-param-list">
                            <div class="spec-param-item">
                                <span class="param-label">Frequency Bands</span>
                                <span class="param-value">700MHz to 28GHz (5G NR / MW)</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Testing Equipment</span>
                                <span class="param-value">Anritsu Site Master (VSWR/DTF)</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Link Availability</span>
                                <span class="param-value">99.999% SLA Carrier Standard</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Vendor Standards</span>
                                <span class="param-value">Huawei / Ericsson / Nokia / ZTE</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Pillar 4: Civil & Electrical CME -->
            <section id="civil" class="service-detail-card">
                <div class="service-detail-grid">
                    <div class="service-detail-info">
                        <div class="service-meta">
                            <span class="service-pillar-number">04</span>
                            <span class="service-badge">Compound Works</span>
                        </div>
                        <h2 class="service-detail-title">Civil, Mechanical & Electrical (CME)</h2>
                        <p class="service-detail-copy">
                            Behind every active telecom site is a secure, powered civil compound. Tumpat Solutions builds the supportive infrastructure that protects costly communications hardware from power disruptions and environmental elements.
                        </p>
                        <ul class="service-bullets">
                            <li><strong>Site Access:</strong> Access road earthworks, hillside retainment, culvert drainage, and anti-erosion slope protection.</li>
                            <li><strong>Compound Enclosures:</strong> Anti-climb fencing, razor wire toppings, heavy security gates, and perimeter sensor conduits.</li>
                            <li><strong>Power Systems:</strong> Tenaga Nasional Berhad (TNB) substation interconnects, step-down transformers, and distribution boards.</li>
                            <li><strong>Backup Generation:</strong> Permanent diesel generator sets (DG), automatic transfer switches (ATS), and sound-proof canopies.</li>
                        </ul>
                    </div>
                    <div class="service-spec-panel">
                        <h4 class="spec-panel-title">Technical Parameters</h4>
                        <div class="spec-param-list">
                            <div class="spec-param-item">
                                <span class="param-label">Grid Connection</span>
                                <span class="param-value">TNB Substation / Transformer</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Enclosures</span>
                                <span class="param-value">Galvanized Anti-Climb (BS1722)</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Backup Capacity</span>
                                <span class="param-value">15kVA – 100kVA Soundproof DG</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Safety Clearance</span>
                                <span class="param-value">Suruhanjaya Tenaga Certified</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Pillar 5: Maintenance & Operations -->
            <section id="maintenance" class="service-detail-card">
                <div class="service-detail-grid">
                    <div class="service-detail-info">
                        <div class="service-meta">
                            <span class="service-pillar-number">05</span>
                            <span class="service-badge">Managed Services</span>
                        </div>
                        <h2 class="service-detail-title">Managed Site Operations & 24/7 SLA</h2>
                        <p class="service-detail-copy">
                            Telecommunication uptime is non-negotiable. Our regional maintenance hubs across Peninsular Malaysia, Sabah, and Sarawak operate with rapid-dispatch vehicles to maintain target 99.99% network availability.
                        </p>
                        <ul class="service-bullets">
                            <li><strong>Preventive Maintenance:</strong> Quarterly tower structural bolt torquing, paint corrosion treatment, and earthing checks.</li>
                            <li><strong>Power Integrity:</strong> Generator load testing, fuel replenishment, battery conductance testing, and rectifier servicing.</li>
                            <li><strong>Emergency Restoration:</strong> 2-to-4 hour SLA emergency callout response for storm damage, fiber cuts, or power grid failure.</li>
                            <li><strong>Compound Hygiene:</strong> Bush clearing, weed abatement, drainage unblocking, and pest barrier inspections.</li>
                        </ul>
                    </div>
                    <div class="service-spec-panel">
                        <h4 class="spec-panel-title">Technical Parameters</h4>
                        <div class="spec-param-list">
                            <div class="spec-param-item">
                                <span class="param-label">Emergency Callout</span>
                                <span class="param-value">2 – 4 Hour Response Window</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Fleet Readiness</span>
                                <span class="param-value">4WD Service Vehicles Nationwide</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Structural Checks</span>
                                <span class="param-value">Torque Calibrated & Certified</span>
                            </div>
                            <div class="spec-param-item">
                                <span class="param-label">Availability Target</span>
                                <span class="param-value">99.99% Network Uptime SLA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>

        <!-- QHSE & Standards Grid (Replaced Emojis with Clean Technical SVGs) -->
        <div class="qhse-container content-box">
            <div class="qhse-header text-center">
                <p class="eyebrow">Safety & Quality Compliance</p>
                <h2 class="section-title">Committed to Zero Harm & Rigorous Engineering Standards</h2>
                <p class="section-copy max-w-2xl mx-auto">
                    Telecom and high-elevation tower works require uncompromising health, environmental, and structural safety discipline.
                </p>
            </div>
            <div class="qhse-grid">
                <!-- CIDB Grade G5 -->
                <div class="qhse-card">
                    <div class="qhse-icon-box" aria-hidden="true">
                        <svg class="w-6 h-6 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="qhse-card-title">CIDB Certified Grade G5</h3>
                    <p class="qhse-card-desc">Recognized by the Construction Industry Development Board Malaysia for civil engineering (CE21) and mechanical/electrical works without tender value ceilings.</p>
                </div>

                <!-- ISO 9001:2015 -->
                <div class="qhse-card">
                    <div class="qhse-icon-box" aria-hidden="true">
                        <svg class="w-6 h-6 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <h3 class="qhse-card-title">ISO 9001:2015 Accredited</h3>
                    <p class="qhse-card-desc">Standardized quality management systems applied to every engineering deliverable, subcontractor audit, material testing report, and site handover.</p>
                </div>

                <!-- NIOSH & DOSH -->
                <div class="qhse-card">
                    <div class="qhse-icon-box" aria-hidden="true">
                        <svg class="w-6 h-6 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="qhse-card-title">NIOSH & DOSH Compliant</h3>
                    <p class="qhse-card-desc">All climbers, riggers, and field engineers hold valid CIDB Green Cards, certified Working at Heights (WAH) credentials, and TM Safety Passports (NTMSP).</p>
                </div>
            </div>
        </div>

        <!-- Conversion Prompt -->
        <div class="services-cta-banner">
            <h3 class="services-cta-title">Looking for an Accredited Engineering Partner?</h3>
            <p class="services-cta-copy">
                Contact our engineering directors today to discuss scopes of work, bill of quantities (BOQ), or site feasibility surveys.
            </p>
            <div class="services-cta-action">
                <a href="{{ route('contact') }}" class="cta-button">
                    Speak With Our Technical Team
                </a>
            </div>
        </div>

    </div>
@endsection