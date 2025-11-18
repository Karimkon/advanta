<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADVANTA ERP System - Complete User Manual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.7;
        }
        
        .manual-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            font-weight: 600;
            margin-right: 1rem;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .feature-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .nav-sidebar {
            position: sticky;
            top: 2rem;
            max-height: calc(100vh - 4rem);
            overflow-y: auto;
        }
        
        .nav-link {
            display: block;
            padding: 0.75rem 1rem;
            color: #4a5568;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background: #f7fafc;
            border-left-color: #667eea;
            color: #667eea;
        }
        
        .workflow-diagram {
            background: #f8fafc;
            border-radius: 8px;
            padding: 2rem;
            margin: 2rem 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .keyboard-shortcut {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            background: #edf2f7;
            border: 1px solid #cbd5e0;
            border-radius: 4px;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 0.75rem;
            margin: 0 0.25rem;
        }
        
        .troubleshooting-item {
            border-left: 4px solid #e53e3e;
            background: #fef5f5;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0 4px 4px 0;
        }
        
        .quick-tip {
            background: #ebf8ff;
            border-left: 4px solid #4299e1;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 0 4px 4px 0;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            .section-card {
                box-shadow: none;
                border: 1px solid #e2e8f0;
            }
            
            .feature-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b no-print">
        <div class="manual-container">
            <div class="flex items-center justify-between py-4 px-6">
                <div class="flex items-center space-x-4">
                    <a href="/" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to Login</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="window.print()" class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="bi bi-printer"></i>
                        <span>Print Manual</span>
                    </button>
                    <button onclick="toggleDarkMode()" class="flex items-center space-x-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        <i class="bi bi-moon"></i>
                        <span>Dark Mode</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="manual-container">
        <div class="flex flex-col lg:flex-row gap-8 py-8">
            <!-- Navigation Sidebar -->
            <aside class="lg:w-1/4 no-print">
                <div class="nav-sidebar bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-bold text-lg mb-4 text-gray-800">Manual Contents</h3>
                    <nav class="space-y-1">
                        <a href="#introduction" class="nav-link active">Introduction & Overview</a>
                        <a href="#system-requirements" class="nav-link">System Requirements</a>
                        <a href="#getting-started" class="nav-link">Getting Started</a>
                        
                        <div class="mt-6">
                            <h4 class="font-semibold text-sm uppercase text-gray-500 mb-2">User Role Manuals</h4>
                            <a href="#admin-guide" class="nav-link">👑 Administrator</a>
                            <a href="#ceo-guide" class="nav-link">📊 CEO Executive</a>
                            <a href="#project-manager-guide" class="nav-link">📋 Project Manager</a>
                            <a href="#engineer-guide" class="nav-link">🏗️ Engineer</a>
                            <a href="#surveyor-guide" class="nav-link">📐 Surveyor</a>
                            <a href="#operations-guide" class="nav-link">⚙️ Operations</a>
                            <a href="#procurement-guide" class="nav-link">📦 Procurement</a>
                            <a href="#finance-guide" class="nav-link">💰 Finance</a>
                            <a href="#stores-guide" class="nav-link">🏪 Stores</a>
                            <a href="#supplier-guide" class="nav-link">🚚 Supplier</a>
                        </div>
                        
                        <div class="mt-6">
                            <h4 class="font-semibold text-sm uppercase text-gray-500 mb-2">Core Processes</h4>
                            <a href="#requisition-workflow" class="nav-link">Requisition Workflow</a>
                            <a href="#lpo-process" class="nav-link">LPO Management</a>
                            <a href="#inventory-management" class="nav-link">Inventory System</a>
                            <a href="#milestone-tracking" class="nav-link">Milestone Tracking</a>
                            <a href="#reporting-analytics" class="nav-link">Reporting & Analytics</a>
                        </div>
                        
                        <div class="mt-6">
                            <h4 class="font-semibold text-sm uppercase text-gray-500 mb-2">Reference</h4>
                            <a href="#troubleshooting" class="nav-link">Troubleshooting</a>
                            <a href="#keyboard-shortcuts" class="nav-link">Keyboard Shortcuts</a>
                            <a href="#faq" class="nav-link">FAQ</a>
                            <a href="#support" class="nav-link">Support & Contact</a>
                        </div>
                    </nav>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="lg:w-3/4">
                <!-- Introduction Section -->
                <section id="introduction" class="section-card">
                    <div class="section-header">
                        <h1 class="text-3xl font-bold">ADVANTA ERP System</h1>
                        <p class="text-blue-100 mt-2">Complete User Manual & Documentation</p>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">Welcome to ADVANTA ERP</h2>
                            <p class="text-lg text-gray-600 mb-6">
                                This comprehensive manual covers all aspects of the ADVANTA Enterprise Resource Planning system designed specifically for construction and project management. The system streamlines requisitions, procurement, inventory management, and project tracking across your organization.
                            </p>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                                <h3 class="text-lg font-semibold text-blue-800 mb-2">🚀 Quick Start</h3>
                                <p class="text-blue-700">New users should begin with their specific role guide below. Each section provides step-by-step instructions for daily tasks.</p>
                            </div>
                            
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">System Architecture</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div class="border rounded-lg p-4">
                                    <h4 class="font-semibold mb-2">🏗️ Project Management</h4>
                                    <ul class="list-disc list-inside text-gray-600 space-y-1">
                                        <li>Project creation & team assignment</li>
                                        <li>Milestone tracking & progress monitoring</li>
                                        <li>Budget management & cost control</li>
                                        <li>Resource allocation</li>
                                    </ul>
                                </div>
                                <div class="border rounded-lg p-4">
                                    <h4 class="font-semibold mb-2">📋 Requisition System</h4>
                                    <ul class="list-disc list-inside text-gray-600 space-y-1">
                                        <li>Multi-level approval workflow</li>
                                        <li>Store vs Purchase requisitions</li>
                                        <li>Real-time status tracking</li>
                                        <li>Automated notifications</li>
                                    </ul>
                                </div>
                                <div class="border rounded-lg p-4">
                                    <h4 class="font-semibold mb-2">📦 Procurement</h4>
                                    <ul class="list-disc list-inside text-gray-600 space-y-1">
                                        <li>Supplier management</li>
                                        <li>LPO creation & issuance</li>
                                        <li>Delivery tracking</li>
                                        <li>Price comparison</li>
                                    </ul>
                                </div>
                                <div class="border rounded-lg p-4">
                                    <h4 class="font-semibold mb-2">💰 Finance & Reporting</h4>
                                    <ul class="list-disc list-inside text-gray-600 space-y-1">
                                        <li>Payment processing</li>
                                        <li>Expense tracking</li>
                                        <li>Financial reporting</li>
                                        <li>Budget vs Actual analysis</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- System Requirements -->
                <section id="system-requirements" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">System Requirements & Setup</h2>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Technical Requirements</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <div>
                                    <h4 class="font-semibold text-lg mb-3">💻 Client Requirements</h4>
                                    <ul class="list-disc list-inside text-gray-600 space-y-2">
                                        <li><strong>Browser:</strong> Chrome 90+, Firefox 88+, Safari 14+, Edge 90+</li>
                                        <li><strong>JavaScript:</strong> Must be enabled</li>
                                        <li><strong>Screen Resolution:</strong> 1280x720 minimum</li>
                                        <li><strong>Internet:</strong> Stable broadband connection</li>
                                        <li><strong>Cookies:</strong> Must be enabled for session management</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-lg mb-3">🛠️ Recommended Setup</h4>
                                    <ul class="list-disc list-inside text-gray-600 space-y-2">
                                        <li>Use Google Chrome for best performance</li>
                                        <li>Bookmark your role-specific login page</li>
                                        <li>Enable browser notifications</li>
                                        <li>Use PDF reader for document downloads</li>
                                        <li>Keep browser updated to latest version</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Security Best Practices</h3>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                                <h4 class="font-semibold text-yellow-800 mb-2">🔐 Security Guidelines</h4>
                                <ul class="list-disc list-inside text-yellow-700 space-y-1">
                                    <li>Never share your login credentials</li>
                                    <li>Use strong passwords (min. 8 characters with mix of letters, numbers, symbols)</li>
                                    <li>Log out after each session, especially on shared computers</li>
                                    <li>Clear browser cache regularly</li>
                                    <li>Report any suspicious activity immediately to IT</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Getting Started -->
                <section id="getting-started" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">Getting Started</h2>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">First Time Login</h3>
                            
                            <div class="space-y-6">
                                <div class="flex items-start">
                                    <div class="step-number">1</div>
                                    <div>
                                        <h4 class="font-semibold text-lg">Access the System</h4>
                                        <p class="text-gray-600">Navigate to the ADVANTA login portal and select your role-specific login page.</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">2</div>
                                    <div>
                                        <h4 class="font-semibold text-lg">Enter Credentials</h4>
                                        <p class="text-gray-600">Use the email and password provided by your administrator. First-time users may need to reset their password.</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">3</div>
                                    <div>
                                        <h4 class="font-semibold text-lg">Explore Dashboard</h4>
                                        <p class="text-gray-600">Familiarize yourself with your role-specific dashboard layout and navigation menu.</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">4</div>
                                    <div>
                                        <h4 class="font-semibold text-lg">Complete Profile</h4>
                                        <p class="text-gray-600">Update your profile information including contact details and notification preferences.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-tip mt-6">
                                <strong>💡 Pro Tip:</strong> Bookmark your role-specific login page for quick access. Each role has a customized interface and workflow.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Administrator Guide -->
                <section id="admin-guide" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">👑 Administrator Guide</h2>
                        <p class="text-blue-100">Complete system administration and user management</p>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Administrator Responsibilities</h3>
                            <p class="text-gray-600 mb-6">
                                Administrators have full system access and are responsible for user management, project setup, system configuration, and overall system maintenance.
                            </p>
                            
                            <div class="feature-grid">
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">👥</div>
                                    <h4 class="font-semibold text-lg mb-2">User Management</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Create and manage user accounts</li>
                                        <li>Assign roles and permissions</li>
                                        <li>Reset passwords and manage access</li>
                                        <li>Monitor user activity</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">🏗️</div>
                                    <h4 class="font-semibold text-lg mb-2">Project Setup</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Create new projects</li>
                                        <li>Assign project teams</li>
                                        <li>Set up project stores</li>
                                        <li>Configure project budgets</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">⚙️</div>
                                    <h4 class="font-semibold text-lg mb-2">System Configuration</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Manage system settings</li>
                                        <li>Configure approval workflows</li>
                                        <li>Set up notification templates</li>
                                        <li>Manage system backups</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Step-by-Step: Creating a New Project</h4>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="step-number">1</div>
                                    <div>
                                        <h5 class="font-semibold">Navigate to Projects</h5>
                                        <p class="text-gray-600">Go to Admin Dashboard → Projects → Create New Project</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">2</div>
                                    <div>
                                        <h5 class="font-semibold">Enter Project Details</h5>
                                        <p class="text-gray-600">Fill in project name, code, description, location, dates, and budget</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">3</div>
                                    <div>
                                        <h5 class="font-semibold">Assign Team Members</h5>
                                        <p class="text-gray-600">Select Project Manager, Store Manager, Engineers, and Surveyors</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">4</div>
                                    <div>
                                        <h5 class="font-semibold">Create Project Store</h5>
                                        <p class="text-gray-600">System automatically creates a dedicated store for the project</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">5</div>
                                    <div>
                                        <h5 class="font-semibold">Generate Milestones</h5>
                                        <p class="text-gray-600">System creates default construction milestones automatically</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-tip mt-6">
                                <strong>💡 Pro Tip:</strong> Use project templates for similar projects to save setup time. The system automatically creates default construction milestones based on project type.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- CEO Guide -->
                <section id="ceo-guide" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">📊 CEO Executive Guide</h2>
                        <p class="text-blue-100">Executive overview, approvals, and strategic decision-making</p>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">CEO Portal Features</h3>
                            <p class="text-gray-600 mb-6">
                                The CEO portal provides high-level oversight of all company operations, financial performance, project progress, and strategic decision-making tools.
                            </p>
                            
                            <div class="feature-grid">
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">✅</div>
                                    <h4 class="font-semibold text-lg mb-2">Approval Authority</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Final approval on major requisitions</li>
                                        <li>LPO approval and authorization</li>
                                        <li>Budget override capabilities</li>
                                        <li>Strategic project approvals</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">📈</div>
                                    <h4 class="font-semibold text-lg mb-2">Financial Overview</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Company-wide financial dashboard</li>
                                        <li>Budget vs actual expenditure</li>
                                        <li>Project profitability analysis</li>
                                        <li>Cash flow monitoring</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">🏗️</div>
                                    <h4 class="font-semibold text-lg mb-2">Project Portfolio</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>All projects status overview</li>
                                        <li>Milestone completion tracking</li>
                                        <li>Resource allocation views</li>
                                        <li>Project health indicators</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Approval Workflow for CEO</h4>
                            <div class="workflow-diagram">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="text-center">
                                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white mx-auto mb-2">1</div>
                                        <p class="text-sm font-medium">Requisition Created</p>
                                    </div>
                                    <div class="flex-1 h-1 bg-gray-300 mx-4"></div>
                                    <div class="text-center">
                                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white mx-auto mb-2">2</div>
                                        <p class="text-sm font-medium">PM & Ops Approved</p>
                                    </div>
                                    <div class="flex-1 h-1 bg-gray-300 mx-4"></div>
                                    <div class="text-center">
                                        <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center text-white mx-auto mb-2">3</div>
                                        <p class="text-sm font-medium">CEO Review</p>
                                    </div>
                                    <div class="flex-1 h-1 bg-gray-300 mx-4"></div>
                                    <div class="text-center">
                                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white mx-auto mb-2">4</div>
                                        <p class="text-sm font-medium">Final Approval</p>
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Step-by-Step: Approving a Requisition</h4>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="step-number">1</div>
                                    <div>
                                        <h5 class="font-semibold">Access Pending Requisitions</h5>
                                        <p class="text-gray-600">Go to CEO Dashboard → Pending Approval to see all requisitions awaiting your review</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">2</div>
                                    <div>
                                        <h5 class="font-semibold">Review Details</h5>
                                        <p class="text-gray-600">Click on any requisition to view full details, items, costs, and approval history</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">3</div>
                                    <div>
                                        <h5 class="font-semibold">Make Decision</h5>
                                        <p class="text-gray-600">Choose to Approve, Reject, or Request modifications with comments</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">4</div>
                                    <div>
                                        <h5 class="font-semibold">Set Approved Amount</h5>
                                        <p class="text-gray-600">You can adjust the approved amount if different from requested amount</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">5</div>
                                    <div>
                                        <h5 class="font-semibold">Confirm Action</h5>
                                        <p class="text-gray-600">System notifies all relevant parties and moves requisition to next stage</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-tip mt-6">
                                <strong>💡 Pro Tip:</strong> Use the "All Requisitions" view to monitor the entire requisition pipeline and identify bottlenecks in the approval process.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Project Manager Guide -->
                <section id="project-manager-guide" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">📋 Project Manager Guide</h2>
                        <p class="text-blue-100">Project oversight, team coordination, and resource management</p>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Project Manager Responsibilities</h3>
                            <p class="text-gray-600 mb-6">
                                Project Managers oversee specific construction projects, manage project teams, approve requisitions, monitor progress, and ensure projects stay on schedule and within budget.
                            </p>
                            
                            <div class="feature-grid">
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">👥</div>
                                    <h4 class="font-semibold text-lg mb-2">Team Management</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Coordinate with engineers and surveyors</li>
                                        <li>Assign tasks and responsibilities</li>
                                        <li>Monitor team performance</li>
                                        <li>Facilitate communication</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">✅</div>
                                    <h4 class="font-semibold text-lg mb-2">Requisition Approval</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>First-level approval for project requisitions</li>
                                        <li>Budget compliance checking</li>
                                        <li>Priority setting for requests</li>
                                        <li>Resource allocation decisions</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">📊</div>
                                    <h4 class="font-semibold text-lg mb-2">Progress Monitoring</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Track milestone completion</li>
                                        <li>Monitor project timeline</li>
                                        <li>Budget expenditure tracking</li>
                                        <li>Progress reporting to management</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Step-by-Step: Managing Project Requisitions</h4>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="step-number">1</div>
                                    <div>
                                        <h5 class="font-semibold">Review Pending Requisitions</h5>
                                        <p class="text-gray-600">Check your dashboard for new requisitions from engineers on your projects</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">2</div>
                                    <div>
                                        <h5 class="font-semibold">Evaluate Request</h5>
                                        <p class="text-gray-600">Review items, quantities, costs, and urgency against project budget and timeline</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">3</div>
                                    <div>
                                        <h5 class="font-semibold">Approve or Modify</h5>
                                        <p class="text-gray-600">Approve as-is, adjust quantities/prices, or reject with explanation</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">4</div>
                                    <div>
                                        <h5 class="font-semibold">Forward to Operations</h5>
                                        <p class="text-gray-600">Approved requisitions move to Operations for further processing</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">5</div>
                                    <div>
                                        <h5 class="font-semibold">Track Status</h5>
                                        <p class="text-gray-600">Monitor requisition progress through procurement and delivery stages</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-tip mt-6">
                                <strong>💡 Pro Tip:</strong> Use the project dashboard to get a quick overview of all active requisitions, their status, and any delays that might impact your project timeline.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Engineer Guide -->
                <section id="engineer-guide" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">🏗️ Engineer Guide</h2>
                        <p class="text-blue-100">Site operations, material requests, and technical oversight</p>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Engineer Responsibilities</h3>
                            <p class="text-gray-600 mb-6">
                                Engineers manage day-to-day site operations, create material requisitions, monitor construction quality, and provide technical guidance for project execution.
                            </p>
                            
                            <div class="feature-grid">
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">📝</div>
                                    <h4 class="font-semibold text-lg mb-2">Requisition Creation</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Create material and equipment requests</li>
                                        <li>Specify technical requirements</li>
                                        <li>Set urgency levels</li>
                                        <li>Provide detailed specifications</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">👁️</div>
                                    <h4 class="font-semibold text-lg mb-2">Site Supervision</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Monitor construction quality</li>
                                        <li>Supervise subcontractors</li>
                                        <li>Ensure safety compliance</li>
                                        <li>Document site progress</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">📋</div>
                                    <h4 class="font-semibold text-lg mb-2">Technical Input</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Provide technical specifications</li>
                                        <li>Review material quality</li>
                                        <li>Approval of delivered items</li>
                                        <li>Quality control documentation</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Step-by-Step: Creating a Requisition</h4>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="step-number">1</div>
                                    <div>
                                        <h5 class="font-semibold">Access Requisition Module</h5>
                                        <p class="text-gray-600">Go to Engineer Dashboard → Requisitions → Create New</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">2</div>
                                    <div>
                                        <h5 class="font-semibold">Select Project & Type</h5>
                                        <p class="text-gray-600">Choose your project and select between Store Requisition or Purchase Requisition</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">3</div>
                                    <div>
                                        <h5 class="font-semibold">Add Items</h5>
                                        <p class="text-gray-600">Specify items, quantities, units, and provide technical specifications</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">4</div>
                                    <div>
                                        <h5 class="font-semibold">Set Urgency & Reason</h5>
                                        <p class="text-gray-600">Define urgency level and provide detailed reason for the requisition</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">5</div>
                                    <div>
                                        <h5 class="font-semibold">Submit for Approval</h5>
                                        <p class="text-gray-600">Submit to Project Manager for first-level approval</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-tip mt-6">
                                <strong>💡 Pro Tip:</strong> Use the "Pending" view to track your requisitions through the approval process. Provide detailed technical specifications to avoid delays in procurement.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Surveyor Guide -->
                <section id="surveyor-guide" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">📐 Surveyor Guide</h2>
                        <p class="text-blue-100">Construction milestone tracking and progress documentation</p>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Surveyor Responsibilities</h3>
                            <p class="text-gray-600 mb-6">
                                Surveyors track construction progress, update milestone completion, document site conditions, and provide accurate progress reporting for management decision-making.
                            </p>
                            
                            <div class="feature-grid">
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">🎯</div>
                                    <h4 class="font-semibold text-lg mb-2">Milestone Tracking</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Monitor construction milestones</li>
                                        <li>Update completion percentages</li>
                                        <li>Document progress with notes</li>
                                        <li>Track against project timeline</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">📊</div>
                                    <h4 class="font-semibold text-lg mb-2">Progress Reporting</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Generate progress reports</li>
                                        <li>Document challenges and solutions</li>
                                        <li>Provide photographic evidence</li>
                                        <li>Update management regularly</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">📝</div>
                                    <h4 class="font-semibold text-lg mb-2">Quality Documentation</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Record actual costs incurred</li>
                                        <li>Document work quality</li>
                                        <li>Note any deviations from plans</li>
                                        <li>Maintain accurate records</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Standard Construction Milestones</h4>
                            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h5 class="font-semibold mb-2">🏗️ Construction Phases</h5>
                                        <ul class="text-sm text-gray-600 space-y-1">
                                            <li>• Foundation (Omusingi)</li>
                                            <li>• Substructure</li>
                                            <li>• Superstructure</li>
                                            <li>• Roofing Level</li>
                                            <li>• Finishing Stages</li>
                                            <li>• Finalization</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h5 class="font-semibold mb-2">📈 Progress Indicators</h5>
                                        <ul class="text-sm text-gray-600 space-y-1">
                                            <li>• Completion Percentage (0-100%)</li>
                                            <li>• Status (Pending/In Progress/Completed)</li>
                                            <li>• Actual vs Estimated Costs</li>
                                            <li>• Timeline Adherence</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Step-by-Step: Updating Milestone Progress</h4>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="step-number">1</div>
                                    <div>
                                        <h5 class="font-semibold">Access Project Milestones</h5>
                                        <p class="text-gray-600">Go to Surveyor Dashboard → Select Project → View Milestones</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">2</div>
                                    <div>
                                        <h5 class="font-semibold">Select Milestone to Update</h5>
                                        <p class="text-gray-600">Choose the specific milestone you want to update progress for</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">3</div>
                                    <div>
                                        <h5 class="font-semibold">Update Progress Details</h5>
                                        <p class="text-gray-600">Set status, completion percentage, actual costs, and add progress notes</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">4</div>
                                    <div>
                                        <h5 class="font-semibold">Document Observations</h5>
                                        <p class="text-gray-600">Provide detailed notes on work quality, challenges, and site conditions</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">5</div>
                                    <div>
                                        <h5 class="font-semibold">Save Updates</h5>
                                        <p class="text-gray-600">System updates project progress and notifies relevant stakeholders</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-tip mt-6">
                                <strong>💡 Pro Tip:</strong> Update milestones regularly (at least weekly) to provide accurate progress tracking. Use the "Attention Needed" section on your dashboard to quickly identify overdue or upcoming milestones.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Operations Guide -->
                <section id="operations-guide" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">⚙️ Operations Guide</h2>
                        <p class="text-blue-100">Workflow coordination and process optimization</p>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Operations Responsibilities</h3>
                            <p class="text-gray-600 mb-6">
                                Operations personnel manage the requisition workflow, coordinate between departments, ensure process compliance, and optimize operational efficiency across all projects.
                            </p>
                            
                            <div class="feature-grid">
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">🔄</div>
                                    <h4 class="font-semibold text-lg mb-2">Workflow Management</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Monitor requisition flow</li>
                                        <li>Identify and resolve bottlenecks</li>
                                        <li>Ensure timely processing</li>
                                        <li>Coordinate between departments</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">✅</div>
                                    <h4 class="font-semibold text-lg mb-2">Approval Coordination</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Second-level approval authority</li>
                                        <li>Verify Project Manager approvals</li>
                                        <li>Ensure compliance with procedures</li>
                                        <li>Forward to Procurement</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">📈</div>
                                    <h4 class="font-semibold text-lg mb-2">Process Optimization</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Analyze process efficiency</li>
                                        <li>Identify improvement opportunities</li>
                                        <li>Implement process changes</li>
                                        <li>Monitor performance metrics</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Step-by-Step: Processing Requisitions</h4>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="step-number">1</div>
                                    <div>
                                        <h5 class="font-semibold">Review Approved Requisitions</h5>
                                        <p class="text-gray-600">Check Operations Dashboard for requisitions approved by Project Managers</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">2</div>
                                    <div>
                                        <h5 class="font-semibold">Verify Compliance</h5>
                                        <p class="text-gray-600">Ensure requisitions follow company procedures and have proper documentation</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">3</div>
                                    <div>
                                        <h5 class="font-semibold">Approve or Return</h5>
                                        <p class="text-gray-600">Approve compliant requisitions or return for corrections with comments</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">4</div>
                                    <div>
                                        <h5 class="font-semibold">Forward to Procurement</h5>
                                        <p class="text-gray-600">Send approved requisitions to Procurement department for sourcing</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">5</div>
                                    <div>
                                        <h5 class="font-semibold">Monitor Progress</h5>
                                        <p class="text-gray-600">Track requisitions through procurement and delivery stages</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-tip mt-6">
                                <strong>💡 Pro Tip:</strong> Use the "Pending" and "Approved" views to monitor workflow efficiency. Look for patterns in delays to identify process improvement opportunities.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Procurement Guide -->
                <section id="procurement-guide" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">📦 Procurement Guide</h2>
                        <p class="text-blue-100">Supplier management, purchasing, and LPO processing</p>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Procurement Responsibilities</h3>
                            <p class="text-gray-600 mb-6">
                                Procurement specialists manage supplier relationships, create purchase orders, negotiate pricing, ensure timely delivery, and maintain procurement records.
                            </p>
                            
                            <div class="feature-grid">
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">🤝</div>
                                    <h4 class="font-semibold text-lg mb-2">Supplier Management</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Maintain supplier database</li>
                                        <li>Evaluate supplier performance</li>
                                        <li>Negotiate pricing and terms</li>
                                        <li>Manage supplier relationships</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">📄</div>
                                    <h4 class="font-semibold text-lg mb-2">LPO Processing</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Create Local Purchase Orders</li>
                                        <li>Select appropriate suppliers</li>
                                        <li>Set delivery terms and dates</li>
                                        <li>Issue LPOs to suppliers</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">🚚</div>
                                    <h4 class="font-semibold text-lg mb-2">Delivery Coordination</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Track order status</li>
                                        <li>Coordinate with stores for receipt</li>
                                        <li>Resolve delivery issues</li>
                                        <li>Update requisition status</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Step-by-Step: LPO Creation & Issuance</h4>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="step-number">1</div>
                                    <div>
                                        <h5 class="font-semibold">Start Procurement Process</h5>
                                        <p class="text-gray-600">Select requisition and click "Start Procurement" to begin LPO creation</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">2</div>
                                    <div>
                                        <h5 class="font-semibold">Select Supplier</h5>
                                        <p class="text-gray-600">Choose appropriate supplier based on price, quality, and delivery capability</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">3</div>
                                    <div>
                                        <h5 class="font-semibold">Create LPO Draft</h5>
                                        <p class="text-gray-600">System generates LPO with all items, quantities, and pricing</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">4</div>
                                    <div>
                                        <h5 class="font-semibold">Send for CEO Approval</h5>
                                        <p class="text-gray-600">Submit LPO to CEO for final approval before issuance</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">5</div>
                                    <div>
                                        <h5 class="font-semibold">Issue to Supplier</h5>
                                        <p class="text-gray-600">Once CEO approved, issue LPO to supplier and track delivery</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-tip mt-6">
                                <strong>💡 Pro Tip:</strong> Maintain good relationships with multiple suppliers for each category to ensure competitive pricing and reliable delivery. Use the supplier performance metrics to make informed sourcing decisions.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Finance Guide -->
                <section id="finance-guide" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">💰 Finance Guide</h2>
                        <p class="text-blue-100">Payment processing, expense tracking, and financial reporting</p>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Finance Responsibilities</h3>
                            <p class="text-gray-600 mb-6">
                                Finance personnel manage payments, track expenses, generate financial reports, ensure budget compliance, and provide financial oversight across all projects.
                            </p>
                            
                            <div class="feature-grid">
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">💳</div>
                                    <h4 class="font-semibold text-lg mb-2">Payment Processing</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Process supplier payments</li>
                                        <li>Verify delivery completion</li>
                                        <li>Maintain payment records</li>
                                        <li>Reconcile accounts</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">📊</div>
                                    <h4 class="font-semibold text-lg mb-2">Financial Reporting</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Generate expense reports</li>
                                        <li>Track budget vs actual</li>
                                        <li>Project cost analysis</li>
                                        <li>Management reporting</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">💰</div>
                                    <h4 class="font-semibold text-lg mb-2">Budget Management</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Monitor project budgets</li>
                                        <li>Flag budget overruns</li>
                                        <li>Cost control measures</li>
                                        <li>Financial forecasting</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Step-by-Step: Processing Payments</h4>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="step-number">1</div>
                                    <div>
                                        <h5 class="font-semibold">Review Pending Payments</h5>
                                        <p class="text-gray-600">Check Finance Dashboard for delivered items awaiting payment</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">2</div>
                                    <div>
                                        <h5 class="font-semibold">Verify Delivery</h5>
                                        <p class="text-gray-600">Confirm items were received by stores and meet quality standards</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">3</div>
                                    <div>
                                        <h5 class="font-semibold">Process Payment</h5>
                                        <p class="text-gray-600">Create payment record and process through accounting system</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">4</div>
                                    <div>
                                        <h5 class="font-semibold">Update Status</h5>
                                        <p class="text-gray-600">Mark requisition as "Payment Completed" in the system</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">5</div>
                                    <div>
                                        <h5 class="font-semibold">File Documentation</h5>
                                        <p class="text-gray-600">Maintain proper records for auditing and reporting purposes</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-tip mt-6">
                                <strong>💡 Pro Tip:</strong> Use the financial reports to identify spending patterns and potential cost savings. Regular budget vs actual analysis helps prevent project overruns.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Stores Guide -->
                <section id="stores-guide" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">🏪 Stores Guide</h2>
                        <p class="text-blue-100">Inventory management, stock control, and material issuance</p>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Stores Responsibilities</h3>
                            <p class="text-gray-600 mb-6">
                                Stores personnel manage inventory, receive deliveries, issue materials to projects, maintain stock records, and ensure proper storage and security of all materials.
                            </p>
                            
                            <div class="feature-grid">
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">📦</div>
                                    <h4 class="font-semibold text-lg mb-2">Inventory Management</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Maintain stock levels</li>
                                        <li>Track inventory movements</li>
                                        <li>Conduct stock counts</li>
                                        <li>Manage stock valuation</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">✅</div>
                                    <h4 class="font-semibold text-lg mb-2">Delivery Receiving</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Receive LPO deliveries</li>
                                        <li>Verify quantity and quality</li>
                                        <li>Update inventory records</li>
                                        <li>Confirm delivery completion</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">📤</div>
                                    <h4 class="font-semibold text-lg mb-2">Material Issuance</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Process store requisitions</li>
                                        <li>Issue materials to projects</li>
                                        <li>Maintain issuance records</li>
                                        <li>Track project consumption</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Step-by-Step: Receiving LPO Deliveries</h4>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="step-number">1</div>
                                    <div>
                                        <h5 class="font-semibold">Check Pending Deliveries</h5>
                                        <p class="text-gray-600">Review Stores Dashboard for LPOs awaiting delivery confirmation</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">2</div>
                                    <div>
                                        <h5 class="font-semibold">Receive Physical Delivery</h5>
                                        <p class="text-gray-600">Inspect delivered items for quantity, quality, and condition</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">3</div>
                                    <div>
                                        <h5 class="font-semibold">Confirm Delivery in System</h5>
                                        <p class="text-gray-600">Use "Confirm Delivery" to update received quantities and condition</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">4</div>
                                    <div>
                                        <h5 class="font-semibold">Update Inventory</h5>
                                        <p class="text-gray-600">System automatically adds received items to store inventory</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">5</div>
                                    <div>
                                        <h5 class="font-semibold">Notify Stakeholders</h5>
                                        <p class="text-gray-600">System updates requisition status and notifies relevant parties</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-tip mt-6">
                                <strong>💡 Pro Tip:</strong> Conduct regular stock counts to ensure system records match physical inventory. Use the low stock alerts to proactively reorder critical items before they run out.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Supplier Guide -->
                <section id="supplier-guide" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">🚚 Supplier Guide</h2>
                        <p class="text-blue-100">Order management, delivery tracking, and communication</p>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Supplier Portal Features</h3>
                            <p class="text-gray-600 mb-6">
                                Suppliers can view their orders, track delivery status, update order information, and communicate with ADVANTA procurement team through the supplier portal.
                            </p>
                            
                            <div class="feature-grid">
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">📄</div>
                                    <h4 class="font-semibold text-lg mb-2">Order Management</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>View assigned LPOs</li>
                                        <li>Track order status</li>
                                        <li>Download order documents</li>
                                        <li>Manage order fulfillment</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">🚛</div>
                                    <h4 class="font-semibold text-lg mb-2">Delivery Tracking</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Update delivery status</li>
                                        <li>Provide delivery updates</li>
                                        <li>Upload delivery documents</li>
                                        <li>Confirm delivery completion</li>
                                    </ul>
                                </div>
                                
                                <div class="feature-card">
                                    <div class="text-2xl mb-3">💬</div>
                                    <h4 class="font-semibold text-lg mb-2">Communication</h4>
                                    <ul class="text-gray-600 space-y-1 text-sm">
                                        <li>Message procurement team</li>
                                        <li>Receive order updates</li>
                                        <li>Clarify order requirements</li>
                                        <li>Report delivery issues</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Step-by-Step: Managing Orders</h4>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="step-number">1</div>
                                    <div>
                                        <h5 class="font-semibold">Login to Supplier Portal</h5>
                                        <p class="text-gray-600">Access your dedicated supplier dashboard with provided credentials</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">2</div>
                                    <div>
                                        <h5 class="font-semibold">Review Assigned LPOs</h5>
                                        <p class="text-gray-600">Check for new LPOs and review order details, quantities, and delivery dates</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">3</div>
                                    <div>
                                        <h5 class="font-semibold">Acknowledge Order</h5>
                                        <p class="text-gray-600">Confirm order acceptance and provide expected delivery timeline</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">4</div>
                                    <div>
                                        <h5 class="font-semibold">Update Delivery Status</h5>
                                        <p class="text-gray-600">Provide regular updates on order preparation and delivery progress</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">5</div>
                                    <div>
                                        <h5 class="font-semibold">Complete Delivery</h5>
                                        <p class="text-gray-600">Deliver items to specified location and confirm delivery completion</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-tip mt-6">
                                <strong>💡 Pro Tip:</strong> Maintain open communication with the procurement team regarding any delivery challenges. Timely updates help avoid project delays and maintain good business relationships.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Requisition Workflow -->
                <section id="requisition-workflow" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">Requisition Workflow Process</h2>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Complete Requisition Lifecycle</h3>
                            
                            <div class="workflow-diagram">
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 text-center">
                                    <div class="bg-blue-100 p-4 rounded-lg">
                                        <div class="text-2xl mb-2">📝</div>
                                        <h4 class="font-semibold">Creation</h4>
                                        <p class="text-sm mt-2">Engineer creates requisition with items and specifications</p>
                                    </div>
                                    <div class="bg-green-100 p-4 rounded-lg">
                                        <div class="text-2xl mb-2">✅</div>
                                        <h4 class="font-semibold">PM Approval</h4>
                                        <p class="text-sm mt-2">Project Manager reviews and approves based on project needs</p>
                                    </div>
                                    <div class="bg-yellow-100 p-4 rounded-lg">
                                        <div class="text-2xl mb-2">⚙️</div>
                                        <h4 class="font-semibold">Ops Review</h4>
                                        <p class="text-sm mt-2">Operations ensures procedure compliance and forwards</p>
                                    </div>
                                    <div class="bg-purple-100 p-4 rounded-lg">
                                        <div class="text-2xl mb-2">👑</div>
                                        <h4 class="font-semibold">CEO Approval</h4>
                                        <p class="text-sm mt-2">CEO provides final approval for major purchases</p>
                                    </div>
                                    <div class="bg-orange-100 p-4 rounded-lg">
                                        <div class="text-2xl mb-2">📦</div>
                                        <h4 class="font-semibold">Procurement</h4>
                                        <p class="text-sm mt-2">Procurement creates LPO and manages supplier process</p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center mt-4">
                                    <div class="bg-indigo-100 p-4 rounded-lg">
                                        <div class="text-2xl mb-2">🚚</div>
                                        <h4 class="font-semibold">Delivery</h4>
                                        <p class="text-sm mt-2">Supplier delivers items to stores for inspection</p>
                                    </div>
                                    <div class="bg-teal-100 p-4 rounded-lg">
                                        <div class="text-2xl mb-2">🏪</div>
                                        <h4 class="font-semibold">Store Receiving</h4>
                                        <p class="text-sm mt-2">Stores confirm delivery and update inventory</p>
                                    </div>
                                    <div class="bg-red-100 p-4 rounded-lg">
                                        <div class="text-2xl mb-2">💰</div>
                                        <h4 class="font-semibold">Payment</h4>
                                        <p class="text-sm mt-2">Finance processes payment to supplier</p>
                                    </div>
                                    <div class="bg-gray-100 p-4 rounded-lg">
                                        <div class="text-2xl mb-2">✅</div>
                                        <h4 class="font-semibold">Completion</h4>
                                        <p class="text-sm mt-2">Requisition marked complete and archived</p>
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Requisition Status Definitions</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="status-badge bg-yellow-500 text-white mb-2">Pending</span>
                                    <p class="text-sm text-gray-600">Awaiting Project Manager approval</p>
                                    
                                    <span class="status-badge bg-blue-500 text-white mb-2 mt-4">Project Manager Approved</span>
                                    <p class="text-sm text-gray-600">Approved by PM, awaiting Operations</p>
                                    
                                    <span class="status-badge bg-indigo-500 text-white mb-2 mt-4">Operations Approved</span>
                                    <p class="text-sm text-gray-600">Approved by Ops, sent to Procurement</p>
                                    
                                    <span class="status-badge bg-purple-500 text-white mb-2 mt-4">Procurement</span>
                                    <p class="text-sm text-gray-600">Being processed by Procurement team</p>
                                </div>
                                <div>
                                    <span class="status-badge bg-orange-500 text-white mb-2">Pending CEO Approval</span>
                                    <p class="text-sm text-gray-600">LPO created, awaiting CEO approval</span>
                                    
                                    <span class="status-badge bg-green-500 text-white mb-2 mt-4">CEO Approved</span>
                                    <p class="text-sm text-gray-600">CEO approved, ready for LPO issuance</p>
                                    
                                    <span class="status-badge bg-teal-500 text-white mb-2 mt-4">LPO Issued</span>
                                    <p class="text-sm text-gray-600">LPO issued to supplier, awaiting delivery</p>
                                    
                                    <span class="status-badge bg-gray-500 text-white mb-2 mt-4">Completed</span>
                                    <p class="text-sm text-gray-600">All items delivered and payment processed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Troubleshooting -->
                <section id="troubleshooting" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">Troubleshooting & Common Issues</h2>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Frequently Encountered Problems</h3>
                            
                            <div class="space-y-6">
                                <div class="troubleshooting-item">
                                    <h4 class="font-semibold text-red-800 mb-2">❌ Cannot Login to System</h4>
                                    <p class="text-red-700 mb-2">Possible causes and solutions:</p>
                                    <ul class="list-disc list-inside text-red-700 space-y-1">
                                        <li>Check you're using the correct role-specific login page</li>
                                        <li>Verify your email and password are correct</li>
                                        <li>Ensure Caps Lock is not activated</li>
                                        <li>Clear browser cache and cookies</li>
                                        <li>Try using Google Chrome browser</li>
                                        <li>Contact administrator if issue persists</li>
                                    </ul>
                                </div>
                                
                                <div class="troubleshooting-item">
                                    <h4 class="font-semibold text-red-800 mb-2">❌ Requisition Stuck in Approval</h4>
                                    <p class="text-red-700 mb-2">Possible causes and solutions:</p>
                                    <ul class="list-disc list-inside text-red-700 space-y-1">
                                        <li>Check if approver is available/on leave</li>
                                        <li>Verify all required information is provided</li>
                                        <li>Contact the approver directly for update</li>
                                        <li>Check if budget approval is required</li>
                                        <li>Contact Operations for workflow assistance</li>
                                    </ul>
                                </div>
                                
                                <div class="troubleshooting-item">
                                    <h4 class="font-semibold text-red-800 mb-2">❌ System Running Slowly</h4>
                                    <p class="text-red-700 mb-2">Possible causes and solutions:</p>
                                    <ul class="list-disc list-inside text-red-700 space-y-1">
                                        <li>Check your internet connection speed</li>
                                        <li>Close unnecessary browser tabs</li>
                                        <li>Clear browser cache and history</li>
                                        <li>Try accessing during off-peak hours</li>
                                        <li>Restart your computer and router</li>
                                        <li>Contact IT if problem continues</li>
                                    </ul>
                                </div>
                                
                                <div class="troubleshooting-item">
                                    <h4 class="font-semibold text-red-800 mb-2">❌ Cannot Find Specific Feature</h4>
                                    <p class="text-red-700 mb-2">Possible causes and solutions:</p>
                                    <ul class="list-disc list-inside text-red-700 space-y-1">
                                        <li>Verify your user role has access to the feature</li>
                                        <li>Check different menu sections</li>
                                        <li>Use the search functionality</li>
                                        <li>Consult this manual for role-specific features</li>
                                        <li>Contact administrator for access rights</li>
                                    </ul>
                                </div>
                                
                                <div class="troubleshooting-item">
                                    <h4 class="font-semibold text-red-800 mb-2">❌ Error Messages During Use</h4>
                                    <p class="text-red-700 mb-2">Possible causes and solutions:</p>
                                    <ul class="list-disc list-inside text-red-700 space-y-1">
                                        <li>Note the exact error message text</li>
                                        <li>Take screenshot if possible</li>
                                        <li>Try the action again after few minutes</li>
                                        <li>Check if others are experiencing same issue</li>
                                        <li>Report to IT with error details</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Preventive Measures</h4>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                                <ul class="list-disc list-inside text-green-700 space-y-2">
                                    <li>Regularly clear browser cache and cookies</li>
                                    <li>Keep browser updated to latest version</li>
                                    <li>Use recommended browser (Google Chrome)</li>
                                    <li>Maintain stable internet connection</li>
                                    <li>Log out properly after each session</li>
                                    <li>Report issues promptly to prevent escalation</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Support Section -->
                <section id="support" class="section-card">
                    <div class="section-header">
                        <h2 class="text-2xl font-bold">Support & Contact Information</h2>
                    </div>
                    <div class="p-8">
                        <div class="prose max-w-none">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Getting Help</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <h4 class="font-semibold text-lg mb-3">📞 Immediate Assistance</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <i class="bi bi-telephone text-blue-600 mr-3"></i>
                                            <div>
                                                <p class="font-medium">IT Help Desk</p>
                                                <p class="text-gray-600">+256-707-208954</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="bi bi-envelope text-blue-600 mr-3"></i>
                                            <div>
                                                <p class="font-medium">Email Support</p>
                                                <p class="text-gray-600">it-support@advanta.co.ug</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="bi bi-clock text-blue-600 mr-3"></i>
                                            <div>
                                                <p class="font-medium">Support Hours</p>
                                                <p class="text-gray-600">Mon-Fri: 8:00 AM - 5:00 PM</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <h4 class="font-semibold text-lg mb-3">👥 Department Contacts</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <i class="bi bi-person text-green-600 mr-3"></i>
                                            <div>
                                                <p class="font-medium">System Administrator</p>
                                                <p class="text-gray-600">For user access and permissions</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="bi bi-gear text-orange-600 mr-3"></i>
                                            <div>
                                                <p class="font-medium">Operations Department</p>
                                                <p class="text-gray-600">For workflow and process issues</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="bi bi-cash-coin text-purple-600 mr-3"></i>
                                            <div>
                                                <p class="font-medium">Finance Department</p>
                                                <p class="text-gray-600">For payment and budget queries</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold text-lg mt-8 mb-4">Issue Reporting Procedure</h4>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="step-number">1</div>
                                    <div>
                                        <h5 class="font-semibold">Document the Issue</h5>
                                        <p class="text-gray-600">Take screenshots, note error messages, and document steps to reproduce</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">2</div>
                                    <div>
                                        <h5 class="font-semibold">Check This Manual</h5>
                                        <p class="text-gray-600">Review troubleshooting section for known issues and solutions</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">3</div>
                                    <div>
                                        <h5 class="font-semibold">Contact Appropriate Department</h5>
                                        <p class="text-gray-600">Based on issue type, contact the relevant department</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="step-number">4</div>
                                    <div>
                                        <h5 class="font-semibold">Follow Up</h5>
                                        <p class="text-gray-600">If not resolved in reasonable time, escalate to IT Help Desk</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
                                <h4 class="font-semibold text-blue-800 mb-2">🚨 Emergency Contact</h4>
                                <p class="text-blue-700">For system-wide outages or critical business-impacting issues, contact the IT Manager directly at <strong>+256-707-208954</strong></p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Footer -->
                <footer class="text-center py-8 text-gray-600 border-t mt-8">
                    <p>ADVANTA Uganda Limited ERP System Manual</p>
                    <p class="text-sm mt-2">Document Version 2.1 | Last Updated: {{ date('F d, Y') }}</p>
                    <p class="text-sm mt-1">© {{ date('Y') }} Advanta Uganda Ltd. All rights reserved.</p>
                </footer>
            </main>
        </div>
    </div>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // Update active nav link
                    document.querySelectorAll('.nav-link').forEach(link => {
                        link.classList.remove('active');
                    });
                    this.classList.add('active');
                }
            });
        });

        // Update active nav link on scroll
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('.nav-link');
            
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= sectionTop - 100) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });

        // Dark mode toggle
        function toggleDarkMode() {
            document.body.classList.toggle('bg-gray-900');
            document.body.classList.toggle('text-white');
            
            const cards = document.querySelectorAll('.section-card, .feature-card, .nav-sidebar');
            cards.forEach(card => {
                card.classList.toggle('bg-gray-800');
                card.classList.toggle('text-white');
            });
        }

        // Print functionality
        function printManual() {
            window.print();
        }
    </script>
</body>
</html>