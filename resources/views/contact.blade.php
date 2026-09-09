@extends('layouts.site')

@section('title', 'Contact Us & Engineering Inquiries - Tumpat Solutions')

@section('content')
    <!-- Page Header Banner -->
    <div class="page-banner">
        <div class="page-banner-container">
            <nav class="breadcrumb-nav" aria-label="Breadcrumb">
                <ol class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-separator">/</li>
                    <li class="breadcrumb-current">Contact Us</li>
                </ol>
            </nav>
            <h1 class="page-banner-title">Contact Our Engineering Team</h1>
            <p class="page-banner-subtitle">
                Whether you require project tenders, site feasibility surveys, or emergency network restoration, our engineering directors are ready to assist.
            </p>
        </div>
    </div>

    <div class="page-shell">
        <!-- Success Alert -->
        @if(session('success'))
            <div class="contact-alert-success" role="alert">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="font-bold text-emerald-900">Message Transmitted Successfully</p>
                        <p class="text-sm text-emerald-800 mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Split: Direct Info & Contact Form -->
        <div class="contact-main-grid">
            
            <!-- Left Column: Corporate Channels -->
            <div class="contact-channels">
                <div class="channels-header">
                    <p class="eyebrow">Direct Contact</p>
                    <h2 class="section-title">Headquarters & Operational Bases</h2>
                    <p class="section-copy">
                        Reach out directly to our central management or technical project office.
                    </p>
                </div>

                <div class="contact-card-list">
                    <!-- Card 1: Phone -->
                    <div class="contact-info-card">
                        <div class="contact-info-icon bg-orange-50 text-orange-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h4>Corporate Telephone</h4>
                            <p class="contact-info-highlight">+60 3-5611 9916</p>
                            <p class="text-xs text-gray-500 mt-1">Direct office reception & technical switchboard</p>
                        </div>
                    </div>

                    <!-- Card 2: Email -->
                    <div class="contact-info-card">
                        <div class="contact-info-icon bg-emerald-50 text-emerald-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h4>Engineering & Tenders</h4>
                            <p class="contact-info-highlight">enquiry@tumpatsolutions.com</p>
                            <p class="text-xs text-gray-500 mt-1">Official RFP submissions & quotation requests</p>
                        </div>
                    </div>

                    <!-- Card 3: Address -->
                    <div class="contact-info-card">
                        <div class="contact-info-icon bg-blue-50 text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h4>Corporate Office</h4>
                            <p class="text-sm font-semibold text-gray-900 leading-snug">
                                No. 12, Jalan TP 5, Taman Perindustrian UEP,<br>
                                47600 Subang Jaya, Selangor, Malaysia
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Hubs also in Kota Kinabalu & Kuching</p>
                        </div>
                    </div>

                    <!-- Card 4: Operating Hours -->
                    <div class="contact-info-card">
                        <div class="contact-info-icon bg-purple-50 text-purple-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h4>Operating Hours</h4>
                            <p class="text-sm font-semibold text-gray-900">Monday - Friday: 8:30 AM – 5:30 PM</p>
                            <p class="text-xs text-gray-500 mt-1">24/7 NOC & Rapid Response for contracted clients</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Interactive Inquiry Form -->
            <div class="contact-form-container content-box">
                <div class="form-header">
                    <h3 class="form-title">Send Project Inquiry</h3>
                    <p class="form-subtitle">Complete the details below and our project engineers will respond within 24 hours.</p>
                </div>

                <form method="POST" action="{{ route('contact.submit') }}" class="contact-form">
                    @csrf

                    <div class="form-row">
                        <!-- Full Name -->
                        <div class="form-group">
                            <label for="name" class="form-label">Your Full Name <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}" 
                                required 
                                placeholder="e.g. Ir. Ahmad Razali"
                                class="form-input @error('name') form-input-error @enderror"
                            >
                            @error('name')
                                <span class="form-error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Organization / Company -->
                        <div class="form-group">
                            <label for="company" class="form-label">Company / Organization</label>
                            <input 
                                type="text" 
                                id="company" 
                                name="company" 
                                value="{{ old('company') }}" 
                                placeholder="e.g. Telecommunication Operator"
                                class="form-input"
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <!-- Email -->
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address <span class="text-red-500">*</span></label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                placeholder="name@company.com"
                                class="form-input @error('email') form-input-error @enderror"
                            >
                            @error('email')
                                <span class="form-error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone / Mobile Number</label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                value="{{ old('phone') }}" 
                                placeholder="012-3456789"
                                class="form-input"
                            >
                        </div>
                    </div>

                    <!-- Service of Interest -->
                    <div class="form-group">
                        <label for="service" class="form-label">Primary Capability Required</label>
                        <select id="service" name="service" class="form-select">
                            <option value="">-- Select Required Service --</option>
                            <option value="Tower Construction">Telecommunication Tower Construction & CME</option>
                            <option value="Fiber Optics">Fiber Optics (OSP / ISP / Splicing / Micro-trenching)</option>
                            <option value="Wireless & Microwave">Wireless, 5G & Microwave Transmission Rollout</option>
                            <option value="Civil Infrastructure">Civil Works, Roads, Drainage & Compound Fencing</option>
                            <option value="Site Maintenance">Managed Site Operations & 24/7 SLA Maintenance</option>
                            <option value="General Tender">General Tender / BOQ Scope Discussion</option>
                        </select>
                    </div>

                    <!-- Project Message -->
                    <div class="form-group">
                        <label for="message" class="form-label">Project Details & Scope <span class="text-red-500">*</span></label>
                        <textarea 
                            id="message" 
                            name="message" 
                            rows="5" 
                            required 
                            placeholder="Please provide site location, estimated quantities, schedule expectations, or specific tender scope..."
                            class="form-textarea @error('message') form-input-error @enderror"
                        >{{ old('message') }}</textarea>
                        @error('message')
                            <span class="form-error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="cta-button w-full text-center justify-center">
                        Submit Inquiry
                        <svg class="w-4 h-4 ml-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </form>
            </div>

        </div>

        <!-- Google Map Section -->
        <div class="contact-map-wrap">
            <div class="map-header">
                <p class="eyebrow">Location</p>
                <h3 class="contact-map-title">Headquarters Location Map</h3>
                <p class="section-copy">Subang Jaya Industrial Hub with easy access to major expressways (KESAS, ELITE, Federal Highway).</p>
            </div>
            <div class="contact-map-frame">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15936.364814273595!2d101.54301793955081!3d3.0703017000000044!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc4da826c62fb5%3A0x4fa84be3cc4e7794!2sTumpat%20Solutions%20Sendirian%20Berhad!5e0!3m2!1sen!2smy!4v1686105083511!5m2!1sen!2smy"
                    width="100%"
                    height="420"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Tumpat Solutions Location Map"></iframe>
            </div>
        </div>

        <!-- Enterprise FAQ Accordion -->
        <div class="faq-container content-box">
            <div class="faq-header text-center">
                <p class="eyebrow">Frequently Asked Questions</p>
                <h2 class="section-title">Common Client & Partner Queries</h2>
                <p class="section-copy max-w-xl mx-auto">Everything you need to know about our certifications, regional reach, and mobilization.</p>
            </div>

            <div class="faq-accordion">
                <details class="faq-item">
                    <summary class="faq-summary">
                        <span>What geographic coverage does Tumpat Solutions provide?</span>
                        <svg class="faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </summary>
                    <div class="faq-content">
                        <p>We deliver nationwide engineering support across all 14 states in Peninsular Malaysia as well as East Malaysia (Sabah and Sarawak). We maintain permanent logistical hubs and certified local engineering crews in both Kota Kinabalu and Kuching to ensure rapid mobilization.</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary class="faq-summary">
                        <span>Are your site riggers and climbers certified for Working at Heights (WAH)?</span>
                        <svg class="faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </summary>
                    <div class="faq-content">
                        <p>Yes. 100% of our tower climbers, riggers, and field technicians hold valid Working at Heights (WAH) certifications from accredited training bodies (NIOSH / DOSH), active CIDB Green Cards, and vendor-specific safety accreditations. Safety and zero-harm on all elevated structures is our primary operational requirement.</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary class="faq-summary">
                        <span>Can Tumpat Solutions handle full turnkey Greenfield tower acquisition and construction?</span>
                        <svg class="faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </summary>
                    <div class="faq-content">
                        <p>Yes. We provide complete turnkey execution including soil investigation (borehole tests), local council (PBT) planning submissions, civil foundation micro-piling, 4-leg lattice tower erection, lightning protection grids, TNB electrical substation hookups, and final carrier antenna rigging.</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary class="faq-summary">
                        <span>What is your typical emergency callout response time for SLA maintenance?</span>
                        <svg class="faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </summary>
                    <div class="faq-content">
                        <p>For contracted carrier and utility clients, our 24/7 Network Operations Center (NOC) operates under strict service level agreements (SLAs), offering 2-to-4 hour on-site dispatch for major outages, generator power failure, or physical fiber cuts.</p>
                    </div>
                </details>
            </div>
        </div>

    </div>
@endsection