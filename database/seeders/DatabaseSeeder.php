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
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }

        // Create access levels
        $accessLevels = [
            ['program_name' => 'System', 'access_level' => 'Full Access'],
            ['program_name' => 'System', 'access_level' => 'Read Only'],
            ['program_name' => 'Procurement', 'access_level' => 'Approver'],
            ['program_name' => 'Finance', 'access_level' => 'Processor'],
        ];

        foreach ($accessLevels as $access) {
            Access::create($access);
        }

        // Create accounts
        $accounts = [
            ['account_title' => 'Office Supplies'],
            ['account_title' => 'Equipment'],
            ['account_title' => 'Travel Expenses'],
            ['account_title' => 'Training'],
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
                'username' => 'superadmin',
                'first_name' => 'Super',
                'middle_name' => 'System',
                'last_name' => 'Admin',
                'email' => 'admin@itc.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'status' => 'active',
                'department_id' => Department::where('department_name', 'Administration')->first()->id,
                'access_id' => Access::where('access_level', 'Full Access')->first()->id,
            ],
            [
                'username' => 'itmanager',
                'first_name' => 'IT',
                'middle_name' => 'Department',
                'last_name' => 'Manager',
                'email' => 'itmanager@itc.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'department_id' => Department::where('department_name', 'IT')->first()->id,
                'access_id' => Access::where('access_level', 'Full Access')->first()->id,
            ],
            [
                'username' => 'finance1',
                'first_name' => 'Finance',
                'middle_name' => 'Department',
                'last_name' => 'Officer',
                'email' => 'finance@itc.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'status' => 'active',
                'department_id' => Department::where('department_name', 'Finance')->first()->id,
                'access_id' => Access::where('access_level', 'Processor')->first()->id,
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
                'user_id' => User::where('username', 'superadmin')->first()->id,
            ],
            [
                'request_no' => 'REQ-2023-002',
                'request_date' => now()->subDays(3),
                'purpose' => 'New laptops for IT department',
                'status' => 'pending',
                'department_id' => Department::where('department_name', 'IT')->first()->id,
                'user_id' => User::where('username', 'itmanager')->first()->id,
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
                'user_id' => User::where('username', 'superadmin')->first()->id,
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
            'user_id' => User::where('username', 'finance1')->first()->id,
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