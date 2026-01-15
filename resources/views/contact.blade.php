@extends('layouts.public')

@section('title', 'Contact Us')

@section('content')
<!-- Hero Section -->
<section class="hero-gradient text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">Get in Touch</h1>
            <p class="text-xl md:text-2xl mb-8 text-gray-100">
                We'd love to hear from you. Send us a message and we'll respond as soon as possible.
            </p>
        </div>
    </div>
</section>

<!-- Contact Content -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Contact Form -->
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Send us a Message</h2>
                <form class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Your Name *</label>
                            <input type="text" id="name" name="name" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                        <input type="text" id="subject" name="subject" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                        <textarea id="message" name="message" rows="6" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    
                    <div>
                        <label for="project-type" class="block text-sm font-medium text-gray-700 mb-2">Project Type</label>
                        <select id="project-type" name="project-type" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select a project type</option>
                            <option value="web-development">Web Development</option>
                            <option value="mobile-development">Mobile Development</option>
                            <option value="desktop-applications">Desktop Applications</option>
                            <option value="data-science">Data Science</option>
                            <option value="devops">DevOps</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="newsletter" name="newsletter" class="mr-2">
                        <label for="newsletter" class="text-sm text-gray-600">
                            I'd like to receive updates about new features and releases
                        </label>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-indigo-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>Send Message
                    </button>
                </form>
            </div>
            
            <!-- Contact Information -->
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Contact Information</h2>
                
                <div class="space-y-8">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-map-marker-alt text-indigo-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Office Address</h3>
                            <p class="text-gray-600">
                                123 Tech Street<br>
                                Sofia Oblast<br>
                                Bulgaria
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-phone text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Phone Number</h3>
                            <p class="text-gray-600">
                                Main: +359 895457247<br>
                                Support: +359 884686449
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-envelope text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Email Addresses</h3>
                            <p class="text-gray-600">
                                General: info@projectmanager.com<br>
                                Support: support@projectmanager.com<br>
                                Sales: sales@projectmanager.com
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-purple-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Business Hours</h3>
                            <p class="text-gray-600">
                                Monday - Friday: 9:00 AM - 6:00 PM PST<br>
                                Saturday: 10:00 AM - 4:00 PM PST<br>
                                Sunday: Closed
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media -->
                <div class="mt-12">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Follow Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-blue-600 text-white rounded-lg flex items-center justify-center hover:bg-blue-700 transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-blue-700 text-white rounded-lg flex items-center justify-center hover:bg-blue-800 transition-colors">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 text-white rounded-lg flex items-center justify-center hover:bg-gray-900 transition-colors">
                            <i class="fab fa-github"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gradient-to-br from-purple-600 to-pink-600 text-white rounded-lg flex items-center justify-center hover:from-purple-700 hover:to-pink-700 transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
            <p class="text-lg text-gray-600">Quick answers to common questions</p>
        </div>
        
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <button class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 focus:outline-none">
                    <span class="font-semibold text-gray-900">How do I get started with the project management system?</span>
                    <i class="fas fa-chevron-down text-gray-500"></i>
                </button>
                <div class="px-6 py-4 border-t">
                    <p class="text-gray-600">Simply click on the "Admin Panel" button to access the management interface. From there, you can create projects, add categories, and manage technologies.</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <button class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 focus:outline-none">
                    <span class="font-semibold text-gray-900">Can I customize the project categories?</span>
                    <i class="fas fa-chevron-down text-gray-500"></i>
                </button>
                <div class="px-6 py-4 border-t">
                    <p class="text-gray-600">Yes! The admin panel allows you to create, edit, and delete project categories to match your specific needs.</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <button class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 focus:outline-none">
                    <span class="font-semibold text-gray-900">Is there a limit to the number of projects I can create?</span>
                    <i class="fas fa-chevron-down text-gray-500"></i>
                </button>
                <div class="px-6 py-4 border-t">
                    <p class="text-gray-600">No, there's no limit to the number of projects you can create. The system is designed to scale with your needs.</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <button class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 focus:outline-none">
                    <span class="font-semibold text-gray-900">What file types are supported for project attachments?</span>
                    <i class="fas fa-chevron-down text-gray-500"></i>
                </button>
                <div class="px-6 py-4 border-t">
                    <p class="text-gray-600">We support PDF, DOC, DOCX, TXT, JPG, JPEG, PNG, and GIF files with a maximum size of 10MB per file.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
