<?php
session_start();
include("header.php");
?>

<body class="bg-gray-50 flex flex-col min-h-screen">
    <div class="text-center pt-12 pb-6">
        <h1 class="text-4xl font-extrabold text-teal-700">Contact Us</h1>
        <p class="text-gray-500 mt-2 text-lg">Have questions? We'd love to hear from you.</p>
    </div>

    <div class="container mx-auto px-4 flex-grow flex items-start justify-center">
        <div
            class="w-full max-w-5xl bg-white rounded-lg shadow-2xl overflow-hidden flex flex-col md:flex-row border border-gray-100">

            <div class="w-full md:w-2/5 bg-blue-700 text-white p-8 flex flex-col justify-between">
                <div>
                    <h3 class="text-2xl font-bold mb-6">Get in Touch</h3>
                    <p class="text-gray-400 mb-8 text-base">Reach out to our support team for inquiries or technical
                        help.</p>

                    <div class="space-y-6">
                        <div class="flex items-start">
                            <span class="text-xl mr-3">📞</span>
                            <div>
                                <p class="font-bold text-lg uppercase text-gray-400">Call Us</p>
                                <p class="text-base">+60 15-666 5487</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <span class="text-xl mr-3">✉️</span>
                            <div>
                                <p class="font-bold text-lg uppercase text-gray-400">Email Support</p>
                                <ol class="list-decimal pl-5 text-base mt-1 space-y-1">
                                    <li class="hover:text-gray-300">olms@support.email.com</li>
                                    <li class="hover:text-gray-300">olms@example.support.com</li>
                                </ol>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <span class="text-xl mr-3">⏰</span>
                            <div>
                                <p class="font-bold text-lg uppercase text-gray-400">Operating Hours</p>
                                <div class="grid grid-cols-[auto_1fr] text-base mt-1">
                                    <p class="font-medium mr-2">Monday - Thursday:</p>
                                    <p>8:00 AM - 6:00 PM</p>

                                    <p class="font-medium mr-2">Friday:</p>
                                    <div>
                                        <p>8:00 AM - 12:00 PM</p>
                                        <p>3:00 PM - 6:00 PM</p>
                                    </div>

                                    <p class="font-medium mr-2">Saturday:</p>
                                    <p>8:00 AM - 2:00 PM</p>
                                </div>

                                <p class="text-base font-bold uppercase mt-3">Except Public Holiday</p>
                                <div class="text-base text-gray-400 mt-2">
                                    <p>NOTES:</p>
                                    <p>Response will be given as fast as possible during operating hours.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-gray-800">
                    <p class="text-base font-bold mb-3 text-white">Find Us On:</p>
                    <div class="flex space-x-3">

                        <a href="https://www.facebook.com/" target="blank"
                            class="w-8 h-8 rounded-md overflow-hidden hover:opacity-80 transition" title="Facebook">
                            <img src="<?php echo BASE_URL; ?>uploads/asset/FB.png" alt="Facebook"
                                class="w-full h-full object-cover">
                        </a>

                        <a href="https://www.youtube.com/" target="blank"
                            class="w-8 h-8 rounded-md overflow-hidden hover:opacity-80 transition" title="Youtube">
                            <img src="<?php echo BASE_URL; ?>uploads/asset/YT.png" alt="Youtube"
                                class="w-full h-full object-cover">
                        </a>

                        <a href="https://www.instagram.com/" target="blank"
                            class="w-8 h-8 rounded-md overflow-hidden hover:opacity-80 transition" title="Instagram">
                            <img src="<?php echo BASE_URL; ?>uploads/asset/IG.jpg" alt="Instagram"
                                class="w-full h-full object-cover">
                        </a>

                    </div>
                </div>
            </div>

            <div class="w-full md:w-3/5 p-8">
                <form action="process_contact.php" method="POST" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 text-base font-bold mb-2">Username</label>
                            <input
                                class="w-full bg-gray-50 border border-gray-300 rounded p-2 focus:outline-none focus:border-gray-900"
                                type="text" placeholder="Your Username" name="username">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-base font-bold mb-2">Email Address</label>
                            <input
                                class="w-full bg-gray-50 border border-gray-300 rounded p-2 focus:outline-none focus:border-gray-900"
                                type="email" placeholder="Your Email" name="email">
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-base font-bold mb-2">Subject</label>
                        <select
                            class="w-full bg-gray-50 border border-gray-300 rounded p-2 focus:outline-none focus:border-gray-900"
                            name="subject_type">
                            <option value="general">General Inquiry</option>
                            <option value="technical">Technical Issue (Login/Access)</option>
                            <option value="enrollment">Course Enrollment</option>
                            <option value="suggestion">Feedback / Suggestions</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-base font-bold mb-2">Message</label>
                        <textarea
                            class="w-full bg-gray-50 border border-gray-300 rounded p-2 focus:outline-none focus:border-gray-900"
                            rows="5" placeholder="Describe your issue..." name="message"></textarea>
                    </div>

                    <button
                        class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-6 rounded transition shadow-lg w-full md:w-auto"
                        type="submit">
                        Send Message
                    </button>
                </form>

                <div class="mt-10 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-3">🔥 Top 3 Frequently Asked Questions</h3>
                    <ul class="space-y-2 text-base text-gray-600 list-decimal pl-5">
                        <li class="hover:text-gray-900">How do I reset my password?</li>
                        <li class="hover:text-gray-900">What payment methods are accepted?</li>
                        <li class="hover:text-gray-900">Is this course available offline?</li>
                    </ul>
                    <p class="mt-4 text-base font-medium">
                        For more FAQ: <a href="<?php echo BASE_URL; ?>faq.php" class="text-blue-600 hover:underline">Click Here</a>
                    </p>
                </div>
            </div>
        </div>
    </div><br><br>

    <?php
    include("footer.php");
    ?>