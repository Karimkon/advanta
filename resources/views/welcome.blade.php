<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADVANTA – Project Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(-45deg, #0f172a, #1e293b, #0f2a1f, #1a0f2a);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
        }
        
        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .fade-in-up {
            opacity: 0;
            transform: translateY(40px);
            animation: fadeInUp 1s ease forwards;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .role-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .role-card:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        
        .logo-container {
            transition: all 0.3s ease;
        }
        
        .logo-container:hover {
            transform: scale(1.02);
        }
    </style>
</head>

<body class="gradient-bg text-white min-h-screen flex items-center justify-center">

<div class="max-w-6xl w-full mx-auto text-center p-8">
    <!-- Logo Section - Choose one option below -->

    <!-- Option 1: Rectangular logo with white background -->
    <div class="fade-in-up mb-8 logo-container">
        <div class="bg-white p-4 rounded-2xl inline-block mb-6 shadow-2xl">
            <img src="{{ asset('images/advanta.jpg') }}" 
                 alt="ADVANTA Logo" 
                 class="h-16 w-auto mx-auto">
        </div>
        <h1 class="text-4xl font-extrabold mb-4 tracking-tight">
            ADVANTA Uganda Limited – Project Management System
        </h1>
        <p class="text-lg text-gray-300 mb-10 max-w-3xl mx-auto">
            Streamline projects, requisitions, LPOs, procurement, approvals, and reporting — all in one platform.
        </p>
    </div>

   

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
        <a href="/admin/login" class="role-card group p-6 rounded-2xl text-center">
            <div class="text-3xl mb-3 group-hover:scale-110 transition-transform duration-300">👑</div>
            <h4 class="text-lg font-bold mb-2 text-purple-300">Admin</h4>
            <p class="text-sm text-gray-300">Full system access</p>
        </a>
        
        <a href="/operations/login" class="role-card group p-6 rounded-2xl text-center">
            <div class="text-3xl mb-3 group-hover:scale-110 transition-transform duration-300">⚙️</div>
            <h4 class="text-lg font-bold mb-2 text-green-300">Operations</h4>
            <p class="text-sm text-gray-300">Workflow management</p>
        </a>
        
        <a href="/procurement/login" class="role-card group p-6 rounded-2xl text-center">
            <div class="text-3xl mb-3 group-hover:scale-110 transition-transform duration-300">📦</div>
            <h4 class="text-lg font-bold mb-2 text-orange-300">Procurement</h4>
            <p class="text-sm text-gray-300">Supplier & purchasing</p>
        </a>
        
        <a href="/finance/login" class="role-card group p-6 rounded-2xl text-center">
            <div class="text-3xl mb-3 group-hover:scale-110 transition-transform duration-300">💰</div>
            <h4 class="text-lg font-bold mb-2 text-blue-300">Finance</h4>
            <p class="text-sm text-gray-300">Payments & reporting</p>
        </a>
        
        <a href="/stores/login" class="role-card group p-6 rounded-2xl text-center">
            <div class="text-3xl mb-3 group-hover:scale-110 transition-transform duration-300">🏪</div>
            <h4 class="text-lg font-bold mb-2 text-indigo-300">Stores</h4>
            <p class="text-sm text-gray-300">Inventory management</p>
        </a>
        
        <a href="/ceo/login" class="role-card group p-6 rounded-2xl text-center">
            <div class="text-3xl mb-3 group-hover:scale-110 transition-transform duration-300">📊</div>
            <h4 class="text-lg font-bold mb-2 text-pink-300">CEO Portal</h4>
            <p class="text-sm text-gray-300">Executive overview</p>
        </a>
        
        <a href="/project/login" class="role-card group p-6 rounded-2xl text-center">
            <div class="text-3xl mb-3 group-hover:scale-110 transition-transform duration-300">📋</div>
            <h4 class="text-lg font-bold mb-2 text-cyan-300">Project Manager</h4>
            <p class="text-sm text-gray-300">Project oversight</p>
        </a>
        
        <a href="/engineer/login" class="role-card group p-6 rounded-2xl text-center">
            <div class="text-3xl mb-3 group-hover:scale-110 transition-transform duration-300">🏗️</div>
            <h4 class="text-lg font-bold mb-2 text-yellow-300">Engineer</h4>
            <p class="text-sm text-gray-300">Site operations & requisitions</p>
        </a>
        
        <a href="/manual" class="role-card group p-6 rounded-2xl text-center">
            <div class="text-3xl mb-3 group-hover:scale-110 transition-transform duration-300">📚</div>
            <h4 class="text-lg font-bold mb-2 text-red-300">Read Manual</h4>
            <p class="text-sm text-gray-300">System documentation</p>
        </a>
    </div>

    <div class="fade-in-up mt-12 text-center">
        <div class="text-sm text-gray-400">
            © {{ date('Y') }} Advanta Uganda Ltd. All rights reserved.
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add staggered animation to cards
        const cards = document.querySelectorAll('.role-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('fade-in-up');
        });
    });
</script>

</body>
</html>