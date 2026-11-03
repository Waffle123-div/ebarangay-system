<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="E-Barangay: Web-Based Document Request and Tracking System for Barangay Cogon East, Carmen, Cebu. Request barangay documents online, track your requests in real-time, and access community services.">
  <title>E-Barangay — Document Request & Tracking System | Barangay Cogon East, Carmen, Cebu</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Icons (Lucide via CDN) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">

  <!-- Styles (Updated for Laravel) -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">

  <style>
    /* ========================================================
       LANDING PAGE MODERN UI ENHANCEMENTS
       ======================================================== */

    /* ── 1. Header Contrast & Floating Navbar ── */
    .header:not(.scrolled) {
      background: linear-gradient(180deg, rgba(8, 17, 36, 0.85) 0%, rgba(8, 17, 36, 0.4) 60%, transparent 100%) !important;
    }
    .header:not(.scrolled) .logo-text {
      color: #FFFFFF !important;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
    }
    .header:not(.scrolled) .logo-text span {
      color: #93C5FD !important;
      text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
    }
    @media (min-width: 769px) {
      .header:not(.scrolled) .nav-links a:not(.active) {
        color: rgba(255, 255, 255, 0.9) !important;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
      }
      .header:not(.scrolled) .nav-links a:not(.active):hover {
        color: #FFFFFF !important;
        background: rgba(255, 255, 255, 0.16) !important;
      }
    }
    .header:not(.scrolled) .dark-mode-toggle {
      background: rgba(255, 255, 255, 0.12) !important;
      border-color: rgba(255, 255, 255, 0.25) !important;
      color: #FFFFFF !important;
    }
    .header:not(.scrolled) .btn-outline-nav {
      color: #FFFFFF !important;
      border-color: rgba(255, 255, 255, 0.35) !important;
      background: rgba(255, 255, 255, 0.1) !important;
    }
    .header:not(.scrolled) .btn-outline-nav:hover {
      background: rgba(255, 255, 255, 0.22) !important;
      border-color: #FFFFFF !important;
      color: #FFFFFF !important;
    }

    /* ── 2. Hero Background & Interactive Cinematic Animations ── */
    .hero {
      position: relative;
      min-height: 100vh;
      display: flex;
      align-items: center;
      overflow: hidden;
      padding-top: 110px;
      padding-bottom: 70px;
      --hero-mouse-x: 50%;
      --hero-mouse-y: 45%;
      --hero-tilt-x: 0;
      --hero-tilt-y: 0;
    }
    .hero-bg {
      position: absolute;
      inset: 0;
      z-index: 0;
      overflow: hidden;
      perspective: 1200px;
    }
    .hero-bg img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transform: scale(1.04) translate(calc(var(--hero-tilt-x, 0) * 8px), calc(var(--hero-tilt-y, 0) * 8px));
      filter: contrast(1.1) brightness(0.85);
      transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), filter 0.5s ease;
      will-change: transform;
    }
    .hero:hover .hero-bg img {
      transform: scale(1.07) translate(calc(var(--hero-tilt-x, 0) * 12px), calc(var(--hero-tilt-y, 0) * 12px));
    }
    .hero-bg-vignette {
      position: absolute;
      inset: 0;
      background: radial-gradient(
        ellipse 90% 80% at 50% 40%,
        rgba(15, 34, 64, 0.72) 0%,
        rgba(10, 25, 52, 0.86) 50%,
        rgba(6, 14, 30, 0.96) 100%
      );
      mix-blend-mode: multiply;
      pointer-events: none;
    }

    /* ── Interactive Cursor-Follow Spotlight ── */
    .hero-interactive-spotlight {
      position: absolute;
      inset: -100px;
      pointer-events: none;
      background: radial-gradient(
        700px circle at var(--hero-mouse-x, 50%) var(--hero-mouse-y, 45%),
        rgba(56, 189, 248, 0.28) 0%,
        rgba(37, 99, 235, 0.16) 35%,
        rgba(30, 64, 175, 0.05) 55%,
        transparent 75%
      );
      transition: opacity 0.4s ease;
      z-index: 1;
      mix-blend-mode: screen;
      will-change: background;
    }

    /* ── Rotating Holographic Accent Rings ── */
    .hero-bg-ring {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      z-index: 1;
      will-change: transform;
    }
    .hero-bg-ring-1 {
      width: 720px;
      height: 720px;
      border: 1.5px dashed rgba(56, 189, 248, 0.16);
      top: 50%;
      right: -140px;
      transform: translateY(-50%) rotate(0deg);
      animation: ringRotate 90s linear infinite;
      box-shadow: 0 0 60px rgba(56, 189, 248, 0.06), inset 0 0 60px rgba(37, 99, 235, 0.06);
    }
    .hero-bg-ring-2 {
      width: 1050px;
      height: 1050px;
      border: 1px solid rgba(255, 255, 255, 0.05);
      top: 50%;
      right: -300px;
      transform: translateY(-50%) rotate(0deg);
      animation: ringRotateRev 130s linear infinite;
    }
    @keyframes ringRotate {
      from { transform: translateY(-50%) rotate(0deg); }
      to   { transform: translateY(-50%) rotate(360deg); }
    }
    @keyframes ringRotateRev {
      from { transform: translateY(-50%) rotate(0deg); }
      to   { transform: translateY(-50%) rotate(-360deg); }
    }

    /* ── Morphing Ambient Aurora Orbs with Tilt Parallax ── */
    .hero-ambient-orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(85px);
      pointer-events: none;
      opacity: 0.6;
      will-change: transform, border-radius;
      transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .orb-gold {
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(245, 158, 11, 0.44) 0%, rgba(217, 119, 6, 0.12) 60%, transparent 100%);
      top: -80px;
      left: 14%;
      animation: orbFloat1 18s ease-in-out infinite alternate, morphBlob 16s ease-in-out infinite alternate;
      transform: translate(calc(var(--hero-tilt-x, 0) * 25px), calc(var(--hero-tilt-y, 0) * 25px));
    }
    .orb-blue {
      width: 620px;
      height: 620px;
      background: radial-gradient(circle, rgba(37, 99, 235, 0.58) 0%, rgba(30, 64, 175, 0.22) 60%, transparent 100%);
      bottom: -110px;
      right: -80px;
      animation: orbFloat2 22s ease-in-out infinite alternate, morphBlob 18s ease-in-out infinite alternate-reverse;
      transform: translate(calc(var(--hero-tilt-x, 0) * -30px), calc(var(--hero-tilt-y, 0) * -30px));
    }
    .orb-cyan {
      width: 440px;
      height: 440px;
      background: radial-gradient(circle, rgba(6, 182, 212, 0.38) 0%, rgba(59, 130, 246, 0.12) 60%, transparent 100%);
      top: 32%;
      left: 48%;
      animation: orbFloat3 24s ease-in-out infinite alternate;
      transform: translate(calc(var(--hero-tilt-x, 0) * 18px), calc(var(--hero-tilt-y, 0) * 18px));
    }
    .orb-indigo {
      width: 480px;
      height: 480px;
      background: radial-gradient(circle, rgba(99, 102, 241, 0.32) 0%, rgba(79, 70, 229, 0.08) 65%, transparent 100%);
      bottom: 8%;
      left: -110px;
      animation: orbFloat1 20s ease-in-out infinite alternate-reverse;
      transform: translate(calc(var(--hero-tilt-x, 0) * -22px), calc(var(--hero-tilt-y, 0) * -22px));
    }
    @keyframes morphBlob {
      0%   { border-radius: 50%; }
      33%  { border-radius: 60% 40% 70% 30% / 40% 60% 40% 60%; }
      66%  { border-radius: 40% 60% 30% 70% / 60% 30% 70% 40%; }
      100% { border-radius: 52% 48% 42% 58% / 54% 46% 54% 46%; }
    }
    @keyframes orbFloat1 {
      0%   { transform: translate(calc(var(--hero-tilt-x, 0) * 25px), calc(var(--hero-tilt-y, 0) * 25px)) scale(1); }
      100% { transform: translate(calc(var(--hero-tilt-x, 0) * 25px + 70px), calc(var(--hero-tilt-y, 0) * 25px + 50px)) scale(1.12); }
    }
    @keyframes orbFloat2 {
      0%   { transform: translate(calc(var(--hero-tilt-x, 0) * -30px), calc(var(--hero-tilt-y, 0) * -30px)) scale(1); }
      100% { transform: translate(calc(var(--hero-tilt-x, 0) * -30px - 60px), calc(var(--hero-tilt-y, 0) * -30px - 70px)) scale(1.1); }
    }
    @keyframes orbFloat3 {
      0%   { transform: translate(calc(var(--hero-tilt-x, 0) * 18px), calc(var(--hero-tilt-y, 0) * 18px)) scale(1); }
      50%  { transform: translate(calc(var(--hero-tilt-x, 0) * 18px - 40px), calc(var(--hero-tilt-y, 0) * 18px + 30px)) scale(1.08); }
      100% { transform: translate(calc(var(--hero-tilt-x, 0) * 18px + 30px), calc(var(--hero-tilt-y, 0) * 18px - 40px)) scale(0.96); }
    }

    /* ── Cyber Grid Pattern & Beam Light Sweep ── */
    .hero-grid-pattern {
      position: absolute;
      inset: 0;
      background-image: 
        linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
      background-size: 42px 42px;
      mask-image: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.95) 25%, transparent 78%);
      -webkit-mask-image: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.95) 25%, transparent 78%);
      pointer-events: none;
      z-index: 1;
      transform: translate(calc(var(--hero-tilt-x, 0) * 6px), calc(var(--hero-tilt-y, 0) * 6px));
      transition: transform 0.3s ease-out;
    }
    .hero-grid-beam {
      position: absolute;
      top: 0;
      left: -60%;
      width: 220%;
      height: 100%;
      background: linear-gradient(
        90deg,
        transparent 0%,
        transparent 40%,
        rgba(56, 189, 248, 0.09) 50%,
        rgba(245, 158, 11, 0.06) 54%,
        transparent 65%,
        transparent 100%
      );
      transform: skewX(-25deg);
      animation: gridBeamSweep 14s cubic-bezier(0.4, 0, 0.2, 1) infinite;
      pointer-events: none;
      z-index: 1;
    }
    @keyframes gridBeamSweep {
      0% { transform: translateX(-100%) skewX(-25deg); }
      35%, 100% { transform: translateX(100%) skewX(-25deg); }
    }

    /* ── Floating Sparkle Particles ── */
    .hero-particles {
      position: absolute;
      inset: 0;
      pointer-events: none;
      z-index: 1;
      overflow: hidden;
    }
    .particle {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      animation: particleDrift linear infinite;
      opacity: 0;
      will-change: transform, opacity;
    }
    .p-1  { width: 4px; height: 4px; background: #38BDF8; left: 10%; bottom: 8%; animation-duration: 9s; animation-delay: 0s; box-shadow: 0 0 10px #38BDF8; }
    .p-2  { width: 3px; height: 3px; background: #F59E0B; left: 22%; bottom: 14%; animation-duration: 12s; animation-delay: 2s; box-shadow: 0 0 8px #F59E0B; }
    .p-3  { width: 5px; height: 5px; background: #60A5FA; left: 36%; bottom: 6%; animation-duration: 14s; animation-delay: 4s; box-shadow: 0 0 12px #60A5FA; }
    .p-4  { width: 3px; height: 3px; background: #FDE68A; left: 50%; bottom: 10%; animation-duration: 10s; animation-delay: 1s; box-shadow: 0 0 6px #FDE68A; }
    .p-5  { width: 4px; height: 4px; background: #38BDF8; left: 66%; bottom: 5%; animation-duration: 13s; animation-delay: 3s; box-shadow: 0 0 10px #38BDF8; }
    .p-6  { width: 3px; height: 3px; background: #93C5FD; left: 80%; bottom: 12%; animation-duration: 11s; animation-delay: 5s; box-shadow: 0 0 8px #93C5FD; }
    .p-7  { width: 5px; height: 5px; background: #F59E0B; left: 86%; bottom: 18%; animation-duration: 15s; animation-delay: 2.5s; box-shadow: 0 0 12px #F59E0B; }
    .p-8  { width: 4px; height: 4px; background: #38BDF8; left: 18%; bottom: 22%; animation-duration: 11s; animation-delay: 6s; box-shadow: 0 0 8px #38BDF8; }
    .p-9  { width: 3px; height: 3px; background: #FFFFFF; left: 42%; bottom: 20%; animation-duration: 13s; animation-delay: 4.5s; box-shadow: 0 0 8px #FFFFFF; }
    .p-10 { width: 4px; height: 4px; background: #FBBF24; left: 60%; bottom: 16%; animation-duration: 10s; animation-delay: 7s; box-shadow: 0 0 10px #FBBF24; }
    .p-11 { width: 3px; height: 3px; background: #67E8F9; left: 74%; bottom: 26%; animation-duration: 14s; animation-delay: 1.5s; box-shadow: 0 0 8px #67E8F9; }
    .p-12 { width: 4px; height: 4px; background: #93C5FD; left: 93%; bottom: 10%; animation-duration: 12s; animation-delay: 5.5s; box-shadow: 0 0 10px #93C5FD; }

    @keyframes particleDrift {
      0% {
        transform: translateY(0) translateX(0) scale(0.6);
        opacity: 0;
      }
      15% {
        opacity: 0.85;
      }
      50% {
        transform: translateY(-280px) translateX(28px) scale(1);
        opacity: 0.95;
      }
      85% {
        opacity: 0.6;
      }
      100% {
        transform: translateY(-580px) translateX(-24px) scale(0.7);
        opacity: 0;
      }
    }

    /* ── 3. Hero Content & Typography ── */
    .hero-container {
      position: relative;
      z-index: 2;
      display: grid;
      grid-template-columns: 1.15fr 0.85fr;
      gap: 3.5rem;
      align-items: center;
    }
    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.65rem;
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border: 1px solid rgba(255, 255, 255, 0.22);
      padding: 0.45rem 1.15rem;
      border-radius: 9999px;
      font-size: 0.82rem;
      font-weight: 600;
      color: #F8FAFC;
      letter-spacing: 0.4px;
      margin-bottom: 1.35rem;
      box-shadow: 0 4px 18px rgba(0, 0, 0, 0.15);
      animation: fadeIn 0.8s ease;
    }
    .hero-pulse-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #10B981;
      box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
      animation: pulseDot 2s infinite;
      flex-shrink: 0;
    }
    @keyframes pulseDot {
      0%   { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
      70%  { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
      100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .hero-title {
      display: flex;
      flex-direction: column;
      gap: 0.2rem;
      margin-bottom: 1.35rem;
      line-height: 1.08;
    }
    .hero-title-lead {
      font-size: clamp(2.3rem, 4.2vw, 3.8rem);
      font-weight: 800;
      color: #FFFFFF;
      letter-spacing: -0.03em;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
    }
    .hero-title-highlight {
      font-size: clamp(2.5rem, 4.8vw, 4.2rem);
      font-weight: 850;
      background: linear-gradient(135deg, #FDE68A 0%, #F59E0B 45%, #D97706 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      filter: drop-shadow(0 4px 22px rgba(245, 158, 11, 0.42));
      letter-spacing: -0.03em;
    }
    .hero-title-sub {
      font-size: clamp(1.7rem, 3.2vw, 2.6rem);
      font-weight: 700;
      color: rgba(255, 255, 255, 0.95);
      display: flex;
      align-items: center;
      gap: 0.9rem;
      letter-spacing: -0.02em;
    }
    .hero-sub-pill {
      font-size: 0.8rem;
      font-weight: 700;
      color: #38BDF8;
      background: rgba(56, 189, 248, 0.16);
      border: 1px solid rgba(56, 189, 248, 0.4);
      padding: 0.25rem 0.8rem;
      border-radius: 9999px;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      vertical-align: middle;
      text-transform: uppercase;
      letter-spacing: 0.8px;
    }
    .hero-description {
      font-size: 1.08rem;
      color: rgba(241, 245, 249, 0.92);
      line-height: 1.75;
      max-width: 540px;
      margin-bottom: 1.25rem;
      text-shadow: 0 1px 4px rgba(0, 0, 0, 0.35);
    }
    .hero-trust-row {
      display: flex;
      align-items: center;
      gap: 1.25rem;
      flex-wrap: wrap;
      margin-bottom: 1.75rem;
    }
    .trust-item {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      font-size: 0.84rem;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.88);
    }
    .trust-item i {
      color: #34D399;
      font-size: 1rem;
    }

    /* ── 4. Hero CTA Actions ── */
    .hero-actions {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      margin-bottom: 2rem;
    }
    .btn-hero-primary {
      display: inline-flex;
      align-items: center;
      gap: 0.65rem;
      background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
      color: #FFFFFF !important;
      font-size: 1.02rem;
      font-weight: 700;
      padding: 0.95rem 1.75rem;
      border-radius: 9999px;
      text-decoration: none;
      box-shadow: 0 12px 28px -4px rgba(217, 119, 6, 0.5), 0 4px 12px rgba(245, 158, 11, 0.3);
      position: relative;
      overflow: hidden;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-hero-primary::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 60%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.35), transparent);
      transform: skewX(-20deg);
      transition: left 0.75s ease;
    }
    .btn-hero-primary:hover {
      transform: translateY(-3px) scale(1.02);
      box-shadow: 0 16px 36px -4px rgba(217, 119, 6, 0.65), 0 6px 16px rgba(245, 158, 11, 0.4);
    }
    .btn-hero-primary:hover::before {
      left: 140%;
    }
    .btn-hero-primary .btn-arrow {
      transition: transform 0.25s ease;
    }
    .btn-hero-primary:hover .btn-arrow {
      transform: translateX(4px);
    }
    .btn-hero-glass {
      display: inline-flex;
      align-items: center;
      gap: 0.65rem;
      background: rgba(255, 255, 255, 0.12);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border: 1.5px solid rgba(255, 255, 255, 0.3);
      color: #FFFFFF !important;
      font-size: 1rem;
      font-weight: 600;
      padding: 0.95rem 1.65rem;
      border-radius: 9999px;
      text-decoration: none;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-hero-glass:hover {
      background: rgba(255, 255, 255, 0.22);
      border-color: rgba(255, 255, 255, 0.6);
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
    }

    /* ── 5. Floating Glassmorphic Stats Dock ── */
    .hero-stats-dock {
      display: flex;
      align-items: center;
      gap: 1.25rem;
      padding: 1rem 1.45rem;
      background: rgba(15, 23, 42, 0.55);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.14);
      border-radius: 22px;
      box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.35);
      max-width: fit-content;
    }
    .dock-stat {
      display: flex;
      align-items: center;
      gap: 0.85rem;
    }
    .dock-stat-icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      flex-shrink: 0;
    }
    .dock-stat-icon.gold {
      background: rgba(245, 158, 11, 0.2);
      border: 1px solid rgba(245, 158, 11, 0.4);
      color: #FBBF24;
    }
    .dock-stat-icon.blue {
      background: rgba(56, 189, 248, 0.2);
      border: 1px solid rgba(56, 189, 248, 0.4);
      color: #38BDF8;
    }
    .dock-stat-icon.green {
      background: rgba(16, 185, 129, 0.2);
      border: 1px solid rgba(16, 185, 129, 0.4);
      color: #34D399;
    }
    .dock-stat-value {
      font-family: var(--font-heading);
      font-size: 1.55rem;
      font-weight: 800;
      color: #FFFFFF;
      line-height: 1.1;
      letter-spacing: -0.02em;
    }
    .dock-stat-label {
      font-size: 0.76rem;
      font-weight: 600;
      color: rgba(241, 245, 249, 0.75);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .dock-divider {
      width: 1px;
      height: 38px;
      background: rgba(255, 255, 255, 0.15);
    }

    /* ── 6. Right Side Interactive Quick Service Cards ── */
    .hero-cards-wrapper {
      display: flex;
      flex-direction: column;
      gap: 0.95rem;
      position: relative;
      z-index: 2;
      animation: fadeSlideUp 0.8s ease-out 0.2s both;
    }
    .hero-cards-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 0.5rem;
    }
    .cards-header-title {
      font-size: 0.88rem;
      font-weight: 700;
      color: rgba(255, 255, 255, 0.92);
      display: flex;
      align-items: center;
      gap: 0.45rem;
      text-transform: uppercase;
      letter-spacing: 0.6px;
    }
    .cards-header-title i {
      color: #FBBF24;
    }
    .cards-header-tag {
      font-size: 0.74rem;
      font-weight: 700;
      color: #38BDF8;
      background: rgba(56, 189, 248, 0.15);
      border: 1px solid rgba(56, 189, 248, 0.35);
      padding: 0.2rem 0.65rem;
      border-radius: 9999px;
      letter-spacing: 0.4px;
    }
    .hero-cards {
      display: flex;
      flex-direction: column;
      gap: 0.85rem;
    }
    .hero-card {
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.18);
      border-radius: 20px;
      padding: 1.15rem 1.35rem;
      display: flex;
      align-items: center;
      gap: 1.1rem;
      cursor: pointer;
      text-decoration: none;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      position: relative;
      overflow: hidden;
    }
    .hero-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: transparent;
      transition: background 0.3s ease;
    }
    .hero-card[data-doc="clearance"]::after { background: #10B981; }
    .hero-card[data-doc="residency"]::after { background: #3B82F6; }
    .hero-card[data-doc="indigency"]::after { background: #F43F5E; }

    .hero-card:hover {
      background: rgba(255, 255, 255, 0.16);
      border-color: rgba(255, 255, 255, 0.38);
      transform: translateY(-4px) translateX(6px);
      box-shadow: 0 18px 36px -8px rgba(0, 0, 0, 0.4);
    }
    .hero-card-icon {
      width: 52px;
      height: 52px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      flex-shrink: 0;
      box-shadow: 0 8px 18px rgba(0, 0, 0, 0.2);
      transition: transform 0.3s ease;
    }
    .hero-card:hover .hero-card-icon {
      transform: scale(1.08) rotate(3deg);
    }
    .card-icon-green {
      background: linear-gradient(135deg, #059669 0%, #10B981 100%);
      color: #FFFFFF;
    }
    .card-icon-blue {
      background: linear-gradient(135deg, #1D4ED8 0%, #3B82F6 100%);
      color: #FFFFFF;
    }
    .card-icon-rose {
      background: linear-gradient(135deg, #BE123C 0%, #F43F5E 100%);
      color: #FFFFFF;
    }
    .hero-card-info {
      flex: 1;
    }
    .card-title-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.5rem;
      margin-bottom: 0.25rem;
    }
    .hero-card-info h3 {
      font-size: 1.05rem;
      font-weight: 700;
      color: #FFFFFF;
      margin: 0;
      letter-spacing: -0.01em;
    }
    .card-pill {
      font-size: 0.68rem;
      font-weight: 700;
      padding: 0.15rem 0.55rem;
      border-radius: 9999px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .pill-green {
      background: rgba(16, 185, 129, 0.2);
      color: #6EE7B7;
      border: 1px solid rgba(16, 185, 129, 0.4);
    }
    .pill-blue {
      background: rgba(59, 130, 246, 0.2);
      color: #93C5FD;
      border: 1px solid rgba(59, 130, 246, 0.4);
    }
    .pill-rose {
      background: rgba(244, 63, 94, 0.2);
      color: #FDA4AF;
      border: 1px solid rgba(244, 63, 94, 0.4);
    }
    .hero-card-info p {
      font-size: 0.84rem;
      color: rgba(241, 245, 249, 0.8);
      margin: 0 0 0.4rem 0;
      line-height: 1.4;
    }
    .card-meta-line {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.75rem;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.7);
    }
    .card-meta-line .text-free {
      color: #6EE7B7;
      font-weight: 700;
    }
    .hero-card-arrow {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      color: rgba(255, 255, 255, 0.6);
      font-size: 0.95rem;
      flex-shrink: 0;
      transition: all 0.25s ease;
    }
    .hero-card:hover .hero-card-arrow {
      background: #F59E0B;
      color: #FFFFFF;
      transform: translateX(4px);
      box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    }

    /* Quick Tracking Bar */
    .hero-quick-track-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.85rem 1.25rem;
      background: rgba(15, 23, 42, 0.55);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px dashed rgba(255, 255, 255, 0.25);
      border-radius: 16px;
      text-decoration: none;
      transition: all 0.25s ease;
    }
    .hero-quick-track-bar:hover {
      background: rgba(15, 23, 42, 0.8);
      border-color: #38BDF8;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    }
    .track-bar-content {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      width: 100%;
    }
    .track-icon {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      background: rgba(56, 189, 248, 0.2);
      color: #38BDF8;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.9rem;
      flex-shrink: 0;
    }
    .track-text {
      font-size: 0.82rem;
      color: rgba(255, 255, 255, 0.82);
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex: 1;
    }
    .track-text strong {
      color: #38BDF8;
      font-weight: 700;
    }

    /* ── Responsive adjustments & Mobile Menu ── */
    html {
      overflow-x: hidden;
      max-width: 100vw;
    }
    body {
      overflow-x: hidden;
      max-width: 100vw;
      position: relative;
    }

    /* Desktop Navigation Defaults */
    @media (min-width: 769px) {
      .mobile-nav-items {
        display: flex;
        align-items: center;
        gap: var(--space-1);
      }
      .mobile-nav-icon {
        display: none !important;
      }
      .mobile-nav-cta {
        display: none !important;
      }
    }

    /* Tablet & Medium Breakpoint (<= 1024px) */
    @media (max-width: 1024px) {
      .hero-container {
        grid-template-columns: 1fr;
        gap: 3rem;
      }
      .hero-content {
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
      }
      .hero-title-sub {
        justify-content: center;
      }
      .hero-trust-row {
        justify-content: center;
      }
      .hero-actions {
        justify-content: center;
      }
      .hero-stats-dock {
        margin: 0 auto;
      }
      .hero-cards-wrapper {
        max-width: 520px;
        margin: 0 auto;
        width: 100%;
      }
    }

    /* Mobile Breakpoint (<= 768px) */
    @media (max-width: 768px) {
      .nav-links {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        height: 100dvh !important;
        background: var(--color-surface) !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        padding: 5rem 1.5rem 2.25rem 1.5rem !important;
        transform: translateX(100%) !important;
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
        z-index: 9999 !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        box-shadow: -10px 0 35px rgba(0, 0, 0, 0.25) !important;
      }
      .nav-links.open {
        transform: translateX(0) !important;
      }
      .mobile-nav-items {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        width: 100%;
      }
      .mobile-nav-items a {
        display: flex !important;
        align-items: center !important;
        gap: 0.85rem !important;
        padding: 0.85rem 1.25rem !important;
        font-size: 1.05rem !important;
        font-weight: 600 !important;
        border-radius: 14px !important;
        color: var(--color-text) !important;
        text-shadow: none !important;
        background: rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease;
      }
      .mobile-nav-items a:hover {
        background: var(--color-primary-50);
        color: var(--color-primary) !important;
      }
      .mobile-nav-items a.active {
        background: var(--gradient-primary) !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 14px rgba(30, 64, 175, 0.3) !important;
        border-color: transparent !important;
      }
      .mobile-nav-icon {
        display: inline-flex !important;
        font-size: 1.15rem;
        width: 24px;
        justify-content: center;
      }
      .mobile-nav-cta {
        display: flex !important;
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
        padding-top: 1.5rem;
        border-top: 1px solid var(--color-border);
        margin-top: 1.5rem;
      }
      .mobile-nav-cta .btn {
        padding: 0.85rem 1.25rem;
        font-size: 1rem;
        border-radius: 12px;
        font-weight: 600;
        width: 100%;
        display: flex !important;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
      }
      .menu-toggle {
        z-index: 10001 !important;
      }
      .header:not(.scrolled) .menu-toggle:not(.active) span {
        background: #FFFFFF !important;
      }
      .menu-toggle.active span {
        background: var(--color-text) !important;
      }

      body.mobile-menu-open .chat-widget {
        display: none !important;
      }

      .hero-stats-dock {
        flex-direction: column;
        width: 100%;
        gap: 1rem;
        align-items: flex-start;
        padding: 1.25rem;
      }
      .dock-divider {
        width: 100%;
        height: 1px;
      }
    }

    /* Small Mobile (<= 640px) */
    @media (max-width: 640px) {
      .hero {
        padding-top: 95px;
        padding-bottom: 50px;
        min-height: auto;
      }
      .hero-badge {
        font-size: 0.74rem;
        padding: 0.4rem 0.85rem;
        max-width: 100%;
        white-space: normal;
        text-align: center;
        line-height: 1.4;
      }
      .hero-title-lead {
        font-size: clamp(1.8rem, 7vw, 2.3rem);
      }
      .hero-title-highlight {
        font-size: clamp(2.1rem, 8vw, 2.7rem);
      }
      .hero-title-sub {
        font-size: clamp(1.4rem, 6vw, 1.9rem);
        flex-wrap: wrap;
      }
      .hero-actions {
        width: 100%;
        flex-direction: column;
      }
      .hero-actions .btn-hero-primary,
      .hero-actions .btn-hero-glass {
        width: 100%;
        justify-content: center;
        text-align: center;
      }
      .hero-quick-track-bar {
        padding: 0.85rem 1rem;
      }
      .track-bar-content {
        flex-direction: row;
        align-items: center;
      }
      .track-text {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.2rem;
      }
    }

    /* Dark Mode Mobile Overrides */
    [data-theme="dark"] .mobile-nav-items a {
      color: #E2E8F0 !important;
      background: rgba(255, 255, 255, 0.05);
      border-color: rgba(255, 255, 255, 0.08);
    }
    [data-theme="dark"] .mobile-nav-items a:hover {
      background: rgba(255, 255, 255, 0.1);
      color: #FFFFFF !important;
    }
    [data-theme="dark"] .mobile-nav-items a.active {
      background: var(--gradient-primary) !important;
      color: #FFFFFF !important;
    }

    /* ========================================================
       ISTONG AI CHAT WIDGET ENHANCEMENTS
       ======================================================== */
    .chat-widget {
      position: fixed;
      bottom: 1.5rem;
      right: 1.5rem;
      z-index: var(--z-chat, 600);
    }
    .chat-toggle-wrap {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      justify-content: flex-end;
    }
    .chat-pill {
      background: rgba(15, 23, 42, 0.88);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #FFFFFF;
      font-size: 0.82rem;
      font-weight: 500;
      padding: 0.55rem 0.95rem;
      border-radius: 9999px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      white-space: nowrap;
      user-select: none;
    }
    .chat-pill strong {
      color: #FBBF24;
      font-weight: 700;
    }
    .chat-pill-sparkle {
      font-size: 0.9rem;
      animation: sparkleRotate 3s infinite ease-in-out;
    }
    @keyframes sparkleRotate {
      0%, 100% { transform: scale(1) rotate(0deg); }
      50% { transform: scale(1.2) rotate(15deg); }
    }
    .chat-pill:hover {
      background: rgba(15, 23, 42, 0.96);
      transform: translateX(-4px);
      border-color: #38BDF8;
      box-shadow: 0 10px 28px rgba(0, 0, 0, 0.35);
    }
    .chat-window.open ~ .chat-toggle-wrap .chat-pill,
    .chat-toggle.active ~ .chat-pill {
      opacity: 0;
      pointer-events: none;
      transform: scale(0.9);
    }

    .chat-toggle {
      width: 58px;
      height: 58px;
      border-radius: var(--radius-full);
      background: linear-gradient(135deg, #1E40AF 0%, #2563EB 55%, #3B82F6 100%);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 28px -4px rgba(37, 99, 235, 0.5), 0 4px 12px rgba(0, 0, 0, 0.2);
      border: 2px solid rgba(255, 255, 255, 0.4);
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      position: relative;
    }
    .chat-toggle:hover {
      transform: translateY(-3px) scale(1.06);
      box-shadow: 0 16px 36px -4px rgba(37, 99, 235, 0.65);
    }
    .chat-toggle.active {
      background: #DC2626 !important;
      border-color: rgba(255, 255, 255, 0.5);
    }
    .chat-toggle .chat-icon-avatar {
      font-size: 1.45rem;
      transition: transform 0.25s ease;
      display: inline-flex;
    }
    .chat-toggle:hover .chat-icon-avatar {
      transform: scale(1.15) rotate(-8deg);
    }
    .chat-toggle .chat-icon-open { display: none; }
    .chat-toggle .chat-icon-close { display: none; font-size: 1.35rem; }
    .chat-toggle.active .chat-icon-avatar { display: none; }
    .chat-toggle.active .chat-icon-close { display: inline-flex; }

    /* Istong AI Window */
    .chat-window {
      position: absolute;
      bottom: 74px;
      right: 0;
      width: 390px;
      height: 560px;
      max-height: calc(100vh - 110px);
      background: var(--color-surface);
      border-radius: 22px;
      box-shadow: 0 24px 60px -8px rgba(0, 0, 0, 0.32), 0 8px 24px -4px rgba(0, 0, 0, 0.15);
      border: 1px solid var(--color-border);
      display: none;
      flex-direction: column;
      overflow: hidden;
      z-index: 1000;
      animation: chatSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .chat-window.open {
      display: flex;
    }

    /* Header */
    .chat-header {
      background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 55%, #1D4ED8 100%);
      padding: 0.95rem 1.15rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .istong-avatar {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      box-shadow: 0 4px 12px rgba(217, 119, 6, 0.4);
      flex-shrink: 0;
    }
    .istong-avatar .avatar-emoji {
      font-size: 1.35rem;
      line-height: 1;
    }
    .istong-avatar .avatar-ai-badge {
      position: absolute;
      bottom: -3px;
      right: -3px;
      background: #10B981;
      color: #FFFFFF;
      font-size: 0.58rem;
      font-weight: 800;
      padding: 0.12rem 0.35rem;
      border-radius: 9999px;
      border: 1.5px solid #0F172A;
      line-height: 1;
      letter-spacing: 0.3px;
    }
    .chat-header-info {
      flex: 1;
      min-width: 0;
    }
    .chat-title-row {
      display: flex;
      align-items: center;
      gap: 0.45rem;
    }
    .chat-title-row h4 {
      color: #FFFFFF;
      font-size: 0.96rem;
      font-weight: 700;
      margin: 0;
      letter-spacing: -0.01em;
    }
    .chat-badge-tag {
      background: rgba(56, 189, 248, 0.22);
      color: #7DD3FC;
      border: 1px solid rgba(56, 189, 248, 0.35);
      font-size: 0.62rem;
      font-weight: 700;
      padding: 0.12rem 0.45rem;
      border-radius: 9999px;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }
    .chat-header-info p {
      font-size: 0.72rem;
      color: rgba(241, 245, 249, 0.85);
      margin: 0.2rem 0 0 0;
      display: flex;
      align-items: center;
    }
    .chat-header-actions {
      display: flex;
      align-items: center;
      gap: 0.35rem;
    }
    .chat-header-btn {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.12);
      border: none;
      color: rgba(255, 255, 255, 0.85);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 0.95rem;
      transition: all 0.2s ease;
    }
    .chat-header-btn:hover {
      background: rgba(255, 255, 255, 0.25);
      color: #FFFFFF;
      transform: scale(1.05);
    }

    /* Body */
    .chat-body {
      flex: 1;
      padding: 1rem 1.15rem;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      background: var(--color-bg);
      scroll-behavior: smooth;
    }
    .chat-msg {
      max-width: 88%;
      padding: 0.75rem 1rem;
      border-radius: 16px;
      font-size: 0.86rem;
      line-height: 1.55;
      animation: msgAppear 0.25s ease-out;
      word-break: break-word;
    }
    .chat-msg.bot {
      background: var(--color-surface);
      color: var(--color-text);
      border: 1px solid var(--color-border);
      align-self: flex-start;
      border-bottom-left-radius: 4px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    .chat-msg.user {
      background: linear-gradient(135deg, #1E40AF 0%, #2563EB 100%);
      color: #FFFFFF;
      align-self: flex-end;
      border-bottom-right-radius: 4px;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }
    .chat-msg strong {
      font-weight: 700;
      color: inherit;
    }
    .chat-msg em {
      font-style: italic;
      opacity: 0.9;
    }
    .chat-list {
      margin: 0.4rem 0 0.2rem 0;
      padding-left: 0;
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 0.3rem;
    }
    .chat-list-item {
      font-size: 0.84rem;
      line-height: 1.45;
    }
    .chat-list-num {
      display: flex;
      align-items: flex-start;
      gap: 0.45rem;
      font-size: 0.84rem;
      line-height: 1.45;
    }
    .chat-num-badge {
      background: rgba(37, 99, 235, 0.15);
      color: #2563EB;
      font-weight: 700;
      font-size: 0.72rem;
      min-width: 18px;
      height: 18px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      margin-top: 2px;
    }

    /* Typing Indicator */
    .typing-msg {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      padding: 0.65rem 0.95rem !important;
    }
    .typing-bubble {
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .typing-bubble span {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #3B82F6;
      animation: typingBounce 1.2s infinite ease-in-out both;
    }
    .typing-bubble span:nth-child(1) { animation-delay: -0.32s; }
    .typing-bubble span:nth-child(2) { animation-delay: -0.16s; }
    .typing-bubble span:nth-child(3) { animation-delay: 0s; }
    @keyframes typingBounce {
      0%, 80%, 100% { transform: scale(0); opacity: 0.35; }
      40% { transform: scale(1); opacity: 1; }
    }
    .typing-label {
      font-size: 0.75rem;
      color: var(--color-text-light);
      font-style: italic;
    }

    /* Prompts */
    .chat-prompts {
      padding: 0.6rem 0.95rem 0.65rem 0.95rem;
      background: var(--color-surface);
      border-top: 1px solid var(--color-border);
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
    }
    .chat-prompts-label {
      font-size: 0.72rem;
      color: var(--color-text-light);
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.35rem;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }
    .chat-prompts-label i {
      color: #F59E0B;
    }
    .chat-prompt-btns {
      display: flex;
      gap: 0.4rem;
      overflow-x: auto;
      padding-bottom: 2px;
      scrollbar-width: none;
    }
    .chat-prompt-btns::-webkit-scrollbar {
      display: none;
    }
    .chat-prompt-btn {
      padding: 0.35rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.74rem;
      font-weight: 600;
      background: var(--color-bg);
      border: 1px solid var(--color-border);
      color: var(--color-text);
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.2s ease;
      flex-shrink: 0;
    }
    .chat-prompt-btn:hover {
      background: var(--color-primary-50);
      border-color: var(--color-primary);
      color: var(--color-primary);
      transform: translateY(-1px);
    }

    /* Footer / Input Form */
    .chat-footer {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1rem;
      background: var(--color-surface);
      border-top: 1px solid var(--color-border);
    }
    #chatInput {
      flex: 1;
      padding: 0.7rem 1.05rem;
      border-radius: 9999px;
      border: 1.5px solid var(--color-border);
      background: var(--color-bg);
      color: var(--color-text);
      font-size: 0.88rem;
      outline: none;
      transition: all 0.2s ease;
    }
    #chatInput:focus {
      border-color: #2563EB;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .chat-send-btn {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #1E40AF 0%, #2563EB 100%);
      color: #FFFFFF;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
      flex-shrink: 0;
    }
    .chat-send-btn:hover:not(:disabled) {
      transform: scale(1.08);
      box-shadow: 0 6px 16px rgba(37, 99, 235, 0.45);
    }
    .chat-send-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }

    /* Dark Mode Theme Overrides */
    [data-theme="dark"] .chat-window {
      background: #0B132B;
      border-color: rgba(255, 255, 255, 0.12);
    }
    [data-theme="dark"] .chat-body {
      background: #070D1F;
    }
    [data-theme="dark"] .chat-msg.bot {
      background: #0F172A;
      color: #E2E8F0;
      border-color: rgba(255, 255, 255, 0.1);
    }
    [data-theme="dark"] .chat-prompts,
    [data-theme="dark"] .chat-footer {
      background: #0B132B;
      border-color: rgba(255, 255, 255, 0.1);
    }
    [data-theme="dark"] #chatInput {
      background: #070D1F;
      border-color: rgba(255, 255, 255, 0.15);
      color: #F8FAFC;
    }
    [data-theme="dark"] .chat-prompt-btn {
      background: #0F172A;
      border-color: rgba(255, 255, 255, 0.12);
      color: #CBD5E1;
    }
    [data-theme="dark"] .chat-prompt-btn:hover {
      background: rgba(37, 99, 235, 0.2);
      border-color: #38BDF8;
      color: #38BDF8;
    }
    [data-theme="dark"] .chat-num-badge {
      background: rgba(56, 189, 248, 0.2);
      color: #38BDF8;
    }

    /* Mobile Responsive Chat */
    @media (max-width: 480px) {
      .chat-window {
        width: calc(100vw - 1.5rem);
        right: -0.5rem;
        bottom: 70px;
        height: calc(100dvh - 100px);
        max-height: none;
        border-radius: 18px;
      }
      .chat-pill {
        display: none;
      }
    }
  </style>
  @if(env('RECAPTCHA_SITE_KEY'))
  <script>
    window.welcomeRecaptchaReady = false;
    window.onWelcomeRecaptchaLoad = function () {
      window.welcomeRecaptchaReady = true;
      if (typeof window.renderWelcomeRecaptcha === 'function') {
        window.renderWelcomeRecaptcha();
      }
    };
  </script>
  <script src="https://www.google.com/recaptcha/api.js?onload=onWelcomeRecaptchaLoad&render=explicit" async defer></script>
  @endif
</head>
@php
  $welcomeUser = auth()->user();
  $welcomeIsResident = $welcomeUser && trim(strtolower($welcomeUser->role)) !== 'admin';
@endphp
<body
  data-is-resident="{{ $welcomeIsResident ? '1' : '0' }}"
  data-login-url="{{ route('resident.login') }}"
  data-request-url="{{ route('resident.request.create') }}"
>

  <!-- Animated Background Blobs -->
  <div class="bg-blobs" aria-hidden="true">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
  </div>

  <!-- ============================
       HEADER / NAVIGATION
       ============================ -->
  <header class="header" id="header">
    <div class="container">
      <a href="#" class="logo" id="logo">
        <div class="logo-icon">
          <!-- Image path updated for Laravel -->
          <img src="{{ asset('images/logo.png') }}" alt="E-Barangay Logo">
        </div>
        <div class="logo-text">
          E-Barangay
          <span>Cogon East • Carmen, Cebu</span>
        </div>
      </a>

      @php
        $navUser = auth()->user();
        $isLoggedInResident = $navUser && trim(strtolower($navUser->role)) !== 'admin';
        $navInitials = collect(explode(' ', trim($navUser?->name ?? '')))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
      @endphp

      <nav class="nav-links" id="navLinks">
        <div class="mobile-nav-items">
          <a href="#hero" class="active"><i class="icon-home mobile-nav-icon"></i> Home</a>
          <a href="#how-it-works"><i class="icon-help-circle mobile-nav-icon"></i> How It Works</a>
          <a href="#services"><i class="icon-file-text mobile-nav-icon"></i> Documents</a>
          <a href="#news"><i class="icon-bell mobile-nav-icon"></i> News</a>
          <a href="#concerns"><i class="icon-message-square mobile-nav-icon"></i> Community Concern</a>
          <a href="#emergency"><i class="icon-alert-triangle mobile-nav-icon"></i> Emergency</a>
        </div>

        <div class="mobile-nav-cta">
          @if($isLoggedInResident)
            <a href="{{ route('resident.dashboard') }}" class="btn btn-primary">
              <i class="icon-home"></i> My Resident Dashboard
            </a>
            <form method="POST" action="{{ route('resident.logout') }}" style="width: 100%;">
              @csrf
              <button type="submit" class="btn btn-outline">
                <i class="icon-log-out"></i> Logout
              </button>
            </form>
          @else
            <a href="{{ route('resident.login') }}" class="btn btn-outline" id="mobileLoginBtn">
              <i class="icon-log-in"></i> Log In
            </a>
            <a href="{{ route('resident.register') }}" class="btn btn-primary" id="mobileSignupBtn">
              <i class="icon-user-plus"></i> Sign Up
            </a>
          @endif
        </div>
      </nav>

      <div class="nav-cta">

        @if($isLoggedInResident)
          <div class="welcome-user-nav">
            <div class="welcome-notif-wrap" id="welcomeNotifWrap">
              <button type="button" class="welcome-icon-btn" id="welcomeNotifBtn" aria-label="Notifications" title="Notifications">
                <i class="icon-bell"></i>
                @if($navUser->unreadNotifications->count() > 0)
                  <span class="welcome-badge" id="welcomeNotifBadge">{{ $navUser->unreadNotifications->count() }}</span>
                @endif
              </button>
              <div class="welcome-dropdown welcome-notif-dropdown" id="welcomeNotifDropdown">
                <div class="welcome-dropdown-header">
                  <span>Notifications</span>
                </div>
                <div class="welcome-dropdown-body">
                  @forelse($navUser->notifications->take(8) as $notification)
                    @php
                      $notifType = $notification->data['type'] ?? 'document';
                      $notifTitle = $notifType === 'concern_update'
                          ? ($notification->data['title'] ?? 'Community Concern Update')
                          : ($notification->data['document_type'] ?? 'Document Update');
                      $notifMeta = $notifType === 'concern_update'
                          ? ('ID: ' . ($notification->data['concern_id'] ?? 'N/A'))
                          : ('TRK: ' . ($notification->data['tracking_number'] ?? 'N/A'));
                    @endphp
                    <div class="welcome-notif-item {{ $notification->read_at === null ? 'unread' : '' }}">
                      <div class="welcome-notif-title">{{ $notifTitle }}</div>
                      <div class="welcome-notif-message">{{ $notification->data['message'] ?? 'You have a new update.' }}</div>
                      <div class="welcome-notif-meta">{{ $notifMeta }} • {{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                  @empty
                    <div class="welcome-dropdown-empty">
                      <i class="icon-bell-off"></i>
                      <span>No new notifications</span>
                    </div>
                  @endforelse
                </div>
              </div>
            </div>

            <div class="welcome-profile-wrap" id="welcomeProfileWrap">
              <button type="button" class="welcome-profile-btn" id="welcomeProfileBtn" aria-label="Open profile menu">
                @if($navUser->profile_picture)
                  <img src="{{ \Illuminate\Support\Facades\Storage::url($navUser->profile_picture) }}" alt="{{ $navUser->name }}" class="welcome-profile-avatar">
                @else
                  <span class="welcome-profile-avatar welcome-profile-avatar-fallback">{{ $navInitials ?: 'R' }}</span>
                @endif
                <span class="welcome-profile-name">{{ $navUser->name }}</span>
                <i class="icon-chevron-down welcome-profile-chevron"></i>
              </button>
              <div class="welcome-dropdown welcome-profile-dropdown" id="welcomeProfileDropdown">
                <div class="welcome-profile-card">
                  @if($navUser->profile_picture)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($navUser->profile_picture) }}" alt="{{ $navUser->name }}" class="welcome-profile-card-avatar">
                  @else
                    <span class="welcome-profile-card-avatar welcome-profile-avatar-fallback">{{ $navInitials ?: 'R' }}</span>
                  @endif
                  <div>
                    <div class="welcome-profile-card-name">{{ $navUser->name }}</div>
                    <div class="welcome-profile-card-email">{{ $navUser->email ?? 'No email provided' }}</div>
                  </div>
                </div>
                <div class="welcome-dropdown-menu">
                  <a href="{{ route('resident.settings') }}" class="welcome-dropdown-link">
                    <i class="icon-user"></i> My Profile
                  </a>
                  <a href="{{ route('resident.dashboard') }}" class="welcome-dropdown-link">
                    <i class="icon-home"></i> My Dashboard
                  </a>
                  <form method="POST" action="{{ route('resident.logout') }}" class="welcome-logout-form">
                    @csrf
                    <button type="submit" class="welcome-dropdown-link welcome-dropdown-logout">
                      <i class="icon-log-out"></i> Logout
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        @else
          <a href="{{ route('resident.login') }}" class="btn btn-outline-nav" id="loginBtn">
            <i class="icon-log-in"></i>
            Log In
          </a>
          <a href="{{ route('resident.register') }}" class="btn btn-primary" id="signupBtn">
            <i class="icon-user-plus"></i>
            Sign Up
          </a>
        @endif

        <button class="dark-mode-toggle" id="darkModeToggle" aria-label="Toggle dark mode" title="Toggle dark mode">
          <i class="icon-moon dark-icon-moon"></i>
          <i class="icon-sun dark-icon-sun"></i>
        </button>
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
    </div>
  </header>

  <!-- ============================
       HERO SECTION
       ============================ -->
  <section class="hero" id="hero">
    <div class="hero-bg" id="heroBg">
      <img src="{{ asset('images/hero.png') }}" alt="Barangay Cogon East, Carmen, Cebu community" loading="eager">
      <div class="hero-bg-vignette"></div>

      <!-- Interactive Cursor-Follow Spotlight -->
      <div class="hero-interactive-spotlight" id="heroSpotlight"></div>

      <!-- Rotating Holographic Accent Rings -->
      <div class="hero-bg-ring hero-bg-ring-1"></div>
      <div class="hero-bg-ring hero-bg-ring-2"></div>

      <!-- Ambient Morphing Aurora Orbs -->
      <div class="hero-ambient-orb orb-gold"></div>
      <div class="hero-ambient-orb orb-blue"></div>
      <div class="hero-ambient-orb orb-cyan"></div>
      <div class="hero-ambient-orb orb-indigo"></div>

      <!-- Animated Grid Pattern & Light Beam Sweep -->
      <div class="hero-grid-pattern"></div>
      <div class="hero-grid-beam"></div>

      <!-- Floating Sparkles & Light Particles -->
      <div class="hero-particles" aria-hidden="true">
        <span class="particle p-1"></span>
        <span class="particle p-2"></span>
        <span class="particle p-3"></span>
        <span class="particle p-4"></span>
        <span class="particle p-5"></span>
        <span class="particle p-6"></span>
        <span class="particle p-7"></span>
        <span class="particle p-8"></span>
        <span class="particle p-9"></span>
        <span class="particle p-10"></span>
        <span class="particle p-11"></span>
        <span class="particle p-12"></span>
      </div>
    </div>

    <div class="container hero-container">
      <div class="hero-content">
        
        <!-- Live Verified Badge -->
        <div class="hero-badge">
          <span class="hero-pulse-dot"></span>
          <span>Official LGU Digital Portal &bull; Cogon East, Carmen, Cebu</span>
        </div>

        <!-- Grand Headline -->
        <h1 class="hero-title">
          <span class="hero-title-lead">Request &amp; Track</span>
          <span class="hero-title-highlight">Barangay Documents</span>
          <span class="hero-title-sub">
            <span>Online</span>
            <span class="hero-sub-pill"><i class="icon-activity"></i> Real-Time</span>
          </span>
        </h1>

        <!-- High-readability Description -->
        <p class="hero-description">
          Modernizing public service for all residents of Barangay Cogon East, Carmen, Cebu. 
          Submit certificate requests online, receive instant SMS/email updates, and skip the queue at the barangay hall.
        </p>

        <!-- Feature Trust Pills Row -->
        <div class="hero-trust-row">
          <div class="trust-item">
            <i class="icon-check-circle"></i>
            <span>Fast 1–2 Day Turnaround</span>
          </div>
          <div class="trust-item">
            <i class="icon-shield-check"></i>
            <span>RA 10173 Privacy Protected</span>
          </div>
          <div class="trust-item">
            <i class="icon-clock"></i>
            <span>24/7 Digital Filing</span>
          </div>
        </div>

        <!-- CTA Buttons -->
        <div class="hero-actions">
          <a href="{{ route('resident.request.create') }}" class="btn-hero-primary request-doc-btn">
            <i class="icon-file-plus"></i>
            <span>Request Document Now</span>
            <i class="icon-arrow-right btn-arrow"></i>
          </a>

          @if($isLoggedInResident)
            <a href="{{ route('resident.dashboard') }}" class="btn-hero-glass" id="heroDashboardBtn">
              <i class="icon-home"></i>
              <span>My Resident Dashboard</span>
            </a>
          @else
            <a href="{{ route('resident.register') }}" class="btn-hero-glass" id="heroSignupBtn">
              <i class="icon-user-plus"></i>
              <span>Sign Up</span>
            </a>
          @endif
        </div>

        <!-- Floating Glassmorphic Stats Dock -->
        <div class="hero-stats-dock">
          <div class="dock-stat">
            <div class="dock-stat-icon gold"><i class="icon-users"></i></div>
            <div>
              <div class="dock-stat-value">3,316+</div>
              <div class="dock-stat-label">Residents Served</div>
            </div>
          </div>
          <div class="dock-divider"></div>
          <div class="dock-stat">
            <div class="dock-stat-icon blue"><i class="icon-files"></i></div>
            <div>
              <div class="dock-stat-value">5+</div>
              <div class="dock-stat-label">Official Services</div>
            </div>
          </div>
          <div class="dock-divider"></div>
          <div class="dock-stat">
            <div class="dock-stat-icon green"><i class="icon-zap"></i></div>
            <div>
              <div class="dock-stat-value">24/7</div>
              <div class="dock-stat-label">Instant Access</div>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Interactive Quick Service Cards -->
      <div class="hero-cards-wrapper">
        <div class="hero-cards-header">
          <div class="cards-header-title">
            <i class="icon-sparkles"></i> Popular Barangay Services
          </div>
          <span class="cards-header-tag">Instant Request</span>
        </div>

        <div class="hero-cards">
          <!-- Card 1: Clearance -->
          <a href="#services" class="hero-card" data-doc="clearance">
            <div class="hero-card-icon card-icon-green">
              <i class="icon-file-badge"></i>
            </div>
            <div class="hero-card-info">
              <div class="card-title-row">
                <h3>Barangay Clearance</h3>
                <span class="card-pill pill-green">Most Requested</span>
              </div>
              <p>For employment, permits &amp; legal transactions</p>
              <div class="card-meta-line">
                <span><i class="icon-clock"></i> 1–2 Days</span>
                <span>&bull;</span>
                <span>₱50.00</span>
              </div>
            </div>
            <div class="hero-card-arrow">
              <i class="icon-arrow-right"></i>
            </div>
          </a>

          <!-- Card 2: Residency -->
          <a href="#services" class="hero-card" data-doc="residency">
            <div class="hero-card-icon card-icon-blue">
              <i class="icon-home"></i>
            </div>
            <div class="hero-card-info">
              <div class="card-title-row">
                <h3>Certificate of Residency</h3>
                <span class="card-pill pill-blue">Fast Release</span>
              </div>
              <p>Official proof of residence in Cogon East</p>
              <div class="card-meta-line">
                <span><i class="icon-clock"></i> Same-Day</span>
                <span>&bull;</span>
                <span>₱50.00</span>
              </div>
            </div>
            <div class="hero-card-arrow">
              <i class="icon-arrow-right"></i>
            </div>
          </a>

          <!-- Card 3: Indigency -->
          <a href="#services" class="hero-card" data-doc="indigency">
            <div class="hero-card-icon card-icon-rose">
              <i class="icon-heart-handshake"></i>
            </div>
            <div class="hero-card-info">
              <div class="card-title-row">
                <h3>Certificate of Indigency</h3>
                <span class="card-pill pill-rose">100% Free / Libre</span>
              </div>
              <p>For financial aid, scholarships &amp; medical help</p>
              <div class="card-meta-line">
                <span><i class="icon-check-circle"></i> Priority SLA</span>
                <span>&bull;</span>
                <span class="text-free">FREE</span>
              </div>
            </div>
            <div class="hero-card-arrow">
              <i class="icon-arrow-right"></i>
            </div>
          </a>
        </div>

        <!-- Quick Tracking Lookup Bar -->
        <a href="{{ route('resident.request.track') }}" class="hero-quick-track-bar">
          <div class="track-bar-content">
            <div class="track-icon"><i class="icon-search"></i></div>
            <div class="track-text">
              <span>Already have an active request?</span>
              <strong>Track with Reference Number &rarr;</strong>
            </div>
          </div>
        </a>
      </div>

    </div>
  </section>

  <!-- ============================
       HOW IT WORKS SECTION
       ============================ -->
  <section class="guidance-section" id="how-it-works">
    <div class="container">
      <div class="guidance-header fade-in-up">
        <div class="section-label">How It Works</div>
        <h2 class="section-title">Request Documents in 4 Easy Steps</h2>
        <p class="section-subtitle">
          No more long queues or repeated visits. Our web-based system streamlines the 
          entire document request process from submission to pickup.
        </p>
      </div>

      <!-- Step-by-step process -->
      <div class="how-it-works-steps fade-in-up">

        <div class="hiw-step">
          <div class="hiw-node">
            <div class="hiw-node-ring"></div>
            <div class="hiw-step-number">1</div>
          </div>
          <div class="hiw-step-body">
            <div class="hiw-step-icon">
              <i class="icon-user-plus"></i>
            </div>
            <h3>Register &amp; Verify</h3>
            <p>Create your account online. Barangay officials will verify your identity as a legitimate resident of Cogon East before activating your account.</p>
            <div class="hiw-step-tag">Step 1</div>
          </div>
        </div>

        <div class="hiw-step">
          <div class="hiw-node">
            <div class="hiw-node-ring"></div>
            <div class="hiw-step-number">2</div>
          </div>
          <div class="hiw-step-body">
            <div class="hiw-step-icon">
              <i class="icon-file-plus"></i>
            </div>
            <h3>Submit Request</h3>
            <p>Choose the document you need — Barangay Clearance, Certificate of Residency, Indigency, Business Clearance, or Barangay ID — and fill out the online form.</p>
            <div class="hiw-step-tag">Step 2</div>
          </div>
        </div>

        <div class="hiw-step">
          <div class="hiw-node">
            <div class="hiw-node-ring"></div>
            <div class="hiw-step-number">3</div>
          </div>
          <div class="hiw-step-body">
            <div class="hiw-step-icon">
              <i class="icon-scan-search"></i>
            </div>
            <h3>Track in Real-Time</h3>
            <p>Monitor the status of your request through your dashboard. Get notified when your document is being processed, approved, and ready for pickup.</p>
            <div class="hiw-step-tag">Step 3</div>
          </div>
        </div>

        <div class="hiw-step">
          <div class="hiw-node hiw-node--final">
            <div class="hiw-node-ring"></div>
            <div class="hiw-step-number">✓</div>
          </div>
          <div class="hiw-step-body">
            <div class="hiw-step-icon hiw-step-icon--final">
              <i class="icon-file-check-2"></i>
            </div>
            <h3>Claim Document</h3>
            <p>Visit the Barangay Hall to claim your completed document. Your records are securely stored in our digital system for future reference.</p>
            <div class="hiw-step-tag hiw-step-tag--final">Complete!</div>
          </div>
        </div>

      </div>

      <!-- Key Features -->
      <div class="features-grid fade-in-up">
        <div class="feature-card">
          <div class="feature-icon blue">
            <i class="icon-monitor-smartphone"></i>
          </div>
          <h3>Access Anywhere</h3>
          <p>Submit document requests from your phone, tablet, or computer — 24/7, no visit required.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon green">
            <i class="icon-activity"></i>
          </div>
          <h3>Real-Time Tracking</h3>
          <p>Know exactly where your request stands — Pending, Processing, Approved, or Ready for Pickup.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon gold">
            <i class="icon-shield-check"></i>
          </div>
          <h3>Secure & Private</h3>
          <p>Your personal data is protected under the Data Privacy Act (RA 10173) with encrypted storage.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon rose">
            <i class="icon-clock"></i>
          </div>
          <h3>Faster Processing</h3>
          <p>Reduced wait times and eliminated redundancies. No more lost paperwork or delayed requests.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================
       DOCUMENTS / SERVICES
       ============================ -->
  <section class="services-section" id="services">
    <div class="container">
      <div class="services-header fade-in-up">
        <div>
          <div class="section-label">Available Documents</div>
          <h2 class="section-title">Request Barangay Documents Online</h2>
          <p class="section-subtitle">Select the document you need and submit your request digitally.</p>
        </div>
        <div class="services-search" id="servicesSearch">
          <i class="icon-search"></i>
          <input type="text" placeholder="Search documents (e.g., clearance, ID...)" id="serviceSearchInput">
        </div>
      </div>

      <div class="services-grid fade-in-up" id="servicesGrid">
        @php
            $portalDocs = collect();
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('document_types')) {
                    $portalDocs = \App\Models\DocumentType::active()->orderBy('name')->get();
                }
            } catch (\Throwable $e) {
                $portalDocs = collect();
            }
        @endphp
        @forelse($portalDocs as $pDoc)
            @php
                $nameLower = strtolower($pDoc->name ?? '');
                if (str_contains($nameLower, 'clearance') && str_contains($nameLower, 'business')) {
                    $lucideIcon = 'icon-store';
                    $iconStyle = 'background: #CCFBF1; color: var(--color-accent-teal);';
                } elseif (str_contains($nameLower, 'clearance')) {
                    $lucideIcon = 'icon-file-badge';
                    $iconStyle = 'background: var(--color-primary-50); color: var(--color-primary);';
                } elseif (str_contains($nameLower, 'residency') || str_contains($nameLower, 'resident')) {
                    $lucideIcon = 'icon-home';
                    $iconStyle = 'background: #EDE9FE; color: #7C3AED;';
                } elseif (str_contains($nameLower, 'indigency') || str_contains($nameLower, 'assistance') || str_contains($nameLower, 'medical')) {
                    $lucideIcon = 'icon-heart-handshake';
                    $iconStyle = 'background: #FEF3C7; color: var(--color-secondary);';
                } elseif (str_contains($nameLower, 'business') || str_contains($nameLower, 'permit')) {
                    $lucideIcon = 'icon-store';
                    $iconStyle = 'background: #CCFBF1; color: var(--color-accent-teal);';
                } elseif (str_contains($nameLower, 'id') || str_contains($nameLower, 'card')) {
                    $lucideIcon = 'icon-id-card';
                    $iconStyle = 'background: #FFE4E6; color: var(--color-accent-rose);';
                } else {
                    $lucideIcon = 'icon-file-text';
                    $iconStyle = 'background: #E0F2FE; color: #0284C7;';
                }

                $pReqs = !empty($pDoc->requirements_list) ? implode(', ', $pDoc->requirements_list) : 'Valid ID, Proof of Residency';
                $keywords = strtolower($pDoc->name . ' ' . ($pDoc->description ?? '') . ' ' . $pReqs);
            @endphp
            <div class="service-card" data-keywords="{{ $keywords }}">
              <div class="service-card-icon" style="{{ $iconStyle }}">
                <i class="{{ $lucideIcon }}"></i>
              </div>
              <h3>{{ $pDoc->name }}</h3>
              <p>{{ $pDoc->description ?: 'Official certification issued by the barangay for administrative and legal requirements.' }}</p>
              <div class="service-card-meta">
                <span class="meta-tag"><i class="icon-clock"></i> {{ $pDoc->processing_days ?: '1–2 days processing' }}</span>
                <span class="meta-tag"><i class="icon-philippine-peso"></i> {{ $pDoc->formatted_fee }}</span>
              </div>
              <div class="service-card-footer">
                <div class="service-card-requirements">
                  <strong>Requirements:</strong> {{ $pReqs }}
                </div>
                <a href="#" class="btn btn-primary btn-sm request-doc-btn" data-doc="{{ $pDoc->name }}">
                  <i class="icon-file-plus"></i> Request Now
                </a>
              </div>
            </div>
        @empty
            <div class="col-12 text-center py-4 text-muted">
                <p>No document services currently available.</p>
            </div>
        @endforelse
      </div>

      <div class="services-no-results" id="servicesNoResults">
        <i class="icon-search-x"></i>
        <h3>No documents found</h3>
        <p>Try a different search term or browse all available documents above.</p>
      </div>
    </div>
  </section>

  <!-- ============================
       DOCUMENT REQUEST MODAL
       ============================ -->
  <div class="modal-overlay" id="requestModal">
    <div class="modal">
      <div class="modal-header">
        <div class="modal-header-icon">
          <i class="icon-file-plus"></i>
        </div>
        <div>
          <h3 id="modalTitle">Request Document</h3>
          <p id="modalSubtitle">Fill out the form below to submit your request</p>
        </div>
        <button class="modal-close" id="modalClose" aria-label="Close modal">
          <i class="icon-x"></i>
        </button>
      </div>
      <div class="modal-body">
        <form id="requestForm">
          <div class="form-group">
            <label for="reqFullName"><i class="icon-user"></i> Full Name</label>
            <input type="text" id="reqFullName" placeholder="e.g., Juan Dela Cruz" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="reqAge"><i class="icon-calendar"></i> Age</label>
              <input type="number" id="reqAge" placeholder="e.g., 25" min="18" required>
            </div>
            <div class="form-group">
              <label for="reqSex"><i class="icon-users"></i> Sex</label>
              <select id="reqSex" required>
                <option value="">Select...</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="reqAddress"><i class="icon-map-pin"></i> Address in Cogon East</label>
            <input type="text" id="reqAddress" placeholder="e.g., Purok 3, Cogon East, Carmen, Cebu" required>
          </div>
          <div class="form-group">
            <label for="reqContact"><i class="icon-phone"></i> Contact Number</label>
            <input type="tel" id="reqContact" placeholder="e.g., 09171234567" required>
          </div>
          <div class="form-group">
            <label for="reqPurpose"><i class="icon-clipboard-list"></i> Purpose of Request</label>
            <textarea id="reqPurpose" rows="3" placeholder="e.g., Employment requirement, School enrollment..." required></textarea>
          </div>
          <div class="form-group form-consent">
            <label class="consent-label">
              <input type="checkbox" id="reqConsent" required>
              <span class="consent-box"></span>
              <span class="consent-text">
                I consent to the collection and processing of my personal data in accordance with
                <strong>Republic Act 10173</strong> (Data Privacy Act of 2012). My information will
                be used solely for processing this barangay document request.
              </span>
            </label>
          </div>
          <div class="form-actions">
            <button type="button" class="btn btn-secondary" id="cancelRequest">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="icon-send"></i> Submit Request
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <!-- ============================
       NEWS & ANNOUNCEMENTS
       ============================ -->
  <section class="news-section" id="news">
    <div class="container">
      <div class="fade-in-up">
        <div class="section-label">Latest Updates</div>
        <h2 class="section-title">Barangay News & Announcements</h2>
      </div>

      <div class="news-grid fade-in-up">
        <div class="news-card">
          <div class="news-card-img">
            <i class="icon-laptop"></i>
          </div>
          <div class="news-card-body">
            <div class="news-card-date">
              <i class="icon-calendar"></i>
              June 10, 2026
            </div>
            <h3>E-Barangay System Now Live!</h3>
            <p>The new web-based document request and tracking system is now available for all residents of Barangay Cogon East. Register now to skip the lines!</p>
          </div>
        </div>

        <div class="news-card">
          <div class="news-card-img" style="background: var(--gradient-gold);">
            <i class="icon-megaphone"></i>
          </div>
          <div class="news-card-body">
            <div class="news-card-date">
              <i class="icon-calendar"></i>
              June 5, 2026
            </div>
            <h3>Free Barangay Clearance Day — June 20</h3>
            <p>In celebration of the E-Barangay launch, barangay clearance processing will be FREE for all residents on June 20, 2026. Don't miss it!</p>
          </div>
        </div>

        <div class="news-card">
          <div class="news-card-img" style="background: linear-gradient(135deg, #0D9488, #14B8A6);">
            <i class="icon-calendar-check"></i>
          </div>
          <div class="news-card-body">
            <div class="news-card-date">
              <i class="icon-calendar"></i>
              June 1, 2026
            </div>
            <h3>Community Assembly — June 15 at 2PM</h3>
            <p>All residents are invited to the quarterly community assembly at the Barangay Hall covered court. Discussion on the digital transformation initiative.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================
       COMMUNITY CONCERNS
       ============================ -->
  <section class="concerns-section" id="concerns">
    <div class="container">
      <div class="concerns-content fade-in-up">
        <div class="concerns-text">
          <div class="section-label">Community Feature</div>
          <h2 class="section-title">Submit a Community Concern</h2>
          <p class="section-subtitle">
            Have an issue or concern in your purok? Report it online and barangay officials 
            will monitor and address it. Together, we build a better Cogon East.
          </p>
          <div class="concern-types">
            <div class="concern-type">
              <i class="icon-construction"></i>
              <span>Infrastructure</span>
            </div>
            <div class="concern-type">
              <i class="icon-shield-alert"></i>
              <span>Peace & Order</span>
            </div>
            <div class="concern-type">
              <i class="icon-leaf"></i>
              <span>Sanitation</span>
            </div>
            <div class="concern-type">
              <i class="icon-lightbulb"></i>
              <span>Utilities</span>
            </div>
          </div>
        </div>
        <div class="concerns-form-card">
          @php
            $authUser = auth()->user();
            $isResident = $authUser && trim(strtolower($authUser->role)) !== 'admin';
            $categories = \App\Models\CommunityConcern::categories();
            $guestLocked = !$isResident;
          @endphp

          @if($errors->any())
            <div class="concern-form-alert">
              <strong>Please fix the following:</strong>
              <ul>
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @include('partials.community-concern-form', [
              'formId' => 'concernForm',
              'context' => 'welcome',
              'isResident' => $isResident,
              'guestLocked' => $guestLocked,
              'authUser' => $authUser,
              'categories' => $categories,
              'showResidentInfo' => false,
          ])
        </div>
      </div>
    </div>
  </section>

  <!-- ============================
       EMERGENCY CONTACTS
       ============================ -->
  <section class="emergency-section" id="emergency">
    <div class="container">
      <div class="emergency-header fade-in-up">
        <div class="section-label" style="color: #FCD34D; justify-content: center;">Emergency Hotlines</div>
        <h2 class="section-title">Need Immediate Help?</h2>
        <p class="section-subtitle">These lines are available 24/7 for emergency assistance in Carmen, Cebu.</p>
      </div>

      <div class="emergency-grid fade-in-up">
        <div class="emergency-card">
          <div class="emergency-card-icon red">
            <i class="icon-siren"></i>
          </div>
          <h3>Barangay Cogon East</h3>
          <div class="emergency-card-number">(032) 555-0100</div>
        </div>
        <div class="emergency-card">
          <div class="emergency-card-icon blue">
            <i class="icon-badge"></i>
          </div>
          <h3>Carmen PNP Station</h3>
          <div class="emergency-card-number">911</div>
        </div>
        <div class="emergency-card">
          <div class="emergency-card-icon orange">
            <i class="icon-flame"></i>
          </div>
          <h3>Bureau of Fire (BFP)</h3>
          <div class="emergency-card-number">(032) 555-0119</div>
        </div>
        <div class="emergency-card">
          <div class="emergency-card-icon green">
            <i class="icon-ambulance"></i>
          </div>
          <h3>Carmen Rural Health Unit</h3>
          <div class="emergency-card-number">161 / Red Cross</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================
       FOOTER
       ============================ -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-about">
          <a href="#" class="logo">
            <div class="logo-icon">
              <!-- Image path updated for Laravel -->
              <img src="{{ asset('images/logo.png') }}" alt="E-Barangay Logo">
            </div>
            <div class="logo-text">
              E-Barangay
              <span>Cogon East • Carmen, Cebu</span>
            </div>
          </a>
          <p>
            E-Barangay is a web-based document request and tracking system 
            designed to modernize barangay services in Cogon East, Carmen, Cebu. 
            Developed as a thesis project by BSIT students of Cebu Technological University — Danao Campus.
          </p>
        </div>

        <div class="footer-col">
          <h4>Documents</h4>
          <ul>
            <li><a href="#services">Barangay Clearance</a></li>
            <li><a href="#services">Certificate of Residency</a></li>
            <li><a href="#services">Certificate of Indigency</a></li>
            <li><a href="#services">Business Clearance</a></li>
            <li><a href="#services">Barangay ID</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="#how-it-works">How It Works</a></li>
            @if($isLoggedInResident)
              <li><a href="{{ route('resident.dashboard') }}">My Dashboard</a></li>
            @else
              <li><a href="{{ route('resident.login') }}">Log In</a></li>
            @endif
            <li><a href="#concerns">Submit Concern</a></li>
            <li><a href="#news">News & Updates</a></li>
            <li><a href="#emergency">Emergency</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Contact Us</h4>
          <ul>
            <li><a href="#"><i class="icon-map-pin"></i> Cogon East, Carmen, Cebu</a></li>
            <li><a href="#"><i class="icon-phone"></i> (032) 555-0100</a></li>
            <li><a href="#"><i class="icon-mail"></i> ebarangay.cogoneast@gmail.com</a></li>
            <li><a href="#"><i class="icon-clock"></i> Mon–Fri, 8AM–5PM</a></li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; 2026 E-Barangay — Barangay Cogon East, Carmen, Cebu. All rights reserved.</p>
        <p class="footer-thesis">
          A thesis by Adolfo, Arong, Castro, Nuñez, Sios-e & Suson — BSIT, CTU Danao Campus
        </p>
      </div>
    </div>
  </footer>

  <!-- ============================
       ISTONG AI CHAT WIDGET
       ============================ -->
  <div class="chat-widget" id="chatWidget">
    <div class="chat-window" id="chatWindow">
      <div class="chat-header">
        <div class="chat-avatar istong-avatar">
          <span class="avatar-emoji">🎣</span>
          <span class="avatar-ai-badge">AI</span>
        </div>
        <div class="chat-header-info">
          <div class="chat-title-row">
            <h4>Istong AI</h4>
            <span class="chat-badge-tag">Digital Assistant</span>
          </div>
          <p><span class="chat-status-dot"></span>Online &bull; Cogon East Assistant</p>
        </div>
        <div class="chat-header-actions">
          <button type="button" class="chat-header-btn" id="chatResetBtn" title="Bag-ong istorya / Reset chat" aria-label="Reset chat">
            <i class="icon-rotate-ccw"></i>
          </button>
          <button type="button" class="chat-header-btn" id="chatCloseBtn" title="Close" aria-label="Close chat">
            <i class="icon-x"></i>
          </button>
        </div>
      </div>

      <div class="chat-body" id="chatBody">
        <div class="chat-msg bot">
          Maayong adlaw, silingan! 👋 Ako diay si <strong>Istong</strong>, ang opisyal nga digital assistant sa Barangay Cogon East.
          <br><br>
          Unsay ako ikatabang nimo karon? Pwede ka mangutana bahin sa:
          <br>&bull; Pamaagi sa pag-request og dokumento
          <br>&bull; Requirements ug mga bayronon (fees)
          <br>&bull; Pag-track sa status sa imong dokumento
          <br>&bull; Oras sa opisina ug serbisyo sa barangay
          <br><br>
          <em>Pangutana lang bisan Bisaya o English!</em>
        </div>
      </div>

      <!-- Quick Suggested Prompts -->
      <div class="chat-prompts">
        <div class="chat-prompts-label"><i class="icon-sparkles"></i> Paspas nga Pangutana:</div>
        <div class="chat-prompt-btns" id="chatPromptBtns">
          <button type="button" class="chat-prompt-btn" data-query="Unsaon pag-request og Barangay Clearance?">📄 Unsaon pag-request?</button>
          <button type="button" class="chat-prompt-btn" data-query="Giunsa nako pag-track ang akong gipasa nga document request?">🔍 Unsaon pag-track?</button>
          <button type="button" class="chat-prompt-btn" data-query="Unsay requirements para sa Certificate of Residency?">📋 Requirements sa Residency</button>
          <button type="button" class="chat-prompt-btn" data-query="Unsa ang schedule ug office hours sa Barangay Hall?">🕐 Office hours</button>
          <button type="button" class="chat-prompt-btn" data-query="Unsaon pagsumite og community concern o reklamo?">📢 Pag-submit og concern</button>
        </div>
      </div>

      <!-- Interactive Message Input -->
      <form class="chat-footer" id="chatForm">
        <input 
          type="text" 
          id="chatInput" 
          placeholder="Pangutana kang Istong diri..." 
          autocomplete="off" 
          maxlength="500" 
          aria-label="Ask Istong AI"
        >
        <button type="submit" id="chatSendBtn" class="chat-send-btn" aria-label="Ipadala" title="Send message">
          <i class="icon-send"></i>
        </button>
      </form>
    </div>

    <!-- Floating Toggle Button with Istong Pill -->
    <div class="chat-toggle-wrap">
      <div class="chat-pill" id="chatPill">
        <span class="chat-pill-sparkle">✨</span>
        <span>Pangutana kang <strong>Istong AI</strong></span>
      </div>
      <button class="chat-toggle" id="chatToggle" aria-label="Open Istong AI Assistant" title="Open Istong AI">
        <span class="chat-icon-avatar">🎣</span>
        <i class="icon-message-circle chat-icon-open"></i>
        <i class="icon-x chat-icon-close"></i>
      </button>
    </div>
  </div>

  <!-- Login Required Modal -->
  <div class="modal-overlay" id="authRequiredModal">
    <div class="modal">
      <div class="modal-header">
        <div class="modal-header-icon">
          <i class="icon-lock"></i>
        </div>
        <div>
          <h3 id="authRequiredTitle">Login Required</h3>
          <p id="authRequiredMessage">Please log in to continue.</p>
        </div>
        <button class="modal-close" id="authRequiredClose" aria-label="Close modal">
          <i class="icon-x"></i>
        </button>
      </div>
      <div class="modal-body">
        <p id="authRequiredDescription" class="text-muted" style="margin: 0; line-height: 1.6;">
          You need to log in first before continuing.
        </p>
      </div>
      <div class="form-actions">
        <button type="button" class="btn btn-secondary" id="authRequiredCancel">Cancel</button>
        <button type="button" class="btn btn-primary" id="authRequiredLogin">
          <i class="icon-log-in"></i> Login
        </button>
      </div>
    </div>
  </div>

  <!-- Resident Login Modal -->
  <div class="modal-overlay" id="residentLoginModal">
    <div class="modal">
      <div class="modal-header">
        <div class="modal-header-icon">
          <i class="icon-user"></i>
        </div>
        <div>
          <h3>Welcome Back</h3>
          <p>Log in to your resident portal</p>
        </div>
        <button class="modal-close" id="residentLoginClose" aria-label="Close modal">
          <i class="icon-x"></i>
        </button>
      </div>
      <div class="modal-body">
        @include('partials.resident-login-form', [
            'formId' => 'welcomeResidentLoginForm',
            'ajaxMode' => true,
            'errorContainerId' => 'welcomeLoginError',
        ])
      </div>
    </div>
  </div>

  <!-- Success Toast -->
  <div class="toast" id="toast">
    <div class="toast-icon"><i class="icon-check-circle"></i></div>
    <div class="toast-content">
      <h4 id="toastTitle">Success!</h4>
      <p id="toastMessage">{{ session('success', 'Your request has been submitted successfully.') }}</p>
    </div>
    <button class="toast-close" id="toastClose"><i class="icon-x"></i></button>
  </div>

  <!-- JavaScript (Updated for Laravel) -->
  <script src="{{ asset('js/app.js') }}"></script>
  @if(session('success'))
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toast = document.getElementById('toast');
      const toastTitle = document.getElementById('toastTitle');
      const toastMessage = document.getElementById('toastMessage');
      if (toast && toastTitle && toastMessage) {
        toastTitle.textContent = 'Success!';
        toast.classList.add('visible');
        setTimeout(() => toast.classList.remove('visible'), 5000);
      }
    });
  </script>
  @endif
</body>
</html>