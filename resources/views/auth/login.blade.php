<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>NexPOS Pro - Enterprise Retail & Point of Sale System</title>
    <meta name="title" content="NexPOS Pro - Enterprise Retail & Point of Sale System" />
    <meta name="description" content="Next generation Point of Sale, inventory management, and business intelligence suite." />

    <!-- Completely Transparent / Blank Favicon -->
    <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=">
    <link rel="shortcut icon" type="image/x-icon" href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-base: #070a13;
            --bg-card: rgba(15, 23, 42, 0.72);
            --bg-card-border: rgba(255, 255, 255, 0.08);
            --primary: #3b82f6;
            --primary-glow: rgba(59, 130, 246, 0.35);
            --secondary: #6366f1;
            --accent: #06b6d4;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --input-bg: rgba(11, 17, 33, 0.75);
            --input-border: rgba(255, 255, 255, 0.12);
            --input-focus: #3b82f6;
            --danger: #ef4444;
            --success: #10b981;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-base);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 20px;
        }

        /* Ambient Lighting / Mesh Glow */
        .ambient-glow {
            position: absolute;
            width: 550px;
            height: 550px;
            border-radius: 50%;
            filter: blur(140px);
            opacity: 0.28;
            pointer-events: none;
            z-index: 0;
        }

        .glow-1 {
            top: -100px;
            left: -100px;
            background: radial-gradient(circle, #3b82f6 0%, transparent 70%);
        }

        .glow-2 {
            bottom: -100px;
            right: -100px;
            background: radial-gradient(circle, #6366f1 0%, transparent 70%);
        }

        .glow-3 {
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #06b6d4 0%, transparent 70%);
            opacity: 0.15;
        }

        /* Main Wrapper */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 1100px;
            background: var(--bg-card);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--bg-card-border);
            border-radius: 24px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7), 0 0 40px -10px var(--primary-glow);
            display: grid;
            grid-template-columns: 1.15fr 1fr;
            overflow: hidden;
        }

        /* Left Showcase Panel */
        .showcase-panel {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.6) 0%, rgba(15, 23, 42, 0.9) 100%);
            padding: 55px 45px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            border-right: 1px solid var(--bg-card-border);
        }

        .brand-header {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px -4px var(--primary-glow);
        }

        .brand-logo-icon svg {
            width: 26px;
            height: 26px;
            fill: #ffffff;
        }

        .brand-name {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-badge {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 6px;
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
            margin-left: 6px;
            vertical-align: middle;
        }

        .showcase-hero {
            margin: 40px 0;
        }

        .tag-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 13px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .tag-pill-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 10px #10b981;
        }

        .showcase-hero h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 34px;
            font-weight: 700;
            line-height: 1.25;
            color: #ffffff;
            margin-bottom: 16px;
        }

        .showcase-hero h1 span {
            background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .showcase-hero p {
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        /* Feature Cards */
        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .feature-card {
            background: rgba(15, 23, 42, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 14px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            background: rgba(30, 41, 59, 0.55);
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateX(4px);
        }

        .feature-icon-box {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(59, 130, 246, 0.12);
            color: #60a5fa;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .feature-icon-box svg {
            width: 20px;
            height: 20px;
        }

        .feature-text h4 {
            font-size: 14px;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 2px;
        }

        .feature-text p {
            font-size: 12px;
            color: #64748b;
        }

        .showcase-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: #64748b;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        /* Right Form Panel */
        .form-panel {
            padding: 55px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(11, 16, 30, 0.5);
        }

        .form-header {
            margin-bottom: 30px;
        }

        .form-header h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Error Banner */
        .error-alert {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 22px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            color: #fca5a5;
            font-size: 13px;
            line-height: 1.5;
        }

        .error-alert svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            fill: #ef4444;
            margin-top: 2px;
        }

        /* Input Group */
        .input-group {
            margin-bottom: 22px;
            position: relative;
        }

        .input-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            color: #64748b;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
        }

        .input-icon svg {
            width: 20px;
            height: 20px;
        }

        .form-control-custom {
            width: 100%;
            height: 52px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            padding: 0 45px 0 48px;
            font-size: 15px;
            color: #ffffff;
            font-family: inherit;
            transition: all 0.25s ease;
            outline: none;
        }

        .form-control-custom::placeholder {
            color: #475569;
        }

        .form-control-custom:focus {
            border-color: var(--input-focus);
            background: rgba(15, 23, 42, 0.95);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.18);
        }

        .input-box:focus-within .input-icon {
            color: var(--primary);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: #e2e8f0;
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
        }

        .field-error {
            color: var(--danger);
            font-size: 12px;
            margin-top: 6px;
            display: block;
        }

        /* Checkbox & Options */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 26px;
            font-size: 13px;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            color: #94a3b8;
            user-select: none;
        }

        .checkbox-container input {
            display: none;
        }

        .custom-checkmark {
            width: 18px;
            height: 18px;
            border-radius: 5px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .checkbox-container input:checked + .custom-checkmark {
            background: var(--primary);
            border-color: var(--primary);
        }

        .custom-checkmark svg {
            width: 12px;
            height: 12px;
            fill: #ffffff;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .checkbox-container input:checked + .custom-checkmark svg {
            opacity: 1;
        }

        .quick-hint {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.3px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 25px -5px var(--primary-glow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.5);
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit svg {
            width: 18px;
            height: 18px;
            transition: transform 0.2s;
        }

        .btn-submit:hover svg {
            transform: translateX(4px);
        }

        /* Terminal Security Badge */
        .security-badge {
            margin-top: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #64748b;
            font-size: 12px;
        }

        .security-badge svg {
            width: 14px;
            height: 14px;
            fill: #10b981;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                max-width: 500px;
            }

            .showcase-panel {
                display: none;
            }

            .form-panel {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Glow Orbs -->
    <div class="ambient-glow glow-1"></div>
    <div class="ambient-glow glow-2"></div>
    <div class="ambient-glow glow-3"></div>

    <div class="login-wrapper">

        <!-- Left Showcase Panel -->
        <div class="showcase-panel">
            <div>
                <!-- Brand Header -->
                <div class="brand-header">
                    <div class="brand-logo-icon">
                        <!-- POS Cart / Register Icon -->
                        <svg viewBox="0 0 24 24">
                            <path d="M4 4h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H10.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1v2h3zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-10 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="brand-name">NexPOS</span>
                        <span class="brand-badge">PRO v2.5</span>
                    </div>
                </div>

                <!-- Hero Section -->
                <div class="showcase-hero">
                    <div class="tag-pill">
                        <span class="tag-pill-dot"></span>
                        Terminal System Online
                    </div>
                    <h1>Smart <span>Retail & POS</span> Management Suite</h1>
                    <p>Streamlined point-of-sale checkout, multi-warehouse stock management, and unified financial analytics.</p>

                    <!-- Feature Highlights -->
                    <div class="feature-list">
                        <div class="feature-card">
                            <div class="feature-icon-box">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Instant Barcode & Serial Checkout</h4>
                                <p>Sub-second barcode scan with instant invoice generation.</p>
                            </div>
                        </div>

                        <div class="feature-card">
                            <div class="feature-icon-box">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Intelligent Inventory Tracking</h4>
                                <p>Live multi-location stock levels with low-balance alerts.</p>
                            </div>
                        </div>

                        <div class="feature-card">
                            <div class="feature-icon-box">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Unified Accounting & Vouchers</h4>
                                <p>Automated profit & loss, party ledgers, and cash flow.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Badge -->
            <div class="showcase-footer">
                <span>Enterprise POS Terminal</span>
                <span>Protected by 256-bit SSL</span>
            </div>
        </div>

        <!-- Right Login Form Panel -->
        <div class="form-panel">
            <div class="form-header">
                <h2>Welcome Back</h2>
                <p>Sign in with your authorized credentials to access the terminal.</p>
            </div>

            <!-- Global Error Banner -->
            @if($errors->any())
            <div class="error-alert">
                <svg viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Login Form (Logic preserved 100%) -->
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <!-- Email Input -->
                <div class="input-group">
                    <label class="input-label" for="email">User Email</label>
                    <div class="input-box">
                        <span class="input-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </span>
                        <input id="email" 
                               name="email" 
                               type="email" 
                               class="form-control-custom" 
                               placeholder="admin@gmail.com" 
                               value="{{ old('email', 'admin@gmail.com') }}" 
                               required 
                               autofocus 
                               autocomplete="email">
                    </div>
                    @error('email')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="input-group">
                    <label class="input-label" for="password">Password</label>
                    <div class="input-box">
                        <span class="input-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input id="password" 
                               name="password" 
                               type="password" 
                               class="form-control-custom" 
                               placeholder="••••••••" 
                               value="{{ old('password', '12345678') }}" 
                               required 
                               autocomplete="current-password">
                        <button type="button" class="toggle-password" id="togglePasswordBtn" title="Toggle password visibility">
                            <svg id="eyeIcon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Form Options -->
                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember" id="remember" checked>
                        <span class="custom-checkmark">
                            <svg viewBox="0 0 12 10">
                                <polyline points="1.5 6 4.5 9 10.5 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            </svg>
                        </span>
                        Remember device
                    </label>

                    <span class="quick-hint">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                        </svg>
                        Admin Verified
                    </span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit" id="submitBtn">
                    <span>Sign In to Terminal</span>
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </form>

            <!-- Bottom Security Notice -->
            <div class="security-badge">
                <svg viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>End-to-End Encrypted Terminal Session</span>
            </div>
        </div>

    </div>

    <script>
        // Password Visibility Toggle
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                
                if (isPassword) {
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                    `;
                } else {
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    `;
                }
            });
        }

        // Smooth button feedback on submit
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', function () {
                submitBtn.innerHTML = `
                    <svg class="spinner" style="animation: spin 1s linear infinite; width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Authenticating...</span>
                `;
                submitBtn.style.opacity = '0.85';
                submitBtn.style.pointerEvents = 'none';
            });
        }
    </script>
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</body>
</html>