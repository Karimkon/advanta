<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Advanta Construction Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .header {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f1f32 100%);
            color: white;
            padding: 60px 0;
        }
        .support-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 40px;
            margin-top: -30px;
            margin-bottom: 40px;
        }
        .whatsapp-btn {
            background: #25D366;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            text-decoration: none;
        }
        .whatsapp-btn:hover {
            background: #128C7E;
            color: white;
            transform: translateY(-2px);
        }
        .contact-method {
            border-left: 4px solid #1e3a5f;
            padding-left: 20px;
            margin-bottom: 25px;
        }
        footer {
            background: #1e3a5f;
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="header text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Support Center</h1>
            <p class="lead">We're here to help you with any issues or questions</p>
        </div>
    </div>

    <div class="container">
        <div class="support-card">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="mb-4" style="color: #1e3a5f;">Get Help Quickly</h2>
                    <p class="lead mb-4">Our support team is available to assist you with any questions about the Advanta Construction Management System.</p>
                    
                    <div class="contact-method">
                        <h4><i class="bi bi-whatsapp"></i> WhatsApp Support</h4>
                        <p class="mb-3">For immediate assistance, click below to chat with our support team on WhatsApp:</p>
                        <a href="{{ $whatsappUrl }}" class="whatsapp-btn" target="_blank">
                            <i class="bi bi-whatsapp" style="font-size: 24px;"></i>
                            Chat on WhatsApp Now
                        </a>
                        <p class="mt-2 text-muted">Phone: +256 {{ substr($whatsappNumber, 1) }}</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="contact-method">
                        <h4><i class="bi bi-envelope"></i> Email Support</h4>
                        <p>For detailed inquiries or documentation:</p>
                        <p class="h5">support@advantaltd.site</p>
                        <p class="text-muted">Response time: Within 24 hours</p>
                    </div>
                    
                    <div class="contact-method">
                        <h4><i class="bi bi-globe"></i> Website</h4>
                        <p>Visit our website for more information:</p>
                        <p class="h5">https://advantaltd.site</p>
                    </div>
                    
                    <div class="contact-method">
                        <h4><i class="bi bi-clock"></i> Support Hours</h4>
                        <p>Monday - Friday: 8:00 AM - 6:00 PM EAT</p>
                        <p>Saturday: 9:00 AM - 1:00 PM EAT</p>
                        <p>Sunday: Emergency support only</p>
                    </div>
                </div>
            </div>
            
            <hr class="my-5">
            
            <div class="row">
                <div class="col-12">
                    <h3 class="mb-4" style="color: #1e3a5f;">Frequently Asked Questions</h3>
                    
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How do I reset my password?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Contact your system administrator or use the "Forgot Password" feature on your role-specific login page.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    How do I submit a requisition?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Navigate to Requisitions → Create New Requisition in your dashboard. Fill in the required details and submit for approval.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Where can I view my project milestones?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Go to Projects → Select your project → Milestones tab. You can view progress and upload photos here.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info mt-5">
                <h5><i class="bi bi-info-circle"></i> Need Urgent Help?</h5>
                <p class="mb-0">For critical system issues affecting your construction operations, please call us immediately or use the WhatsApp button above for fastest response.</p>
            </div>
        </div>
    </div>

    <footer class="text-center">
        <div class="container">
            <p class="mb-0">&copy; 2026 Advanta Uganda Ltd. All rights reserved.</p>
            <p class="mb-0">
                <a href="/" class="text-white me-3">Home</a> | 
                <a href="/privacy" class="text-white me-3">Privacy Policy</a> | 
                <a href="/support" class="text-white">Support</a>
            </p>
        </div>
    </footer>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>