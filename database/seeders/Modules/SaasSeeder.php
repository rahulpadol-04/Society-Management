<?php

declare(strict_types=1);

namespace Database\Seeders\Modules;

use App\Models\Blog;
use App\Models\CmsPage;
use App\Models\ContactInquiry;
use App\Models\PaymentGateway;
use App\Models\Society;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Society\SocietyRegistrationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SaasSeeder extends Seeder
{
    public function run(SocietyRegistrationService $registration): void
    {
        // ---- CMS Pages ----
        if (! CmsPage::where('slug', 'home')->exists()) {
            $pages = [
                [
                    'title'            => 'Home',
                    'slug'             => 'home',
                    'content'          => "Welcome to CommunityOS — the all-in-one society management platform.\n\nManage residents, facilities, complaints and billing from a single dashboard.",
                    'meta_title'       => 'CommunityOS — Society Management SaaS',
                    'meta_description' => 'Manage your housing society with CommunityOS.',
                    'status'           => 'published',
                    'published_at'     => now()->subDays(30),
                ],
                [
                    'title'            => 'About Us',
                    'slug'             => 'about',
                    'content'          => "CommunityOS was founded to simplify housing society operations across India.\n\nOur platform serves thousands of residents across hundreds of societies.",
                    'meta_title'       => 'About CommunityOS',
                    'meta_description' => 'Learn about the team behind CommunityOS.',
                    'status'           => 'published',
                    'published_at'     => now()->subDays(25),
                ],
                [
                    'title'            => 'Privacy Policy',
                    'slug'             => 'privacy-policy',
                    'content'          => "This Privacy Policy describes how CommunityOS collects, uses and shares information.\n\nWe take your privacy seriously and comply with applicable data protection laws.",
                    'meta_title'       => 'Privacy Policy — CommunityOS',
                    'meta_description' => 'CommunityOS privacy policy and data practices.',
                    'status'           => 'published',
                    'published_at'     => now()->subDays(20),
                ],
            ];

            foreach ($pages as $page) {
                CmsPage::create($page);
            }
        }

        // ---- Blog Posts ----
        if (! Blog::where('slug', 'introducing-communityos')->exists()) {
            $author = User::withoutGlobalScopes()
                ->where('email', 'super@communityos.io')
                ->first();

            $posts = [
                [
                    'title'        => 'Introducing CommunityOS — A New Era of Society Management',
                    'slug'         => 'introducing-communityos',
                    'excerpt'      => 'We\'re excited to launch CommunityOS, the platform that brings modern SaaS tools to housing societies of all sizes.',
                    'content'      => "Housing societies face complex challenges — from managing maintenance billing to resolving resident complaints.\n\nCommunityOS brings all of this under one roof, with an intuitive interface and powerful automation tools.\n\nKey features include multi-tower structure management, automated maintenance billing, visitor gate management and a built-in helpdesk.",
                    'author_id'    => $author?->id,
                    'category'     => 'Product',
                    'status'       => 'published',
                    'published_at' => now()->subDays(15),
                    'views'        => 234,
                ],
                [
                    'title'        => '5 Ways to Reduce Maintenance Payment Defaults',
                    'slug'         => '5-ways-reduce-maintenance-defaults',
                    'excerpt'      => 'Late maintenance payments are a common pain point for housing societies. Here are five proven strategies to improve collection rates.',
                    'content'      => "1. Send automated reminders before due dates.\n2. Offer multiple payment gateway options.\n3. Set up auto-billing via standing instructions.\n4. Apply transparent late fees with grace periods.\n5. Publish monthly collection dashboards for resident transparency.",
                    'author_id'    => $author?->id,
                    'category'     => 'Tips & Tricks',
                    'status'       => 'published',
                    'published_at' => now()->subDays(8),
                    'views'        => 189,
                ],
                [
                    'title'        => 'Case Study: Green Valley Residency Goes Paperless',
                    'slug'         => 'case-study-green-valley-paperless',
                    'excerpt'      => 'Learn how Green Valley Residency eliminated paper registers and cut administrative time by 70% using CommunityOS.',
                    'content'      => "Green Valley Residency, a 200-unit complex in Pune, struggled with manual registers for visitor logs, complaint tracking and maintenance billing.\n\nAfter adopting CommunityOS, the society digitised all workflows within two weeks.\n\nResults: 70% reduction in admin time, 95% on-time maintenance collection and zero paper forms.",
                    'author_id'    => $author?->id,
                    'category'     => 'Case Study',
                    'status'       => 'published',
                    'published_at' => now()->subDays(3),
                    'views'        => 412,
                ],
            ];

            foreach ($posts as $post) {
                Blog::create($post);
            }
        }

        // ---- Contact Inquiries ----
        if (ContactInquiry::count() === 0) {
            $inquiries = [
                [
                    'name'         => 'Ramesh Gupta',
                    'email'        => 'ramesh.gupta@example.com',
                    'phone'        => '+91-9876543210',
                    'subject'      => 'Pricing enquiry for 300-unit society',
                    'message'      => 'We have a large gated community of 300 units in Bangalore. Could you please share the enterprise pricing details and implementation timeline?',
                    'society_name' => 'Prestige Gardens',
                    'status'       => 'responded',
                    'notes'        => 'Sent enterprise brochure. Follow-up call scheduled.',
                    'created_at'   => now()->subDays(10),
                    'updated_at'   => now()->subDays(8),
                ],
                [
                    'name'         => 'Sunita Verma',
                    'email'        => 'sunita@silveroaks.co.in',
                    'phone'        => '+91-9123456780',
                    'subject'      => 'Demo request',
                    'message'      => 'We are currently using a spreadsheet-based system and would like to see a live demo of CommunityOS for our 80-unit society.',
                    'society_name' => 'Silver Oaks CHS',
                    'status'       => 'in_progress',
                    'notes'        => 'Demo call booked for next week.',
                    'created_at'   => now()->subDays(5),
                    'updated_at'   => now()->subDays(4),
                ],
                [
                    'name'         => 'Arjun Mehta',
                    'email'        => 'arjun.mehta@outlook.com',
                    'phone'        => null,
                    'subject'      => 'Integration with Tally ERP',
                    'message'      => 'Does your platform integrate with Tally ERP for accounting exports? Our accountant insists on this requirement.',
                    'society_name' => null,
                    'status'       => 'new',
                    'notes'        => null,
                    'created_at'   => now()->subDays(2),
                    'updated_at'   => now()->subDays(2),
                ],
                [
                    'name'         => 'Kavitha Nair',
                    'email'        => 'kavitha.nair@gmail.com',
                    'phone'        => '+91-9988776655',
                    'subject'      => null,
                    'message'      => 'I tried signing up but keep getting an error on the payment page. The plan looks good otherwise.',
                    'society_name' => 'Harmony Heights',
                    'status'       => 'closed',
                    'notes'        => 'Payment gateway issue resolved. User successfully onboarded.',
                    'created_at'   => now()->subDays(20),
                    'updated_at'   => now()->subDays(18),
                ],
            ];

            foreach ($inquiries as $inq) {
                ContactInquiry::create($inq);
            }
        }

        // ---- Payment Gateways ----
        if (PaymentGateway::count() === 0) {
            PaymentGateway::create([
                'name'        => 'Razorpay (Test)',
                'provider'    => 'razorpay',
                'mode'        => 'test',
                'is_active'   => true,
                'credentials' => [
                    'key_id'     => 'rzp_test_DEMO_KEY_ID',
                    'key_secret' => 'rzp_test_DEMO_SECRET',
                ],
            ]);

            PaymentGateway::create([
                'name'        => 'Stripe (Test)',
                'provider'    => 'stripe',
                'mode'        => 'test',
                'is_active'   => false,
                'credentials' => [
                    'publishable_key' => 'pk_test_DEMO',
                    'secret_key'      => 'sk_test_DEMO',
                ],
            ]);
        }

        // ---- Demo Societies (additional) ----
        $professionalPlan = SubscriptionPlan::where('slug', 'professional')->first();
        $enterprisePlan   = SubscriptionPlan::where('slug', 'enterprise')->first();

        if (! Society::where('slug', 'sunrise-towers')->exists() && $professionalPlan) {
            $registration->register(
                societyData: [
                    'name'  => 'Sunrise Towers',
                    'slug'  => 'sunrise-towers',
                    'city'  => 'Mumbai',
                    'state' => 'Maharashtra',
                    'email' => 'admin@sunrisetowers.test',
                    'phone' => '+91-2200000001',
                ],
                adminData: [
                    'name'     => 'Vikram Shah',
                    'email'    => 'admin@sunrisetowers.test',
                    'password' => 'Password@123',
                ],
                plan: $professionalPlan,
            );
        }

        if (! Society::where('slug', 'lakeside-enclave')->exists() && $enterprisePlan) {
            $registration->register(
                societyData: [
                    'name'  => 'Lakeside Enclave',
                    'slug'  => 'lakeside-enclave',
                    'city'  => 'Hyderabad',
                    'state' => 'Telangana',
                    'email' => 'admin@lakesideenclave.test',
                    'phone' => '+91-4000000002',
                ],
                adminData: [
                    'name'     => 'Deepa Reddy',
                    'email'    => 'admin@lakesideenclave.test',
                    'password' => 'Password@123',
                ],
                plan: $enterprisePlan,
            );
        }

        // ---- Subscription Invoices (revenue chart data) ----
        $societies = Society::whereIn('slug', ['green-valley', 'sunrise-towers', 'lakeside-enclave'])->get();

        foreach ($societies as $society) {
            $subscription = $society->subscriptions()->first();
            if (! $subscription) {
                continue;
            }

            // Skip if invoices already exist for this society
            if (SubscriptionInvoice::where('society_id', $society->id)->exists()) {
                continue;
            }

            $amount = (float) ($subscription->amount ?? 4999);
            $tax    = round($amount * 0.18, 2);
            $total  = $amount + $tax;

            // Seed invoices for the past 6 months
            for ($i = 5; $i >= 0; $i--) {
                $paidAt = now()->subMonths($i)->startOfMonth()->addDays(rand(1, 5));
                $invoiceNum = 'INV-'.$society->id.'-'.str_pad((string)(6 - $i), 3, '0', STR_PAD_LEFT);

                SubscriptionInvoice::create([
                    'society_id'       => $society->id,
                    'subscription_id'  => $subscription->id,
                    'invoice_number'   => $invoiceNum,
                    'amount'           => $amount,
                    'tax'              => $tax,
                    'total'            => $total,
                    'currency'         => 'INR',
                    'status'           => 'paid',
                    'gateway'          => 'razorpay',
                    'gateway_payment_id' => 'pay_demo_'.strtolower($society->slug).'_'.($i + 1),
                    'paid_at'          => $paidAt,
                    'created_at'       => $paidAt,
                    'updated_at'       => $paidAt,
                ]);
            }
        }

        $this->command?->info('SaasSeeder: CMS pages, blog posts, inquiries, payment gateways and revenue invoices seeded.');
    }
}
