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
            ['account_title' => 'Aircon'],
            ['account_title' => 'Alumni Fee'],
            ['account_title' => 'Athletic'],
            ['account_title' => 'Audio Visual'],
            ['account_title' => 'Bank Charge'],
            ['account_title' => 'Bar-Ops'],
            ['account_title' => 'Board Scholarship'],
            ['account_title' => 'Booklet'],
            ['account_title' => 'Book-Magsalin'],
            ['account_title' => 'Books-Ladia'],
            ['account_title' => 'Books-Political Law'],
            ['account_title' => 'C1'],
            ['account_title' => 'Cash In Bank - BDO'],
            ['account_title' => 'Cash In Bank - BPI'],
            ['account_title' => 'Cash In Bank - Security Bank'],
            ['account_title' => 'Certification'],
            ['account_title' => 'Certified True Copy'],
            ['account_title' => 'Change Of Enrollment'],
            ['account_title' => 'CLEP Filing Fee'],
            ['account_title' => 'Commencement'],
            ['account_title' => 'Completion/Removal'],
            ['account_title' => 'Computer Equipment'],
            ['account_title' => 'Computer Software'],
            ['account_title' => 'Computer Supplies'],
            ['account_title' => 'Developmental'],
            ['account_title' => 'Diploma'],
            ['account_title' => 'Documentary Stamp'],
            ['account_title' => 'Drop Fee'],
            ['account_title' => 'Due From'],
            ['account_title' => 'Duplicate - Library ID'],
            ['account_title' => 'Duplicate - Student ID'],
            ['account_title' => 'ECC'],
            ['account_title' => 'Electrical Equipment'],
            ['account_title' => 'Electrical Supplies'],
            ['account_title' => 'Entrance Fee'],
            ['account_title' => 'EWT'],
            ['account_title' => 'Faculty Development'],
            ['account_title' => 'Financial Assistance'],
            ['account_title' => 'Fine - Exam Permit'],
            ['account_title' => 'Fire Insurance'],
            ['account_title' => 'Furniture & Fixtures'],
            ['account_title' => 'General Charges'],
            ['account_title' => 'Graduation Picture'],
            ['account_title' => 'Guidance And Counseling'],
            ['account_title' => 'Health Care'],
            ['account_title' => 'Honorable Dismissal'],
            ['account_title' => 'Honorarium'],
            ['account_title' => 'ID'],
            ['account_title' => 'ID Lace'],
            ['account_title' => 'ID Students'],
            ['account_title' => 'Interest Income'],
            ['account_title' => 'Investment'],
            ['account_title' => 'Janitorial Supply'],
            ['account_title' => 'JD Alumni'],
            ['account_title' => 'JD Conferment'],
            ['account_title' => 'Law And Policy Review'],
            ['account_title' => 'Lawphil'],
            ['account_title' => 'Leasehold Improvement'],
            ['account_title' => 'Legal Aid Clinic'],
            ['account_title' => 'Library'],
            ['account_title' => 'Library Bond'],
            ['account_title' => 'Library Books'],
            ['account_title' => 'Library ID'],
            ['account_title' => 'Library Penalty'],
            ['account_title' => 'Light & Water'],
            ['account_title' => 'MCLE'],
            ['account_title' => 'Meal'],
            ['account_title' => 'Medical & Dental'],
            ['account_title' => 'Miscellaneous Expenses'],
            ['account_title' => 'Miscellaneous Fee'],
            ['account_title' => 'Notarial Expense'],
            ['account_title' => 'Notarial Fee'],
            ['account_title' => 'Office Equipment'],
            ['account_title' => 'Office Supplies'],
            ['account_title' => 'Office Uniform'],
            ['account_title' => 'Other Income'],
            ['account_title' => 'Pag-Ibig Fund'],
            ['account_title' => 'Pag-Ibig Loan'],
            ['account_title' => 'Per Diem'],
            ['account_title' => 'Philhealth Premium'],
            ['account_title' => 'Postage Fee'],
            ['account_title' => 'Prebar Review'],
            ['account_title' => 'Pre-Week Bar Review'],
            ['account_title' => 'Prior Year Adjustment'],
            ['account_title' => 'Professional Fees'],
            ['account_title' => 'Publicity'],
            ['account_title' => 'Registration'],
            ['account_title' => 'Regular Bar Review'],
            ['account_title' => 'Repairs'],
            ['account_title' => 'Representation'],
            ['account_title' => 'Retirement'],
            ['account_title' => 'Rice Allowance'],
            ['account_title' => 'Salary Admin'],
            ['account_title' => 'Salary Advance'],
            ['account_title' => 'Salary Teaching'],
            ['account_title' => 'SSS Loan'],
            ['account_title' => 'SSS Maternity Benefit'],
            ['account_title' => 'SSS Premium'],
            ['account_title' => 'Student Council'],
            ['account_title' => 'Student Development Fund'],
            ['account_title' => 'Student ID'],
            ['account_title' => 'Student Publication'],
            ['account_title' => 'Tax Deficiency'],
            ['account_title' => 'Tax Refund'],
            ['account_title' => 'Taxes & License'],
            ['account_title' => 'TCADD Fee'],
            ['account_title' => 'Tel. & Telegraph'],
            ['account_title' => 'Test Booklet'],
            ['account_title' => 'Transcript'],
            ['account_title' => 'Transportation'],
            ['account_title' => 'Tuition Fee'],
            ['account_title' => 'Vehicle'],
            ['account_title' => 'Withholding Tax'],
            ['account_title' => 'Yearbook'],
        ];

        foreach ($accounts as $account) {
            Account::create($account);
        }

        // Create signatories
        $signatories = [
            ['full_name' => 'Atty. Gabriel P. Delapeña', 'position' => 'Executive Director'],
            ['full_name' => 'Ma. Jasmin P. Horlina', 'position' => 'Director, Accounting'],
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
        [
            'username' => 'accounting',
            'first_name' => 'Accounting',
            'last_name' => 'Officer',
            'email' => 'accounting@itc.com',
            'password' => Hash::make('password'),
            'role' => 'accounting',
            'status' => 'active',
            'department_id' => Department::where('department_name', 'Finance')->first()->id,
            'access_id' => Access::where('access_level', 'Approver')->first()->id,
        ],
    ];


        foreach ($users as $user) {
            User::create($user);
        }

        // Create sample requests
        $requests = [
            [
                'request_no' => 'REQ-2023-001',
                'request_date' => now()->subDays(10),
                'purpose' => 'Office supplies for new employees',
                'status' => 'approved',
                'department_id' => Department::where('department_name', 'Administration')->first()->id,
                'user_id' => User::where('username', 'admin')->first()->id,
            ],
            [
                'request_no' => 'REQ-2023-002',
                'request_date' => now()->subDays(9),
                'purpose' => 'New laptops for IT department',
                'status' => 'pending',
                'department_id' => Department::where('department_name', 'IT')->first()->id,
                'user_id' => User::where('username', 'director')->first()->id,
            ],
            [
                'request_no' => 'REQ-2023-003',
                'request_date' => now()->subDays(8),
                'purpose' => 'Maintenance tools',
                'status' => 'approved',
                'department_id' => Department::where('department_name', 'Maintenance')->first()->id,
                'user_id' => User::where('username', 'maintenance')->first()->id,
            ],
            [
                'request_no' => 'REQ-2023-004',
                'request_date' => now()->subDays(7),
                'purpose' => 'Medical supplies',
                'status' => 'approved',
                'department_id' => Department::where('department_name', 'Health')->first()->id,
                'user_id' => User::where('username', 'nurse')->first()->id,
            ],
            [
                'request_no' => 'REQ-2023-005',
                'request_date' => now()->subDays(6),
                'purpose' => 'Classroom equipment',
                'status' => 'approved',
                'department_id' => Department::where('department_name', 'Education')->first()->id,
                'user_id' => User::where('username', 'teacher')->first()->id,
            ],
        ];

        foreach ($requests as $request) {
            $createdRequest = Request::create($request);

            // Generate 5–10 items for each request
            $itemCount = rand(5, 10);
            for ($i = 0; $i < $itemCount; $i++) {
                RequestDetail::create([
                    'request_id' => $createdRequest->id,
                    'quantity' => rand(1, 10),
                    'unit' => 'pcs',
                    'item_description' => 'Item ' . chr(65 + $i), // A, B, C...
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