<?php

namespace Database\Seeders;

use App\Models\Access;
use App\Models\Account;
use App\Models\Department;
use App\Models\Request;
use App\Models\RequestDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Signatory;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create departments
        $departments = [
            ['department_name' => 'Administration', 'department_description' => 'Main administrative department'],
            ['department_name' => 'IT', 'department_description' => 'Information Technology department'],
            ['department_name' => 'Finance', 'department_description' => 'Financial operations department'],
            ['department_name' => 'Procurement', 'department_description' => 'Purchasing and supply department'],
            ['department_name' => 'Supply', 'department_description' => 'Purchasing and supply department'],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }

        // Create access levels
        $accessLevels = [
            ['program_name' => 'System', 'access_level' => 'Full Access'],
            ['program_name' => 'System', 'access_level' => 'Read Only'],
            ['program_name' => 'System', 'access_level' => 'Approver'],
            ['program_name' => 'System', 'access_level' => 'Processor'],
            ['program_name' => 'System', 'access_level' => 'Write'],
        ];

        foreach ($accessLevels as $access) {
            Access::create($access);
        }

        // Create accounts
        $accounts = [
            ['account_title' => '13Th Month'],
            ['account_title' => 'Abn-Amro'],
            ['account_title' => 'Accounts Receivable'],
            ['account_title' => 'Advanced Bar Review'],
            ['account_title' => 'Aldr Book'],
            ['account_title' => 'Alf Mutual Fund'],
            ['account_title' => 'Alf Mutual Loan'],
            ['account_title' => 'Alfa Dues'],
            ['account_title' => 'Alumni'],
            ['account_title' => 'Alumni - Locker'],
            ['account_title' => 'Alumni Fee'],
            ['account_title' => 'Arellano Lecture Series'],
            ['account_title' => 'Athletic'],
            ['account_title' => 'Audio-Visual'],
            ['account_title' => 'Bank Charge'],
            ['account_title' => 'Bar Ops'],
            ['account_title' => 'Bar Ops Ticket'],
            ['account_title' => 'Bar-Ops Jacket/Tshrt'],
            ['account_title' => 'Booklet'],
            ['account_title' => 'Book-Magsalin'],
            ['account_title' => 'Books - Civil Law'],
            ['account_title' => 'Books - Criminal Law'],
            ['account_title' => 'Books - Labor Law'],
            ['account_title' => 'Books - Legal Ethics'],
            ['account_title' => 'Books - Mercantile'],
            ['account_title' => 'Books - Political Law'],
            ['account_title' => 'Books - Remedial Law'],
            ['account_title' => 'Books - Rex'],
            ['account_title' => 'Books - Taxation'],
            ['account_title' => 'Books-Gatdula'],
            ['account_title' => 'Books-Ladia'],
            ['account_title' => 'C-1 [Eligibility]'],
            ['account_title' => 'C-2 [Processing Fee]'],
            ['account_title' => 'Carpass'],
            ['account_title' => 'Case Book'],
            ['account_title' => 'Certification'],
            ['account_title' => 'Certified True Copy'],
            ['account_title' => 'Change Of Enrolment'],
            ['account_title' => 'China Trust'],
            ['account_title' => 'Choir'],
            ['account_title' => 'Choir Ticket'],
            ['account_title' => 'Christmas Outreach'],
            ['account_title' => 'Cola'],
            ['account_title' => 'Completion/Removal'],
            ['account_title' => 'Computer'],
            ['account_title' => 'Computer Equipment'],
            ['account_title' => 'Computer Supplies'],
            ['account_title' => 'Copy Of Grades'],
            ['account_title' => 'Developmental'],
            ['account_title' => 'Diploma'],
            ['account_title' => 'Dividend'],
            ['account_title' => 'Documentary Stamp'],
            ['account_title' => 'Donation'],
            ['account_title' => 'Dormitory - Deposit'],
            ['account_title' => 'Dormitory - Electric'],
            ['account_title' => 'Dormitory - Penalty'],
            ['account_title' => 'Dormitory Fee'],
            ['account_title' => 'Dormitory -Transient'],
            ['account_title' => 'Duplicate -Review Id'],
            ['account_title' => 'Duplicate-Library Id'],
            ['account_title' => 'Duplicate-Student Id'],
            ['account_title' => 'Ecc'],
            ['account_title' => 'Electrical Equipment'],
            ['account_title' => 'Entrance Fee'],
            ['account_title' => 'Evaluation Of Grades'],
            ['account_title' => 'Exam Proctor'],
            ['account_title' => 'Expanded Withholding Tax'],
            ['account_title' => 'Faculty Computer'],
            ['account_title' => 'Faculty Development'],
            ['account_title' => 'Fine - Exam. Permit'],
            ['account_title' => 'Fine - Reg. Form'],
            ['account_title' => 'Fine -Late Enrolment'],
            ['account_title' => 'Furniture And Fixture'],
            ['account_title' => 'General Charges'],
            ['account_title' => 'Graduation Fee'],
            ['account_title' => 'Graduation Picture'],
            ['account_title' => 'Guidance & Counseling'],
            ['account_title' => 'Health Insurance'],
            ['account_title' => 'Home Credit'],
            ['account_title' => 'Honorable Dismissal'],
            ['account_title' => 'Hotel Accommodation'],
            ['account_title' => 'Hsbc Loan'],
            ['account_title' => 'Id'],
            ['account_title' => 'Id - Reviewee'],
            ['account_title' => 'Id Penalty'],
            ['account_title' => 'Incentive'],
            ['account_title' => 'Interest - Tuition'],
            ['account_title' => 'Interest Income - Pldt'],
            ['account_title' => 'Interest P.N.'],
            ['account_title' => 'Law & Policy Review'],
            ['account_title' => 'Law Alumni Fee'],
            ['account_title' => 'Lawphil'],
            ['account_title' => 'Leasehold Improvement'],
            ['account_title' => 'Legal Aid Clinic'],
            ['account_title' => 'Library Bond'],
            ['account_title' => 'Library Books'],
            ['account_title' => 'Library Id'],
            ['account_title' => 'Library Id-Reviewee'],
            ['account_title' => 'Library Penalty'],
            ['account_title' => 'Light And Water'],
            ['account_title' => 'Mcle'],
            ['account_title' => 'Mcle Lecturer'],
            ['account_title' => 'Medical & Dental'],
            ['account_title' => 'Medicare'],
            ['account_title' => 'Merchandise'],
            ['account_title' => 'Misc Income'],
            ['account_title' => 'Office Supplies'],
            ['account_title' => 'Pag Ibig Loan'],
            ['account_title' => 'Pag-Ibig Fund'],
            ['account_title' => 'Party Ticket 2004'],
            ['account_title' => 'Pep Seminar'],
            ['account_title' => 'Per Diem'],
            ['account_title' => 'Pre-Week Bar Review'],
            ['account_title' => 'Privilege'],
            ['account_title' => 'Privilege - Ext.'],
            ['account_title' => 'Professional Fees'],
            ['account_title' => 'Publicity'],
            ['account_title' => 'Purple Notes'],
            ['account_title' => 'Refund Of Salary'],
            ['account_title' => 'Refund-Sc'],
            ['account_title' => 'Registration'],
            ['account_title' => 'Regular Bar Review'],
            ['account_title' => 'Reissuance-Diploma'],
            ['account_title' => 'Renew Privilege'],
            ['account_title' => 'Rental-Copier'],
            ['account_title' => 'Repairs'],
            ['account_title' => 'Retirement-Faculty'],
            ['account_title' => 'Retreat'],
            ['account_title' => 'Salary Advance'],
            ['account_title' => 'Salary Teaching'],
            ['account_title' => 'Salary-Admin'],
            ['account_title' => 'Sc - Cap'],
            ['account_title' => 'Sc - Concert Ticket 2006'],
            ['account_title' => 'Sc-Locker'],
            ['account_title' => 'Sc-Raffle Tickets'],
            ['account_title' => 'Service Income'],
            ['account_title' => 'Sickness Benefit'],
            ['account_title' => 'Ss Maternity Benefit'],
            ['account_title' => 'Sss Loan'],
            ['account_title' => 'Sss Premium'],
            ['account_title' => 'Sticker - Car Pass'],
            ['account_title' => 'Sticker - Student Id'],
            ['account_title' => 'Student Council'],
            ['account_title' => 'Student Development Fund'],
            ['account_title' => 'Student Id'],
            ['account_title' => 'Student Id-Sticker'],
            ['account_title' => 'Student Publication'],
            ['account_title' => 'Tax Refund'],
            ['account_title' => 'Taxes And Licenses'],
            ['account_title' => 'Tcad'],
            ['account_title' => 'Tel. & Telegraph'],
            ['account_title' => 'Transcript'],
            ['account_title' => 'Transportation'],
            ['account_title' => 'Tuition'],
            ['account_title' => 'Tuition - Executive'],
            ['account_title' => 'Tuition - Regular'],
            ['account_title' => 'Tuition Balance'],
            ['account_title' => 'Withholding Tax'],
            ['account_title' => 'Xerox/Photocopy'],
            ['account_title' => 'Yearbook'],
        ];


        foreach ($accounts as $account) {
            Account::create($account);
        }

        // Create signatories
        $signatories = [
            ['full_name' => 'John Smith', 'position' => 'Finance Director'],
            ['full_name' => 'Maria Garcia', 'position' => 'Procurement Manager'],
        ];

        foreach ($signatories as $signatory) {
            Signatory::create($signatory);
        }

        // Create users
        $users = [
        [
            'username' => 'admin',
            'first_name' => 'Super',
            'middle_name' => 'System',
            'last_name' => 'Admin',
            'email' => 'admin@itc.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'department_id' => Department::where('department_name', 'Administration')->first()->id,
            'access_id' => Access::where('access_level', 'Full Access')->first()->id,
        ],
        [
            'username' => 'executive',
            'first_name' => 'Executive',
            'last_name' => 'Director',
            'email' => 'executive@itc.com',
            'password' => Hash::make('password'),
            'role' => 'executive_director',
            'status' => 'active',
            'department_id' => Department::where('department_name', 'Administration')->first()->id,
            'access_id' => Access::where('access_level', 'Full Access')->first()->id,
        ],
        [
            'username' => 'director',
            'first_name' => 'Office',
            'last_name' => 'Director',
            'email' => 'director@itc.com',
            'password' => Hash::make('password'),
            'role' => 'department_head',
            'status' => 'active',
            'department_id' => Department::where('department_name', 'IT')->first()->id,
            'access_id' => Access::where('access_level', 'Approver')->first()->id,
        ],
        [
            'username' => 'staff',
            'first_name' => 'Office',
            'last_name' => 'Staff',
            'email' => 'staff@itc.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'status' => 'active',
            'department_id' => Department::where('department_name', 'IT')->first()->id,
            'access_id' => Access::where('access_level', 'Write')->first()->id,
        ],
        [
            'username' => 'custodian',
            'first_name' => 'Property',
            'last_name' => 'Custodian',
            'email' => 'custodian@itc.com',
            'password' => Hash::make('password'),
            'role' => 'property_custodian',
            'status' => 'active',
            'department_id' => Department::where('department_name', 'Supply')->first()->id,
            'access_id' => Access::where('access_level', 'Approver')->first()->id,
        ],
        [
            'username' => 'purchasing',
            'first_name' => 'Purchasing',
            'last_name' => 'Officer',
            'email' => 'purchasing@itc.com',
            'password' => Hash::make('password'),
            'role' => 'purchasing',
            'status' => 'active',
            'department_id' => Department::where('department_name', 'Supply')->first()->id,
            'access_id' => Access::where('access_level', 'Write')->first()->id,
        ],
    ];


        foreach ($users as $user) {
            User::create($user);
        }

        // Create sample requests
        $requests = [
            [
                'request_no' => 'REQ-2023-001',
                'request_date' => now()->subDays(5),
                'purpose' => 'Office supplies for new employees',
                'status' => 'approved',
                'department_id' => Department::where('department_name', 'Administration')->first()->id,
                'user_id' => User::where('username', 'admin')->first()->id,
            ],
            [
                'request_no' => 'REQ-2023-002',
                'request_date' => now()->subDays(3),
                'purpose' => 'New laptops for IT department',
                'status' => 'pending',
                'department_id' => Department::where('department_name', 'IT')->first()->id,
                'user_id' => User::where('username', 'director')->first()->id,
            ],
        ];

        foreach ($requests as $request) {
            $createdRequest = Request::create($request);

            // Add request details
            $items = $request['purpose'] === 'Office supplies for new employees' ? 
                [
                    ['quantity' => 5, 'unit' => 'box', 'item_description' => 'A4 Paper'],
                    ['quantity' => 10, 'unit' => 'pcs', 'item_description' => 'Ballpens'],
                ] : 
                [
                    ['quantity' => 3, 'unit' => 'unit', 'item_description' => 'Laptop i7 16GB RAM'],
                ];

            foreach ($items as $item) {
                RequestDetail::create([
                    'request_id' => $createdRequest->id,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'item_description' => $item['item_description'],
                ]);
            }
        }

        // Create sample purchase orders
        $purchaseOrders = [
            [
                'po_no' => 'PO-2023-001',
                'payee' => 'Office Supplies Inc.',
                'check_payable_to' => 'Check',
                'date' => now()->subDays(2),
                'amount' => 1250.50,
                'purpose' => 'Office supplies',
                'status' => 'approved',
                'remarks' => 'Urgent delivery needed',
                'user_id' => User::where('username', 'admin')->first()->id,
                'department_id' => Department::where('department_name', 'Administration')->first()->id,
                'account_id' => Account::where('account_title', 'Office Supplies')->first()->id,
            ],
        ];

        foreach ($purchaseOrders as $po) {
            $createdPO = PurchaseOrder::create($po);

            // Add PO details
            PurchaseOrderDetail::create([
                'po_id' => $createdPO->id,
                'quantity' => 5,
                'unit' => 'box',
                'item_description' => 'A4 Paper',
                'unit_price' => 200.00,
                'amount' => 1000.00,
            ]);

            PurchaseOrderDetail::create([
                'po_id' => $createdPO->id,
                'quantity' => 50,
                'unit' => 'pcs',
                'item_description' => 'Ballpens',
                'unit_price' => 5.01,
                'amount' => 250.50,
            ]);
        }

        // Create sample voucher
        $voucher = Voucher::create([
            'voucher_no' => 'V-2023-001',
            'voucher_date' => now()->subDay(),
            'issue_date' => now()->subDay(),
            'payment_date' => now(),
            'type' => 'Payment',
            'payee' => 'Office Supplies Inc.',
            'check_no' => 'CHK-001',
            'check_date' => now(),
            'check_amount' => 1250.50,
            'check_payable_to' => 'Office Supplies Inc.',
            'delivery_date' => now()->addDays(2),
            'purpose' => 'Payment for office supplies',
            'status' => 'paid',
            'user_id' => User::where('username', 'staff')->first()->id,
        ]);

        // Add voucher details
        VoucherDetail::create([
            'voucher_id' => $voucher->id,
            'amount' => 1250.50,
            'charging_tag' => 'ADM-001',
            'account_id' => Account::where('account_title', 'Office Supplies')->first()->id,
        ]);
    }
}