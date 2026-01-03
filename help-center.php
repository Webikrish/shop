<?php
// help-center.php - Help Center Page
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - ShopEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* Use same CSS variables and base styles from other pages */
        :root {
            --primary: #ff6b6b;
            --secondary: #4ecdc4;
            --accent: #ffd166;
            --dark: #2d3047;
            --light: #f7f9fc;
            --text: #333;
            --text-light: #666;
            --border: #e1e5eb;
            --success: #06d6a0;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            --radius: 8px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text);
            background-color: var(--light);
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Help Center Specific Styles */
        .help-hero {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--accent) 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
            margin-bottom: 50px;
        }

        .help-hero h1 {
            font-size: 42px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .help-hero p {
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }

        .search-help {
            max-width: 600px;
            margin: 30px auto 0;
            position: relative;
        }

        .search-help input {
            width: 100%;
            padding: 15px 20px;
            padding-right: 60px;
            border: none;
            border-radius: var(--radius);
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            box-shadow: var(--shadow);
        }

        .search-help button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .search-help button:hover {
            background: #ff5252;
        }

        /* Help Categories */
        .help-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .category-card {
            background-color: white;
            border-radius: var(--radius);
            padding: 30px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
            border: 1px solid transparent;
        }

        .category-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .category-icon {
            width: 70px;
            height: 70px;
            background-color: rgba(255, 107, 107, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
            color: var(--primary);
        }

        .category-card:nth-child(2) .category-icon {
            background-color: rgba(78, 205, 196, 0.1);
            color: var(--secondary);
        }

        .category-card:nth-child(3) .category-icon {
            background-color: rgba(255, 209, 102, 0.1);
            color: var(--accent);
        }

        .category-card:nth-child(4) .category-icon {
            background-color: rgba(6, 214, 160, 0.1);
            color: var(--success);
        }

        .category-card h3 {
            font-size: 22px;
            margin-bottom: 15px;
            color: var(--dark);
        }

        .category-card p {
            color: var(--text-light);
            margin-bottom: 20px;
        }

        /* Popular Questions */
        .popular-questions {
            margin-bottom: 60px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-title h2 {
            font-size: 32px;
            color: var(--dark);
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--primary);
        }

        .section-title p {
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }

        .faq-list {
            max-width: 900px;
            margin: 0 auto;
        }

        .faq-item {
            background-color: white;
            border-radius: var(--radius);
            margin-bottom: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .faq-question {
            padding: 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
            background-color: white;
        }

        .faq-question:hover {
            background-color: var(--light);
        }

        .faq-question h3 {
            font-size: 18px;
            color: var(--dark);
            margin: 0;
            flex: 1;
        }

        .faq-icon {
            font-size: 20px;
            color: var(--primary);
            transition: var(--transition);
            margin-left: 15px;
            flex-shrink: 0;
        }

        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease;
        }

        .faq-answer p {
            padding: 20px 0;
            color: var(--text-light);
            border-top: 1px solid var(--border);
            margin: 0;
        }

        .faq-answer ul, .faq-answer ol {
            margin-left: 20px;
            margin-bottom: 15px;
        }

        .faq-answer li {
            margin-bottom: 8px;
            color: var(--text-light);
        }

        .faq-item.active .faq-question {
            background-color: var(--light);
        }

        .faq-item.active .faq-icon {
            transform: rotate(45deg);
        }

        .faq-item.active .faq-answer {
            max-height: 1000px;
        }

        /* Help Sections */
        .help-section {
            margin-bottom: 60px;
            padding: 40px;
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .help-section h2 {
            font-size: 28px;
            color: var(--dark);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary);
        }

        .help-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .help-topic {
            background-color: var(--light);
            padding: 25px;
            border-radius: var(--radius);
            border-left: 4px solid var(--primary);
        }

        .help-topic h3 {
            font-size: 20px;
            color: var(--dark);
            margin-bottom: 15px;
        }

        .help-topic p {
            color: var(--text-light);
            margin-bottom: 15px;
        }

        .help-topic ul {
            margin-left: 20px;
            color: var(--text-light);
        }

        .help-topic li {
            margin-bottom: 8px;
        }

        /* Contact Support */
        .contact-support {
            background-color: var(--dark);
            color: white;
            padding: 50px;
            border-radius: var(--radius);
            text-align: center;
            margin-bottom: 60px;
        }

        .contact-support h2 {
            font-size: 32px;
            margin-bottom: 20px;
        }

        .contact-support p {
            max-width: 600px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }

        .support-options {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .support-option {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: var(--radius);
            min-width: 220px;
            transition: var(--transition);
        }

        .support-option:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }

        .support-option i {
            font-size: 32px;
            margin-bottom: 15px;
            display: block;
            color: var(--secondary);
        }

        .support-option h4 {
            margin-bottom: 10px;
            font-size: 20px;
        }

        .support-option a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            margin-top: 10px;
        }

        .support-option a:hover {
            text-decoration: underline;
        }

        /* Quick Links */
        .quick-links {
            background-color: white;
            padding: 40px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 60px;
        }

        .quick-links h2 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--dark);
        }

        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .link-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background-color: var(--light);
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--text);
            transition: var(--transition);
        }

        .link-item:hover {
            background-color: var(--primary);
            color: white;
        }

        .link-item:hover i {
            color: white;
        }

        .link-item i {
            font-size: 20px;
            color: var(--primary);
            transition: var(--transition);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .help-hero h1 {
                font-size: 32px;
            }
            
            .help-hero {
                padding: 60px 0;
            }
            
            .help-categories {
                grid-template-columns: 1fr;
            }
            
            .support-options {
                flex-direction: column;
                align-items: center;
            }
            
            .support-option {
                width: 100%;
                max-width: 300px;
            }
            
            .help-section {
                padding: 25px;
            }
            
            .quick-links {
                padding: 25px;
            }
        }

        @media (max-width: 480px) {
            .help-hero h1 {
                font-size: 28px;
            }
            
            .help-hero p {
                font-size: 16px;
            }
            
            .section-title h2 {
                font-size: 24px;
            }
            
            .contact-support {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include "nav.php"; ?>

    <!-- Help Center Hero -->
    <section class="help-hero">
        <div class="container">
            <h1>How can we help you?</h1>
            <p>Find answers to common questions or get in touch with our support team</p>
            
            <div class="search-help">
                <input type="text" placeholder="Search for help articles, FAQs, or guides...">
                <button><i class="fas fa-search"></i></button>
            </div>
        </div>
    </section>

    <!-- Help Categories -->
    <div class="container">
        <div class="help-categories">
            <div class="category-card" onclick="scrollToSection('orders')">
                <div class="category-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>Orders & Shipping</h3>
                <p>Track orders, shipping info, delivery times, order modifications</p>
            </div>
            
            <div class="category-card" onclick="scrollToSection('returns')">
                <div class="category-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3>Returns & Refunds</h3>
                <p>Return policy, refund process, exchanges, damaged items</p>
            </div>
            
            <div class="category-card" onclick="scrollToSection('account')">
                <div class="category-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h3>Account & Security</h3>
                <p>Login issues, password reset, account settings, security</p>
            </div>
            
            <div class="category-card" onclick="scrollToSection('payments')">
                <div class="category-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3>Payments & Pricing</h3>
                <p>Payment methods, discounts, billing questions, pricing</p>
            </div>
        </div>

        <!-- Quick Links -->
        <section class="quick-links">
            <h2>Quick Links</h2>
            <div class="links-grid">
                <a href="track-order.php" class="link-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Track Your Order</span>
                </a>
                <a href="returns.php" class="link-item">
                    <i class="fas fa-undo-alt"></i>
                    <span>Start a Return</span>
                </a>
                <a href="size-guide.php" class="link-item">
                    <i class="fas fa-ruler"></i>
                    <span>Size Guide</span>
                </a>
                <a href="shipping.php" class="link-item">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Shipping Policy</span>
                </a>
                <a href="contact.php" class="link-item">
                    <i class="fas fa-headset"></i>
                    <span>Contact Support</span>
                </a>
                <a href="faq.php" class="link-item">
                    <i class="fas fa-question-circle"></i>
                    <span>View All FAQs</span>
                </a>
            </div>
        </section>

        <!-- Popular Questions -->
        <section class="popular-questions">
            <div class="section-title">
                <h2>Frequently Asked Questions</h2>
                <p>Find quick answers to our most commonly asked questions</p>
            </div>
            
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How can I track my order?</h3>
                        <span class="faq-icon">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>You can track your order through multiple methods:</p>
                        <ol>
                            <li><strong>Website/App:</strong> Log into your account → Order History → View Tracking</li>
                            <li><strong>Email/SMS:</strong> We send tracking links once order ships</li>
                            <li><strong>Order Number:</strong> Use your order number on our Track Order page</li>
                            <li><strong>Customer Support:</strong> Contact us with your order number</li>
                        </ol>
                        <p><strong>Note:</strong> Tracking updates may take 24-48 hours to appear after shipment.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What is your return policy?</h3>
                        <span class="faq-icon">+</span>
                    </div>
                    <div class="faq-answer">
                        <p><strong>30-Day Return Window:</strong> Most products can be returned within 30 days of delivery.</p>
                        <p><strong>Return Conditions:</strong></p>
                        <ul>
                            <li>Product must be unused and in original condition</li>
                            <li>Original packaging with all tags attached</li>
                            <li>Include original invoice/packing slip</li>
                            <li>No damage, wear, or alterations</li>
                        </ul>
                        <p><strong>Non-Returnable Items:</strong></p>
                        <ul>
                            <li>Perishable goods (food, flowers)</li>
                            <li>Intimate apparel (underwear, swimwear)</li>
                            <li>Personalized/customized products</li>
                            <li>Digital products (software, e-books)</li>
                            <li>Health & personal care (if seal broken)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How long does shipping take?</h3>
                        <span class="faq-icon">+</span>
                    </div>
                    <div class="faq-answer">
                        <p><strong>Delivery Timeframes:</strong></p>
                        <ul>
                            <li><strong>Standard Shipping:</strong> 5-7 business days (Free on orders above ₹499)</li>
                            <li><strong>Express Shipping:</strong> 2-3 business days (₹99 extra)</li>
                            <li><strong>Same-day Delivery:</strong> Same day if ordered before 2 PM (₹149 extra, select cities only)</li>
                            <li><strong>International Shipping:</strong> 10-21 business days (varies by destination)</li>
                        </ul>
                        <p><strong>Processing Time:</strong> Orders are processed within 24 hours (excluding weekends and holidays).</p>
                        <p><strong>Delivery Delays:</strong> During peak seasons or unforeseen circumstances, deliveries may be delayed by 1-2 days.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What payment methods do you accept?</h3>
                        <span class="faq-icon">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>We accept all major payment methods:</p>
                        <ul>
                            <li><strong>Credit/Debit Cards:</strong> Visa, MasterCard, American Express, RuPay</li>
                            <li><strong>Digital Wallets:</strong> Paytm, PhonePe, Google Pay, Amazon Pay</li>
                            <li><strong>Net Banking:</strong> All major Indian banks</li>
                            <li><strong>UPI:</strong> All UPI-enabled apps</li>
                            <li><strong>Cash on Delivery (COD):</strong> Available for orders up to ₹5,000</li>
                            <li><strong>EMI:</strong> Available on selected products (3-24 months)</li>
                        </ul>
                        <p><strong>Security:</strong> All payments are processed through secure, encrypted gateways. We never store your card details.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How do I reset my password?</h3>
                        <span class="faq-icon">+</span>
                    </div>
                    <div class="faq-answer">
                        <p><strong>Password Reset Process:</strong></p>
                        <ol>
                            <li>Click "Forgot Password" on the login page</li>
                            <li>Enter your registered email address</li>
                            <li>Check your email for password reset link (check spam folder)</li>
                            <li>Click the link and create a new password</li>
                            <li>Login with new credentials</li>
                        </ol>
                        <p><strong>If you don't receive the email:</strong></p>
                        <ul>
                            <li>Wait 5-10 minutes (sometimes delays occur)</li>
                            <li>Check your spam/junk folder</li>
                            <li>Ensure you're using the correct email address</li>
                            <li>Contact customer support if issue persists</li>
                        </ul>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Do you offer international shipping?</h3>
                        <span class="faq-icon">+</span>
                    </div>
                    <div class="faq-answer">
                        <p><strong>Currently:</strong> We primarily ship within India. However, we're working on expanding our international shipping services.</p>
                        <p><strong>Limited International Shipping:</strong> Some products may be available for international shipping to select countries. Check product page for availability.</p>
                        <p><strong>International Shipping Details:</strong></p>
                        <ul>
                            <li>Shipping time: 10-21 business days</li>
                            <li>Shipping costs vary by destination</li>
                            <li>Customs duties and taxes may apply</li>
                            <li>Returns may have additional restrictions</li>
                        </ul>
                        <p><strong>For International Inquiries:</strong> Contact our customer support team for specific country availability and shipping options.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Can I modify or cancel my order?</h3>
                        <span class="faq-icon">+</span>
                    </div>
                    <div class="faq-answer">
                        <p><strong>Order Modification/Cancellation Policy:</strong></p>
                        <ul>
                            <li><strong>Within 1 hour:</strong> Orders can be modified/cancelled free of charge</li>
                            <li><strong>After 1 hour:</strong> Contact customer support immediately (modifications subject to availability)</li>
                            <li><strong>Once shipped:</strong> Cannot be modified/cancelled (you can refuse delivery or return)</li>
                        </ul>
                        <p><strong>How to Modify/Cancel:</strong></p>
                        <ol>
                            <li>Log into your account</li>
                            <li>Go to Order History</li>
                            <li>Find the order and click "Modify" or "Cancel"</li>
                            <li>Follow the prompts</li>
                        </ol>
                        <p><strong>Refund for Cancelled Orders:</strong> Refunds are processed within 7-10 business days to original payment method.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What is your warranty policy?</h3>
                        <span class="faq-icon">+</span>
                    </div>
                    <div class="faq-answer">
                        <p><strong>Standard Warranty Periods:</strong></p>
                        <ul>
                            <li><strong>Electronics:</strong> 1 year manufacturer warranty</li>
                            <li><strong>Appliances:</strong> 2 years manufacturer warranty</li>
                            <li><strong>Furniture:</strong> 1 year warranty</li>
                            <li><strong>Clothing/Accessories:</strong> 30-day quality guarantee</li>
                        </ul>
                        <p><strong>Warranty Coverage:</strong></p>
                        <ul>
                            <li>Manufacturing defects</li>
                            <li>Faulty components</li>
                            <li>Non-working parts</li>
                            <li>Does not cover: Normal wear & tear, accidental damage, misuse</li>
                        </ul>
                        <p><strong>Warranty Claim Process:</strong></p>
                        <ol>
                            <li>Contact customer support with proof of purchase</li>
                            <li>Describe the issue with photos/videos</li>
                            <li>We'll arrange pickup for inspection</li>
                            <li>Repair/replacement within warranty terms</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Help Sections -->
        <section id="orders" class="help-section">
            <h2>Orders & Shipping Help</h2>
            <div class="help-grid">
                <div class="help-topic">
                    <h3>Order Status</h3>
                    <p>Understanding your order status:</p>
                    <ul>
                        <li><strong>Processing:</strong> Order being prepared</li>
                        <li><strong>Shipped:</strong> Order dispatched with tracking</li>
                        <li><strong>Out for Delivery:</strong> With delivery agent</li>
                        <li><strong>Delivered:</strong> Successfully delivered</li>
                        <li><strong>Cancelled:</strong> Order cancelled</li>
                    </ul>
                </div>
                <div class="help-topic">
                    <h3>Shipping Methods</h3>
                    <p>Choose the best shipping option:</p>
                    <ul>
                        <li>Standard (5-7 days) - Free over ₹499</li>
                        <li>Express (2-3 days) - ₹99</li>
                        <li>Same-day - ₹149 (select cities)</li>
                        <li>International - Varies by location</li>
                    </ul>
                </div>
                <div class="help-topic">
                    <h3>Delivery Issues</h3>
                    <p>Common delivery problems:</p>
                    <ul>
                        <li>Not received tracking info</li>
                        <li>Delivery delayed</li>
                        <li>Wrong address</li>
                        <li>Package damaged</li>
                        <li>Refused delivery</li>
                    </ul>
                </div>
            </div>
        </section>

        <section id="returns" class="help-section">
            <h2>Returns & Refunds Help</h2>
            <div class="help-grid">
                <div class="help-topic">
                    <h3>Return Process</h3>
                    <p>Step-by-step return guide:</p>
                    <ol>
                        <li>Initiate return within 30 days</li>
                        <li>Pack item properly</li>
                        <li>Schedule pickup or drop-off</li>
                        <li>Get refund after inspection</li>
                    </ol>
                </div>
                <div class="help-topic">
                    <h3>Refund Timeline</h3>
                    <p>When to expect your refund:</p>
                    <ul>
                        <li>Credit/Debit Cards: 7-10 days</li>
                        <li>UPI/Net Banking: 3-5 days</li>
                        <li>Wallets: 1-2 days</li>
                        <li>COD: 7-10 days (bank transfer)</li>
                    </ul>
                </div>
                <div class="help-topic">
                    <h3>Exchange Policy</h3>
                    <p>Quick and easy exchanges:</p>
                    <ul>
                        <li>Size/color exchanges available</li>
                        <li>No extra charge for defective items</li>
                        <li>Exchange shipping covered by us</li>
                        <li>Processed within 3-5 business days</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Contact Support -->
        <section class="contact-support">
            <h2>Still Need Help?</h2>
            <p>Our support team is available 24/7 to assist you with any questions or concerns</p>
            
            <div class="support-options">
                <div class="support-option">
                    <i class="fas fa-phone"></i>
                    <h4>Call Us</h4>
                    <p><a href="tel:+919876543210">+91 98765 43210</a></p>
                    <p>Available 9 AM - 8 PM</p>
                    <p>Monday - Saturday</p>
                </div>
                
                <div class="support-option">
                    <i class="fas fa-envelope"></i>
                    <h4>Email Us</h4>
                    <p><a href="mailto:support@shopeasy.com">support@shopeasy.com</a></p>
                    <p>Response within 24 hours</p>
                    <p>Include order number for faster response</p>
                </div>
                
                <div class="support-option">
                    <i class="fas fa-comments"></i>
                    <h4>Live Chat</h4>
                    <p>Chat with our agents</p>
                    <p>Available 10 AM - 7 PM</p>
                    <p>Instant responses</p>
                </div>
                
                <div class="support-option">
                    <i class="fas fa-question-circle"></i>
                    <h4>FAQ Center</h4>
                    <p>Browse our knowledge base</p>
                    <p>1000+ articles</p>
                    <p>Updated regularly</p>
                </div>
            </div>
            
            <div style="margin-top: 40px;">
                <h3>Business Hours</h3>
                <p>Customer Support: Monday - Saturday, 9 AM - 8 PM | Sunday, 10 AM - 6 PM</p>
                <p>Emergency Support: Available 24/7 for order-related emergencies</p>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <?php include "footer.php"; ?>

    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const nav = document.querySelector('nav');
        
        if(mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                nav.classList.toggle('active');
                const icon = mobileMenuBtn.querySelector('i');
                if(icon.classList.contains('fa-bars')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        }
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (mobileMenuBtn && nav && !nav.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                nav.classList.remove('active');
                const icon = mobileMenuBtn.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
        
        // FAQ Accordion Functionality - FIXED
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            // Set first item as active by default
            if (faqItems.length > 0) {
                faqItems[0].classList.add('active');
            }
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                
                question.addEventListener('click', () => {
                    const isActive = item.classList.contains('active');
                    
                    // Close all items
                    faqItems.forEach(otherItem => {
                        otherItem.classList.remove('active');
                    });
                    
                    // If clicked item wasn't active, open it
                    if (!isActive) {
                        item.classList.add('active');
                    }
                });
            });
        });
        
        // Search functionality
        const searchInput = document.querySelector('.search-help input');
        const searchButton = document.querySelector('.search-help button');
        
        searchButton.addEventListener('click', performSearch);
        
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
        
        function performSearch() {
            const query = searchInput.value.trim().toLowerCase();
            if (query) {
                const faqItems = document.querySelectorAll('.faq-item');
                let found = false;
                
                faqItems.forEach(item => {
                    const question = item.querySelector('.faq-question h3').textContent.toLowerCase();
                    const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                    
                    if (question.includes(query) || answer.includes(query)) {
                        item.style.display = 'block';
                        item.style.backgroundColor = 'rgba(255, 107, 107, 0.1)';
                        found = true;
                        
                        // Open the matching FAQ item
                        faqItems.forEach(otherItem => {
                            otherItem.classList.remove('active');
                        });
                        item.classList.add('active');
                        
                        // Scroll to the found item
                        item.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                if (!found) {
                    alert('No results found for: ' + query);
                    // Reset display
                    faqItems.forEach(item => {
                        item.style.display = 'block';
                        item.style.backgroundColor = '';
                    });
                }
            } else {
                // Reset if search is empty
                const faqItems = document.querySelectorAll('.faq-item');
                faqItems.forEach(item => {
                    item.style.display = 'block';
                    item.style.backgroundColor = '';
                });
            }
        }
        
        // Scroll to section function
        function scrollToSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                // Close mobile menu if open
                if (nav && nav.classList.contains('active')) {
                    nav.classList.remove('active');
                    const icon = mobileMenuBtn.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
                
                window.scrollTo({
                    top: section.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        }
        
        // Back to top button
        const backToTopButton = document.createElement('button');
        backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
        backToTopButton.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--secondary);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 1000;
            box-shadow: var(--shadow);
            font-size: 20px;
            display: none;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        `;
        
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        backToTopButton.addEventListener('mouseenter', () => {
            backToTopButton.style.transform = 'scale(1.1)';
        });
        
        backToTopButton.addEventListener('mouseleave', () => {
            backToTopButton.style.transform = 'scale(1)';
        });
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 500) {
                backToTopButton.style.display = 'flex';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        document.body.appendChild(backToTopButton);
        
        // Print button
        const printButton = document.createElement('button');
        printButton.innerHTML = '<i class="fas fa-print"></i> Print Help Guide';
        printButton.style.cssText = `
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: var(--radius);
            cursor: pointer;
            z-index: 1000;
            box-shadow: var(--shadow);
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        `;
        
        printButton.addEventListener('click', () => {
            window.print();
        });
        
        printButton.addEventListener('mouseenter', () => {
            printButton.style.transform = 'scale(1.05)';
        });
        
        printButton.addEventListener('mouseleave', () => {
            printButton.style.transform = 'scale(1)';
        });
        
        document.body.appendChild(printButton);
        
        // Add print styles
        const printStyle = document.createElement('style');
        printStyle.textContent = `
            @media print {
                header, footer, button, .search-help,
                .mobile-menu-btn, .category-card {
                    display: none !important;
                }
                
                .help-section, .contact-support {
                    break-inside: avoid;
                    page-break-inside: avoid;
                }
                
                .faq-item .faq-answer {
                    max-height: none !important;
                    display: block !important;
                }
                
                .faq-item {
                    page-break-inside: avoid;
                }
                
                body {
                    background: white !important;
                    color: black !important;
                }
                
                .container {
                    max-width: 100% !important;
                    padding: 0 !important;
                }
            }
        `;
        document.head.appendChild(printStyle);
    </script>
</body>
</html>