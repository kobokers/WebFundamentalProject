<?php
session_start();
include("header.php");
?>

<body>
    <style>
        :root {
            --primary: #0056D2;
            --primary-soft: #eaf2ff;
            --dark-grey: #2d3436;
            --dark: #1f2937;
            --muted: #6b7280;
            --bg: #f5f8fc;
            --card: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: var(--dark);
            background: #fff;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* HERO SECTION */
        .hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(120deg, var(--dark-grey) 0%, #3d4446 10%, var(--primary) 40%);
            color: #fff;
        }

        .hero-inner {
            max-width: 1200px;
            margin: auto;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 40px;
            padding: 100px 20px 140px 20px;
            align-items: center;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .hero p {
            max-width: 500px;
            color: #eef4ff;
            margin-bottom: 20px;
        }

        .hero .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 16px;
            border-radius: 999px;
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        /* ADVANCED TRANSPARENT SLIDER */
        .slider-container {
            position: relative;
            height: 300px;
            perspective: 1000px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .slider-track {
            position: relative;
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
        }

        .slide {
            position: absolute;
            width: 100%;
            height: 250px;
            background: #fff;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        /* Animation Slider */
        .slide:nth-child(1) {
            animation: slideMove1 12s infinite;
        }

        .slide:nth-child(2) {
            animation: slideMove2 12s infinite;
        }

        .slide:nth-child(3) {
            animation: slideMove3 12s infinite;
        }

        @keyframes slideMove1 {

            0%,
            30% {
                transform: translateX(0) scale(1);
                opacity: 1;
                z-index: 3;
            }

            33%,
            63% {
                transform: translateX(-15%) scale(0.8);
                opacity: 0.4;
                z-index: 1;
            }

            66%,
            96% {
                transform: translateX(15%) scale(0.8);
                opacity: 0.4;
                z-index: 2;
            }
        }

        @keyframes slideMove2 {

            0%,
            30% {
                transform: translateX(15%) scale(0.8);
                opacity: 0.4;
                z-index: 2;
            }

            33%,
            63% {
                transform: translateX(0) scale(1);
                opacity: 1;
                z-index: 3;
            }

            66%,
            96% {
                transform: translateX(-15%) scale(0.8);
                opacity: 0.4;
                z-index: 1;
            }
        }

        @keyframes slideMove3 {

            0%,
            30% {
                transform: translateX(-15%) scale(0.8);
                opacity: 0.4;
                z-index: 1;
            }

            33%,
            63% {
                transform: translateX(15%) scale(0.8);
                opacity: 0.4;
                z-index: 2;
            }

            66%,
            96% {
                transform: translateX(0) scale(1);
                opacity: 1;
                z-index: 3;
            }
        }

        .hero svg {
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
        }

        /* STORY SECTION (INTERACTIVE CARDS) */
        .story {
            background: var(--bg);
            padding: 100px 20px;
        }

        .story-grid {
            max-width: 1200px;
            margin: auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .story .card {
            background: var(--card);
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            text-align: center;
            cursor: pointer;
            position: relative;
            transition: all 0.5s ease;
            overflow: hidden;
            min-height: 220px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        /* (H3) Animation */
        .story .card h3 {
            color: var(--primary);
            font-size: 1.8rem;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 2;
        }

        /* Explanation Animation */
        .story .card p {
            color: var(--muted);
            font-size: 1.05rem;
            opacity: 0;
            max-height: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
            margin-top: 0;
        }

        /* HOVER STATE */
        .story .card:hover {
            background: #ffffff;
            box-shadow: 0 20px 40px rgba(15, 118, 110, 0.15);
            padding-top: 30px;
        }

        .story .card:hover h3 {
            transform: translateY(-15px) scale(0.8);
            margin-bottom: 5px;
        }

        .story .card:hover p {
            opacity: 1;
            max-height: 200px;
            transform: translateY(0);
            margin-top: 10px;
        }

        /* STATS SECTION */
        .stats {
            padding: 80px 20px;
            background: #fff;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .s-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
        }

        .s {
            background: #ffffff;
            border-radius: 22px;
            padding: 40px 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .s h3 {
            font-size: 2.8rem;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .s p {
            color: var(--muted);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* RESPONSIVE */
        @media(max-width: 980px) {
            .hero-inner {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .story-grid {
                grid-template-columns: 1fr;
            }

            .s-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
    <header class="hero">
        <div class="hero-inner">
            <div class="hero-content">
                <span class="badge">ABOUT US</span>
                <h1>Building Reliable Digital</h1>
                <h1>& Learning Solutions</h1>
                <p>With OLMS, we teach, we support, and we empower organizations to modernize their systems while
                    empowering learners through scalable technology</p>
            </div>

            <div class="slider-container">
                <div class="slider-track">
                    <div class="slide">
                        <div style="text-align: center;">
                            <h2 style="color:var(--primary)">Many Lecturers</h2>
                            <p style="color:var(--muted)">Find Your Lecturers</p>
                        </div>
                    </div>

                    <div class="slide">
                        <div style="text-align: center;">
                            <h2 style="color:var(--primary)">Many Courses</h2>
                            <p style="color:var(--muted)">Find Your Courses</p>
                        </div>
                    </div>

                    <div class="slide">
                        <div style="text-align: center;">
                            <h2 style="color:var(--primary)">Flexible Schedules</h2>
                            <p style="color:var(--muted)">Create Your Own Schedules</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <svg viewBox="0 0 1440 60" preserveAspectRatio="none">
            <path fill="#f5f8fc" d="M0,32L1440,0L1440,60L0,60Z"></path>
        </svg>
    </header>

    <section class="story">
        <div class="story-grid">
            <div class="card">
                <h3>Who We Are?</h3>
                <p>OLMS is a technology-driven platform delivering IT and online learning solutions. We focus on
                    usability, performance, and long-term value for every client we serve.</p>
            </div>

            <div class="card">
                <h3>What We Do?</h3>
                <p>From system development to cloud-ready platforms, we design solutions that scale with your business.
                    We bridge the gap between technology and human potential.</p>
            </div>
        </div>
    </section>

    <section class="stats">
        <div class="s-grid">
            <div class="s">
                <h3>5+</h3>
                <p>Years Experience</p>
            </div>
            <div class="s">
                <h3>2k</h3>
                <p>Projects Done</p>
            </div>
            <div class="s">
                <h3>300+</h3>
                <p>Experts Students</p>
            </div>
            <div class="s">
                <h3>1.2k</h3>
                <p>Excellent Ratings</p>
            </div>
        </div>
    </section>

    <?php
    include("footer.php"); ?>