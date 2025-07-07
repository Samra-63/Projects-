<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Display the help and support page.
     */
    public function index()
    {
        return view('help.index', [
            'faqs' => [
                [
                    'question' => 'How do I reset my password?',
                    'answer' => 'You can reset your password by clicking on your profile menu and selecting "Change Password". Follow the instructions to set a new password.'
                ],
                [
                    'question' => 'How do I mark attendance?',
                    'answer' => 'Navigate to the Attendance section from the sidebar menu. Click on "Mark Attendance" and follow the prompts.'
                ],
                [
                    'question' => 'How do I view my salary slip?',
                    'answer' => 'Go to the Salaries section and click on your salary record. You can view and download your salary slip from there.'
                ],
                [
                    'question' => 'How do I apply for leave?',
                    'answer' => 'Visit the Leave Requests section, click on "New Request", fill in the required details, and submit your application.'
                ],
            ],
            'support' => [
                'email' => 'support@payroll.com',
                'phone' => '+92 300 1234567',
                'hours' => '9:00 AM - 5:00 PM (PKT), Monday - Friday'
            ]
        ]);
    }
} 