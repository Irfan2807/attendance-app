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
                Turnkey telecommunication and civil engineering solutions designed for mission-critical reliability across Malaysia.
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
                        <span class="service-pillar-number">01</span>
                        <div class="service-badge service-badge-orange">Civil & Structural</div>
                        <h2 class="service-detail-title">Telecommunication Tower Infrastructure</h2>
                        <p class="service-detail-copy">
                            We deliver end-to-end tower engineering from greenfield acquisition to turnkey commissioning. Our in-house civil engineering teams ensure every structure adheres strictly to local authority (PBT), MCMC, and international structural load guidelines.
                        </p>
                        <ul class="service-bullets">
                            <li><strong>Structure Types:</strong> 4-legged angular/tubular towers, 3-legged masts, monopoles, lamp poles, and rooftop stealth towers.</li>
                            <li><strong>Geotechnical & Civil:</strong> Soil bore testing, standard penetration tests (SPT), pad-and-chimney or micro-piled foundations.</li>
                            <li><strong>Electrical & Protection:</strong> Low-resistance copper earthing grids (< 5Ω), lightning air terminals, and aviation warning obstacle lights (AWL).</li>
                            <li><strong>Audits & Upgrades:</strong> Structural deflection checks, member strengthening, and retrofitting for 5G antenna payload additions.</li>
                        </ul>
                    </div>
                    <div class="service-detail-visual bg-gradient-to-br from-amber-50 to-orange-100 border border-orange-200">
                        <div class="visual-icon-box text-orange-600">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h4 class="visual-caption">Turnkey Tower Commissioning</h4>
                        <p class="visual-sub">Delivering nationwide structures up to 75m heights.</p>
                    </div>
                </div>
            </section>

            <!-- Pillar 2: Fiber Optics -->
            <section id="fiber" class="service-detail-card">
                <div class="service-detail-grid">
                    <div class="service-detail-info">
                        <span class="service-pillar-number">02</span>
                        <div class="service-badge service-badge-green">Optical Network</div>
                        <h2 class="service-detail-title">Fiber Optics & OSP / ISP Rollout</h2>
                        <p class="service-detail-copy">
                            High-speed backhaul and access networks require precision optical deployment. We operate specialized trenching and fusion splicing crews capable of rapid deployment along highway reserves, urban centers, and suburban corridors.
                        </p>
                        <ul class="service-bullets">
                            <li><strong>Civil Trenching:</strong> Horizontal Directional Drilling (HDD), micro-trenching, open-cut excavations, and sub-duct laying.</li>
                            <li><strong>Cable Installation:</strong> Blown fiber systems, high-strand armored optical cable pulling through ducts and aerial spans.</li>
                            <li><strong>Splicing & Termination:</strong> Cleanroom ribbon/core fusion splicing, optical distribution frames (ODF), and patch panel terminations.</li>
                            <li><strong>Testing & Certification:</strong> Bidirectional Optical Time Domain Reflectometer (OTDR) verification, optical insertion loss, and PMD testing.</li>
                        </ul>
                    </div>
                    <div class="service-detail-visual bg-gradient-to-br from-emerald-50 to-teal-100 border border-emerald-200">
                        <div class="visual-icon-box text-emerald-600">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h4 class="visual-caption">High-Density Fiber Deployment</h4>
                        <p class="visual-sub">Long-haul backhaul and FTTH network expansion.</p>
                    </div>
                </div>
            </section>

            <!-- Pillar 3: Wireless & Microwave -->
            <section id="wireless" class="service-detail-card">
                <div class="service-detail-grid">
                    <div class="service-detail-info">
                        <span class="service-pillar-number">03</span>
                        <div class="service-badge service-badge-blue">Radio Frequency</div>
                        <h2 class="service-detail-title">Wireless & Microwave Transmission</h2>
                        <p class="service-detail-copy">
                            Our telecommunications teams are certified to install and integrate carrier-grade radio equipment for leading vendors (Ericsson, Huawei, Nokia, ZTE). We ensure minimal bit-error rates and maximum link availability.
                        </p>
                        <ul class="service-bullets">
                            <li><strong>Cellular Systems:</strong> 4G LTE and 5G Massive MIMO active antenna installations, Remote Radio Units (RRU), and Baseband units.</li>
                            <li><strong>Microwave Transmission:</strong> Line-of-sight (LOS) path profiling, parabolic dish mounting, precision alignment, and hop commissioning.</li>
                            <li><strong>Feeder & Waveguide:</strong> Coaxial feeder routing, grounding kits, waterproof sealing, and VSWR return-loss sweep analysis.</li>
                            <li><strong>Testing & Acceptance:</strong> E1/Ethernet throughput validation, BER testing, and official carrier provisional acceptance (PAC).</li>
                        </ul>
                    </div>
                    <div class="service-detail-visual bg-gradient-to-br from-blue-50 to-indigo-100 border border-blue-200">
                        <div class="visual-icon-box text-blue-600">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                            </svg>
                        </div>
                        <h4 class="visual-caption">Carrier-Grade Radio Networks</h4>
                        <p class="visual-sub">Precision microwave hops and 5G NR integration.</p>
                    </div>
                </div>
            </section>

            <!-- Pillar 4: Civil & Electrical CME -->
            <section id="civil" class="service-detail-card">
                <div class="service-detail-grid">
                    <div class="service-detail-info">
                        <span class="service-pillar-number">04</span>
                        <div class="service-badge service-badge-indigo">Civil Works</div>
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
                    <div class="service-detail-visual bg-gradient-to-br from-purple-50 to-indigo-100 border border-purple-200">
                        <div class="visual-icon-box text-purple-600">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                        </div>
                        <h4 class="visual-caption">Robust CME Infrastructure</h4>
                        <p class="visual-sub">Power resilience and secure compound construction.</p>
                    </div>
                </div>
            </section>

            <!-- Pillar 5: Maintenance & Operations -->
            <section id="maintenance" class="service-detail-card">
                <div class="service-detail-grid">
                    <div class="service-detail-info">
                        <span class="service-pillar-number">05</span>
                        <div class="service-badge service-badge-amber">Operations</div>
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
                    <div class="service-detail-visual bg-gradient-to-br from-amber-50 to-yellow-100 border border-amber-200">
                        <div class="visual-icon-box text-amber-600">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h4 class="visual-caption">Rapid Emergency Dispatch</h4>
                        <p class="visual-sub">SLA-guaranteed 24/7 restoration and maintenance.</p>
                    </div>
                </div>
            </section>

        </div>

        <!-- QHSE & Standards Grid -->
        <div class="qhse-container content-box">
            <div class="qhse-header text-center">
                <p class="eyebrow">Safety & Quality Standards</p>
                <h2 class="section-title">Committed to Zero Harm & Rigorous Quality</h2>
                <p class="section-copy max-w-2xl mx-auto">
                    Telecom and high-elevation tower works require uncompromising health and safety discipline.
                </p>
            </div>
            <div class="qhse-grid">
                <div class="qhse-card">
                    <div class="qhse-icon">🛡️</div>
                    <h4>CIDB Certified Grade G5</h4>
                    <p>Recognized by the Construction Industry Development Board Malaysia for general civil, mechanical, and electrical engineering.</p>
                </div>
                <div class="qhse-card">
                    <div class="qhse-icon">📋</div>
                    <h4>ISO 9001:2015 Accredited</h4>
                    <p>Standardized quality management systems applied to every engineering deliverable, vendor procurement, and project handover.</p>
                </div>
                <div class="qhse-card">
                    <div class="qhse-icon">👷</div>
                    <h4>NIOSH & DOSH Compliant</h4>
                    <p>All climbers, riggers, and field engineers undergo accredited Working at Heights (WAH) and Confined Space training with certified PPE.</p>
                </div>
            </div>
        </div>

        <!-- Conversion Prompt -->
        <div class="services-cta-banner">
            <h3 class="services-cta-title">Looking for an Engineering Partner for Your Rollout?</h3>
            <p class="services-cta-copy">
                Contact our engineering directors today to discuss scopes, bills of quantities (BOQ), or site surveys.
            </p>
            <div class="services-cta-action">
                <a href="{{ route('contact') }}" class="cta-button">
                    Speak With Our Technical Team
                </a>
            </div>
        </div>

    </div>
@endsection