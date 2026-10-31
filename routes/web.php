<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/',      'HomeController@index');
Route::get('backup', 'backupController@db_backup');

Route::get('/run-migrations', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return "Migrations executed successfully! You can now use the website.";
});

Route::get('/clear-system-cache', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
    
    $bad_sales = \App\Sale::where('total_payable', '>', 1000000)->get();
    $html = "<h1>Success! Cache Cleared.</h1>";
    if($bad_sales->count() > 0) {
        $html .= "<h2>WARNING: Found Huge Invoices in Database!</h2>";
        $html .= "<ul>";
        foreach($bad_sales as $sale) {
            $html .= "<li>Invoice ID: {$sale->invoiceId} (Database ID: {$sale->id}) | Date: {$sale->sale_date} | Total Payable: {$sale->total_payable}</li>";
        }
        $html .= "</ul>";
        $html .= "<p style='color:red;'><b>This is why your report is showing Billions!</b> Someone entered a mobile number or wrong amount in these invoices.</p>";
    } else {
        $html .= "<p>No huge invoices found. If the report is still broken, there is another issue.</p>";
    }
    
    return $html;
});

Route::get('/make-project-fresh', function () {
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    $tables = [
        'sales', 'sales_details', 'purchases', 'purchase_details', 
        'transactions', 'balances', 'product_stocks', 'product_serials', 
        'sale_returns', 'sale_return_details', 'purchase_returns', 'purchase_return_details',
        'due_collections', 'customer_collections', 'account_reviews', 
        'stock_transfers', 'stock_transfer_details', 'product_damages',
        'wastages'
    ];

    foreach ($tables as $table) {
        \Illuminate\Support\Facades\DB::table($table)->truncate();
    }

    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    return "<h1>Success!</h1><p>All Sales, Purchases, Stocks, and Transactions have been wiped clean. The project is now FRESH.</p>";
});

Route::get('/make-project-super-fresh', function () {
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    $tables = [
        'sales', 'sales_details', 'purchases', 'purchase_details', 
        'transactions', 'balances', 'product_stocks', 'product_serials', 
        'sale_returns', 'sale_return_details', 'purchase_returns', 'purchase_return_details',
        'due_collections', 'customer_collections', 'account_reviews', 
        'stock_transfers', 'stock_transfer_details', 'product_damages',
        'wastages', 'products', 'customers', 'suppliers'
    ];

    foreach ($tables as $table) {
        \Illuminate\Support\Facades\DB::table($table)->truncate();
    }

    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    return "<h1>Success!</h1><p>EVERYTHING (Transactions + Products + Customers + Suppliers) has been wiped clean. The project is completely FRESH.</p>";
});

Route::get('/fix-permission', function () {
    $paths = [
        storage_path(),
        storage_path('logs'),
        storage_path('framework'),
        storage_path('framework/views'),
        storage_path('framework/sessions'),
        storage_path('framework/cache'),
        base_path('bootstrap/cache'),
    ];
    $output = [];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            @chmod($path, 0777);
            $output[] = "Chmod 0777 on: " . $path;
        }
    }
    foreach (glob(storage_path('logs/*.log')) as $logFile) {
        @chmod($logFile, 0777);
        $output[] = "Chmod 0777 on: " . basename($logFile);
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        $output[] = "Caches cleared successfully!";
    } catch (\Throwable $e) {
        $output[] = "Cache clear note: " . $e->getMessage();
    }

    return "<h1 style='color:green;'>Storage Permissions Fixed Successfully!</h1><pre>" . implode("\n", $output) . "</pre>";
});

Route::get('/fix-negative-stock', function () {
    \Illuminate\Support\Facades\DB::table('product_stocks')
        ->where('available_quantity', '<', 0)
        ->update(['available_quantity' => 0]);
    return "<h1>Success!</h1><p>All negative stocks have been reset to 0.</p>";
});

Route::get('/adjust-cash', function(\Illuminate\Http\Request $request) {
    if (!$request->has('actual_amount')) {
        return "<h1>Cash Adjustment</h1>
                <p>Please provide your actual physical cash amount in the URL.</p>
                <p>Example: <b>localhost:8000/adjust-cash?actual_amount=2000</b></p>";
    }

    $actualAmount = (float) $request->actual_amount;
    
    $companyId = companyId();
    if (!$companyId) {
        return "You must be logged in to a company to adjust cash!";
    }

    // Find Cash Account for the current company
    $cashAccount = \App\Account::where('fk_company_id', $companyId)
                               ->where(function($q) {
                                   $q->where('account_name', 'Cash')
                                     ->orWhere('account_name', 'Cash Account')
                                     ->orWhere('account_name', 'like', '%Cash%');
                               })
                               ->first();
                               
    if (!$cashAccount) {
        return "Cash account not found for your company! Please make sure your cash account name contains the word 'Cash'.";
    }

    // Get current cash balance
    $currentBalance = \Illuminate\Support\Facades\DB::table('transactions')
        ->where('fk_account_id', $cashAccount->id)
        ->sum('amount');

    if (round($currentBalance, 2) == round($actualAmount, 2)) {
        return "<h1>No Adjustment Needed!</h1><p>Your cash balance is already {$actualAmount} TK.</p>";
    }

    // Calculate difference
    $difference = $actualAmount - $currentBalance;

    // Create adjustment transaction
    $transaction = new \App\Transaction();
    $transaction->fk_account_id = $cashAccount->id;
    $transaction->fk_company_id = $companyId;
    $transaction->date = date('Y-m-d');
    $transaction->amount = $difference;
    $transaction->transactionable_type = 'App\Account';
    $transaction->transactionable_id = $cashAccount->id;
    $transaction->save();

    return "<h1>Cash Adjusted Successfully!</h1>
            <ul>
                <li>Previous Balance: <b>{$currentBalance} TK</b></li>
                <li>Adjustment Made: <b>{$difference} TK</b></li>
                <li>New Actual Balance: <b>{$actualAmount} TK</b></li>
            </ul>
            <p>Please check your dashboard now. It should show EXACTLY {$actualAmount} TK.</p>";
});



Auth::routes();

Route::group(['middleware' => ['auth', 'perm']], function(){

    Route::get('/home',                     'HomeController@index')->name('home');
    
    Route::get('/debug-toggle', function () {
        $envPath = base_path('.env');
        $currentEnv = \Illuminate\Support\Facades\File::get($envPath);
 
        if (config('app.debug') === false) {
            //  Turn debug on and set APP_ENV to local
            try {
                \Illuminate\Support\Facades\Artisan::call('debug on');
            } catch (\Exception $e) {}
            
            $newEnv = preg_replace(
                ['/APP_ENV=production/', '/APP_DEBUG=false/'],
                ['APP_ENV=local', 'APP_DEBUG=true'],
                $currentEnv
            );
        } else {
            // Turn debug off and set APP_ENV to production
            try {
                \Illuminate\Support\Facades\Artisan::call('debug off');
            } catch (\Exception $e) {}
            
            $newEnv = preg_replace(
                ['/APP_ENV=local/', '/APP_DEBUG=true/'],
                ['APP_ENV=production', 'APP_DEBUG=false'],
                $currentEnv
            );
        }
 
        // Save the updated .env file
        \Illuminate\Support\Facades\File::put($envPath, $newEnv);
 
        // Clear caches and configurations
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
 
        return redirect()->back()->with('message', 'APP_ENV and APP_DEBUG updated successfully');
    })->middleware('auth');
    
    Route::get('/supplier-dues',             'SupplierController@supplierDues')->name('supplier.dues');
    Route::post('dashboard/income_expense', 'HomeController@income_expense');



    Route::group(['middleware' => 'system_admin'], function(){
        Route::resource('packages',             'PackageController');
        Route::resource('company_packages',     'CompanyPackageController');
        Route::resource('software_payments',    'SoftwarePaymentController');
        Route::get('software_payments/invoice/{id}', 'SoftwarePaymentController@invoice');

        // Route::resource('smart-soft-payments', SoftPaymentController::class);
        // Route::get('smart-soft-payment/alert', [SoftPaymentController::class, 'ajaxAlert'])->name('soft-payments.alert');

        // bKash Payment Routes
        Route::post('/payment/bkash/create', 'BkashPaymentController@createPayment')->name('bkash.create');
        Route::get('/payment/bkash/callback', 'BkashPaymentController@callback')->name('bkash.callback');

       


       
    });
    Route::post('software_payments/paid',            'SoftwarePaymentController@paid');


    // <!-- sms related route -->
    Route::resource('sms-settings',                     'SmsSettingController');
    Route::resource('sms-apis',                         'SmsApiController');
    Route::resource('smsGroup',                         'SmsGroupController');
    Route::get('sms/send',                                   'SmsSettingController@sendSms')->name('send.sms');
    Route::post('sms-number',                                'SmsSettingController@smsNumbers')->name('sms-numbers');
    Route::post('sms-sending',                               'SmsSettingController@smsSending')->name('sms-sending');
    Route::post('all-sms-sending',                           'SmsSettingController@allsmsSending')->name('all-sms-sending');

    // <!-- sms Ajax route -->
    Route::get('active-sms-api',                   'SmsSettingController@getActiveSmsApi')->name('active.sms.api');
    // Route::get('available-sms-balance',            'SMSController@getAvailableSmsBalance')->name('get.available.sms.balance');
    // Route::get('update-available-sms-balance',     'SMSController@updateAvailablveSmsBalance')->name('update.sms.balance');
    // Route::get('ajax-sms-send-by-controller',      'SMSController@sendFromHttpGuzzle')->name('ajax.sms.send');
//    main route
    Route::get('ajax-sms-send-via-controller',     'SmsSendController@sendSmsViaHttpGuzzle')->name('ajax.send.sms');
//

    Route::get('groups/show/{id}',                 'GroupController@show');
    Route::resource('groups',                 'GroupController');

    //USER Profile
    Route::get('myProfile',                        'ProfileController@index')->middleware('user_role');


    //<settings>
    Route::get('categories/get_json_list',         'Product\CategoryController@get_json_list');
    Route::resource('settings/systems',       'GlobalSettingController');

    // <!-- accounta  -->
    Route::group(['prefix' => 'account'], function () {
        // <!-- setup  -->
        Route::group(['prefix' => 'setup'], function () {
            Route::resource('accounts',               'AccountController');
            Route::resource('gl-accounts',            'GlAccountController');
            Route::resource('accounts-charts',        'AccountChartController');
            Route::resource('payments-method',        'PaymentController');
            Route::resource('parties',                'PartyController');
            Route::resource('assets',                 'AssetController');
            Route::resource('liabilities',            'LiabilityController');
            Route::post('liabilities/payment',        'LiabilityController@payment')->name('liabilities.payment');
        });

        // <!-- voucher  -->
        Route::group(['prefix' => 'voucher'], function () {
            Route::resource('payments',               'MakePaymentController');
            Route::resource('fund-transfers',         'FundTransferController');
            Route::resource('asset-purchases',        'AssetPurchaseController');
            Route::resource('liability-purchases',    'LiabilityPurchaseController');
        });

    });
    Route::get('accounts/charts/status/{id}',              'AccountChartController@status')->name('charts.status');
    Route::get('accounts/payments-method/status/{id}',     'PaymentController@status')->name('payment.status');
    Route::get('accounts/status/{id}',                     'AccountController@status')->name('account.status');




    Route::group(['prefix' => 'user'], function () {
        Route::resource('users',                      'UserController');
        Route::get('company-assign',                  'UserController@companyAssign')->name('users.company_assign');
        Route::post('company-assign',                 'UserController@companyAssignStore')->name('users.company_assign.store');
        Route::post('company-switch',                 'UserController@companySwitch')->name('users.company_switch');
        Route::get('user-status-change/{user}',            'UserController@changeStatus')->name('users.status');
        Route::post('change-password',                     'UserController@changePassword')->name('change.user.password');
        Route::resource('roles',                      'RoleController');
        Route::resource('modules',                    'ModuleController');
        Route::resource('permissions',                'PermissionController');
        Route::get('permission-assigns/{id}',              'RolePermissionController@permissionAssign')->name('permissions.assign');
        Route::post('permission-assigns/{id}',             'RolePermissionController@permissionAssignStore')->name('permissions.assign.store');
    });

    //    Route::post('users/role_permissions/assign_rp', 'RolePermissionController@assign_rp');
    //    Route::post('users/role_permissions/change', 'RolePermissionController@change');
    //    Route::get('users/role_permissions/get_id', 'RolePermissionController@get_id_by_role_permission');
    //    Route::resource('users/role_permissions', 'RolePermissionController');
    //    Route::resource('people/billers', 'BillerController');

    Route::group(['prefix' => 'people'], function () {
        // supplier
        Route::resource('suppliers',                   'SupplierController');
        Route::get('upload-suppliers',                      'SupplierController@uploadSuppliers')->name('upload-suppliers.create');
        Route::post('upload-suppliers',                     'SupplierController@uploadSuppliersStore')->name('upload-suppliers.store');
        Route::get('supplier-confirm-list',                 'SupplierController@supplierConfirmList')->name('supplier.confirm.list');
        Route::post('supplier-confirm-list',                'SupplierController@supplierConfirmListStore')->name('supplier-confirm-list.store');
        Route::delete('supplier-confirm-list-delete/{id}',  'SupplierController@confirmSupplierDelete')->name('confirm-list-supplier.delete');
        Route::get('supplier-confirm-list-delete-all',      'SupplierController@confirmSupplierDeleteAll')->name('confirm-list-all-supplier.delete');

        // customers
        Route::resource('customers',                        'CustomerController');
        Route::get('upload-customers',                      'CustomerController@uploadCustomers')->name('upload-customers.create');
        Route::post('upload-customers',                     'CustomerController@uploadCustomersStore')->name('upload-customers.store');
        Route::get('get-last-added-customer',               'CustomerController@getLastAddedCustomer');


        Route::get('customer-confirm-list',                 'CustomerController@customerConfirmList')->name('customer-confirm-list.index');
        Route::post('customer-confirm-list',                'CustomerController@customerConfirmListStore')->name('customer-confirm-list.store');

        Route::delete('customer-confirm-list-delete/{id}',  'CustomerController@confirmCustomerDelete')->name('confirm-list-customers.delete');
        Route::get('customer-confirm-list-delete-all',      'CustomerController@confirmCustomerDeleteAll')->name('confirm-list-all-customers.delete');

    });


    Route::get('quotations/test',                           'QuotationController@test');
    Route::get('quotations/invoice/{id}',                   'QuotationController@invoice');
    Route::resource('quotations',                      'QuotationController');

    //Customer Category
    Route::resource('customer-category',               'CustomerCategoryController');
    Route::resource('marketers',                       'MarketerController');




    // sale controller start

    Route::get('sales/available_quantity/{id}',            'SaleController@available_quantity');
    Route::get('sales/product-available-quantity/{id}',    'SaleController@getProduct');
    Route::get('sales/delivery',                           'SaleController@delivery')->name('sales.delivery');
    Route::get('sales/send-message/{sale}',                'SaleController@sendMessage')->name('sales.send-message');
    Route::get('sales/show-v2/{id}',                       'SaleController@showV2')->name('sales.show-v2');
    Route::get('sales/supplier-wise-sale-create',          'SaleController@newSaleCreate')->name('sales.new.sale.create');
    Route::post('sales/supplier-wise-sale-store',          'SaleController@newSaleStore')->name('sales.new.sale.store');
    Route::resource('sales',                               'SaleController');
    Route::post('sales/send-invoice-email',                'SaleController@sendInvoiceEmailAjax')->name('sales.send-invoice-email');
    Route::resource('package-sales',                       'PackageSaleController');
    Route::resource('curriers',                            'CurrierController');

    Route::get('searchProduct',                            'SaleController@searchProduct')->name('searchProduct');
    Route::get('/check-customer-price', [\App\Http\Controllers\SaleController::class, 'checkCustomerPrice']);

    Route::get('search-product-by-customer-id/{customer_id}/warehouse/{warehouse_id?}', 'SaleController@searchProductByCustomerId')->name('search.product.by.customer.id');
    Route::get('search-product-by-supplier/{supplier_id}', 'SaleController@searchProductBySupplier')->name('search.product.by.supplier');
    Route::get('search-product-by-supplier-customer/{supplier_id}', 'SaleController@searchProductBySupplierCustomer')->name('search.product.by.supplier.customer');
    Route::get('search-holesale-product-by-customer-id/{customer_id}/warehouse/{warehouse_id?}', 'SaleController@searchHolesaleProductByCustomerId')->name('search.holesale.product.by.customer.id');
    Route::get('get-customer',                             'SaleController@getCustomer')->name('get-customer');
    Route::get('get-customers-supplierwise',               'SaleController@getCustomerSupplierwise')->name('get-customer-supplierwise');
    Route::get('get-customer-balance/{id}',                'SaleController@getCustomerBalance')->name('get.customer.balance');
    Route::get('get-supplier-balance/{id}',                'PurchaseController@getSupplierBalance')->name('get.supplier.balance');
    Route::get('get-supplier',                             'SupplierController@getSupplier')->name('get-supplier');
    Route::get('get-customer-balance',                     'SaleController@customerBalance')->name('ajax.customer.balance');
    Route::get('get-customer-supplier-balance',            'SaleController@getCustomerSupplierBalance')->name('get.customer.supplier.balance');



    Route::get('customer-due-collection',             'DueController@customerDue')->name('due-collection');
    Route::post('customer-due-collection/{customer}', 'DueController@customerDueStore')->name('due-collection.store');

    Route::resource('customer-collections',           'CustomerCollectionController');
    Route::get('due-collections/supplierwise-due',    'DueController@supplierwiseDue')->name('due-collections.supplierwise.due');
    Route::post('due-collections/supplierwise-due-store', 'DueController@supplierwiseDueStore')->name('due-collections.supplierwise.due.store');

    Route::resource('due-collections',                'DueController');
    Route::PATCH('due-collections/{review}/approve',  'DueController@approve')->name('due-collections.approve');
    
    // One-off script route to fix the corrupted due bug
    Route::get('fix-due-bug',                         'DueController@fixDueBug')->name('fix.due.bug');

    Route::resource('purchases',                      'PurchaseController');
    Route::get('individual-purchase',                 'PurchaseController@individualPurchase')->name('individual.purchase');
    Route::PATCH('purchases/{purchase}/approve',      'PurchaseController@approve')->name('purchases.approve');


    Route::resource('purchase-returns',               'PurchaseReturnController');
    Route::resource('purchase-return-receives',       'PurchaseReturnReceiveController');


    Route::get('get-supplier-invoices',                    'PurchaseReturnController@getSupplierInvoices')->name('supplier.invoices');
    Route::get('get-supplier-purchased-products',          'PurchaseReturnController@getSupplierPurchasedProducts')->name('supplier.buying-products');


    Route::get('get-supplier-product/{id}',                'PurchaseController@get_supplier_product')->name('purchases.get-supplier-product');
    Route::post('complete-purchases/{id}',                 'PurchaseController@completePurchases')->name('purchases.complete');

    // bulk purchase
    Route::get('bulk-purchase-create',                     'BulkPurchaseController@bulkPurchaseCreate')->name('bulk.purchase.create');
    Route::post('bulk-purchase-store',                     'BulkPurchaseController@bulkPurchaseStore')->name('bulk.purchase.upload.store');

    Route::get('bulk-purchase-confirm',                    'BulkPurchaseController@bulkPurchaseConfirmList')->name('bulk-purchase-confirm-list');
    Route::post('bulk-purchase-confirm-store',             'BulkPurchaseController@bulkPurchaseConfirmListStore')->name('bulk.purchase.confirm.store');

    Route::delete('purchase-confirm-list-delete/{id}',     'BulkPurchaseController@confirmPurchaseDelete')->name('confirm-list-purchases.delete');
    Route::get('purchase-confirm-list-delete-all',         'BulkPurchaseController@confirmPurchaseDeleteAll')->name('confirm-list-all-purchases.delete');

    // ############################----------------- PRODUCTS ----------------- ############################ [Akash]
    Route::group(['prefix' => 'product', 'namespace' => 'Product'], function () {
        Route::resource('units',                    'UnitController');
        Route::resource('product-groups',           'ProductGroupController');
        Route::resource('brands',                   'BrandController');
        Route::resource('product-raks',             'ProductRakController');
        Route::resource('generics',                 'GenericController');
        Route::resource('warehouses',               'WarehouseController');
        Route::resource('categories',               'CategoryController');
        Route::resource('subcategories',            'SubCategoryController');
        Route::post('products-bulk-delete',         'ProductController@bulkDelete')->name('products.bulk-delete');
        Route::resource('products',                 'ProductController');
        Route::resource('product-packages',         'ProductPackageController');
        Route::resource('barcodes',                 'BarcodeController');
        Route::resource('product-damages',          'ProductDamageController');
        Route::resource('customer-product-pricing', 'CustomerProductPricingController');

        // update serial sold status
        Route::post('serial-product',                    'ProductController@UpdateSerial')->name('serial.update');
        // delete serial sold item
        // Route::delete('serial-product/delete','ProductController@DeleteSerial')->name('serial.delete');
        // Serial products
        Route::get('serial-products',                        'ProductController@SerialProduct')->name('product.serial');
        Route::get('search-product',                         'BarcodeController@searchProduct')->name('barcodes.product');
        Route::get('get-product-by-product-serial',          'ProductController@getProductBySerial')->name('product-by-serial');
        Route::get('get-multiple-product-by-product-serial', 'ProductController@getMultipleProductBySerial')->name('multiple-product-by-serial');

        // product upload
        Route::get('product-image-delete',                  'ProductController@productImageDelete')->name('products.image.delete');
        Route::get('upload-products',                       'ProductUploadController@uploadProductView')->name('upload-products.index');
        Route::post('upload-products',                      'ProductUploadController@uploadProductStore')->name('upload-products.store');
        Route::get('product-confirm-list',                  'ProductUploadController@confirmList')->name('product-confirm-list.index');
        Route::post('product-confirm-list',                 'ProductUploadController@confirmProductStore')->name('confirm-list-products.store');

        Route::delete('product-confirm-list-delete/{id}',   'ProductUploadController@confirmProductDelete')->name('confirm-list-products.delete');
        Route::get('product-confirm-list-delete-all',       'ProductUploadController@confirmProductDeleteAll')->name('confirm-list-all-products.delete');

        Route::get('product-prices-edit',                   'ProductController@editProductPrice')->name('product.price.edit');
        Route::post('product-prices-update',                'ProductController@updateProductPrice')->name('product-price.update');
    });



    // ############################----------------- REPORTS ----------------- ############################ [Akash]
    Route::group(['prefix' => 'stock-transfer', 'namespace' => 'StockTransfer'], function () {
        Route::resource('warehouse-to-warehouses',     'WarehouseToWarehouseController');
        Route::resource('company-to-companies',        'CompanyToCompanyController');
        Route::post('company-to-company-receive/{id}',      'CompanyToCompanyController@receiveStock')->name('company-to-companies.receive');
    });


    // ############################----------------- REPORTS ----------------- ############################ [Akash]
    Route::group(['prefix' => 'reports'], function () {
        // Inventory Report
        Route::get('marketers-report',                      'ReportsController@marketers_report')->name('marketers.report');

        Route::get('marketers-ledger/',                     'MarketersledgerController@index')->name('marketers.ledger');
        Route::get('marketers-ledger/create/{id}',          'MarketersledgerController@create')->name('marketers.ledger.create');
        Route::post('marketers-ledger/store/',              'MarketersledgerController@store')->name('marketers.ledger.store');
        Route::get('marketers-ledger/edit/{id}',            'MarketersledgerController@edit')->name('marketers.ledger.edit');
        Route::post('marketers-ledger/update/',             'MarketersledgerController@update')->name('marketers.ledger.update');


        Route::get('serial-product-stocks',                 'StockController@getProductSerials')->name('serial-product.stocks');
        Route::get('supplier-wise-stock-reports',           'StockController@report')->name('supplier.wise.stock.reports');
        Route::get('supplier-wise-stock-reports-print',     'StockController@stockPrint')->name('supplier.wise.stock.reports.print');
        Route::get('supplier-wise-stock-reports-pdf',       'StockController@stockPdf')->name('supplier.wise.stock.reports.pdf');

        Route::get('expire-products',                       'ProductStockController@expireProducts')->name('expire.products');
        Route::post('product_stock/filter',                 'ProductStockController@filter');
        Route::get('product_alert/ajax',                    'ProductAlertController@ajaxAlert')->name('products.alert.ajax');
        Route::get('product_alert',                         'ProductAlertController@index')->name('products.alert');
        Route::get('wastages',                              'ReportsController@wastageReport')->name('wastages.report');
        Route::get('damage-report',                         'DamageReportController')->name('damages.report');
        Route::get('sales',                                 'ReportsController@salesReport')->name('sales.report');
        Route::get('product-wise-sales',                    'ReportsController@productWiseSalesReport')->name('product.wise.sales.report');
        Route::get('delivery-reports',                      'ReportsController@deliveryReport')->name('delivery.reports');
        Route::get('print_sales',                           'ReportsController@printSales');
        Route::get('supplier-wise-sales-report',            'ReportsController@supplierWiseSalesReport')->name('supplier.wise.sales.report');
        Route::get('brand-wise-summary-report',             'ReportsController@brandWiseSummaryReport')->name('brand.wise.summary.report');
        Route::get('sale_discounts',                        'SaleDiscountReportController@index')->name('supplier.wise.discount');
        Route::get('purchase',                              'ReportsController@purchaseReport')->name('purchases.report');


        Route::get('customer-top-sales',                    'ReportsController@customerTopSales')->name('reports.customer_top_sales');

        Route::group(['namespace' => 'Reports'], function () {
            Route::get('product-stocks',                    'InventoryReportController@productStockReport')->name('product.wise.stock');
            Route::get('product-stocks-pdf',                'InventoryReportController@productStockPdf')->name('product.stocks.pdf');

            Route::post('sync-stock',                       'InventoryReportController@syncStock')->name('product.stock.sync');
            Route::get('topsellproduct',                    'InventoryReportController@topsellproduct')->name('top.sell.product');
        });

        // Financial Report
        Route::group(['namespace' => 'Reports'], function () {
            Route::get('cash-flow-reports',                  'FinancialReportController@cashFlowReport')->name('cash.flow.reports');
            Route::get('customer-reports',                   'FinancialReportController@customerReports')->name('customers.report');
            Route::get('supplier-reports',                   'FinancialReportController@supplierReports')->name('suppliers.report');
            Route::get('receivable-due-reports',             'FinancialReportController@receivableDueReport')->name('receivable.due.report');
            Route::get('payable-due-reports',                'FinancialReportController@getPayableDueReport')->name('payable.due.report');
        });

        Route::get('daily-report',                           'ReportsController@dailReport')->name('daily.report');
        Route::get('debit_credit',                           'IncomeExpenseReportController@index')->name('income.expense.report');
        Route::get('ga-parties-report',                      'ReportsController@gaPartiesReport')->name('ga.parities.report');
        Route::get('sale-returns',                           'SaleReturnReportController@report')->name('sale.returns.report');
        Route::get('tax-report',                             'ReportsController@taxReport')->name('tax.report');
        Route::get('product-wise-profit-report',             'ReportsController@productWiseProfitReport')->name('product.wise.profit.report');
        Route::get('profit_loss',                            'ProfitLossReportController@index')->name('profit.loss.report');

        Route::get('date-product-reports',                   'ProductStockController@date_product_reports');
        Route::get('customer_transaction',                   'CustomerTransactionController');




        Route::get('print-sale-returns',                     'SaleReturnReportController@printReport');

        Route::get('get_json_stock',                         'ProductStockController@get_json_stock');

        Route::get('daily_quotation',                        'DailyQuotationController@index');
        Route::get('daily_purchase',                         'DailyPurchaseController@index');
        Route::get('daily_sales',                            'DailySalesController@index');

    });


    Route::get('get-current-balance',                        'BalanceController@currentBalance');
    Route::get('get-product-stock',                          'ProductStockController@stockAlert');
    Route::resource('sale-distributors',                'SaleDistributorController');

    Route::get('get-all-sales',                              'SaleReturnController@getAllSales');
    Route::get('get-product/{product}',                      'Product\ProductController@getProduct');


    Route::get('saleable-products',                          'Product\ProductController@saleableProducts');
    Route::get('sale-exchanges',                              'SaleReturnController@exchanges')->name('sale-exchanges.index');
    Route::resource('sale-returns',                     'SaleReturnController');

    Route::get('get-customer-invoices',                      'SaleReturnController@getCustomerInvoices')->name('customer.invoices');
    Route::get('get-customer-buying-products',               'SaleReturnController@getCustomerBuyingProducts')->name('customer.buying-products');
    Route::get('get-customer-serial-products',               'SaleReturnController@getCustomerSerialProducts')->name('customer.serial-products');
    Route::get('get-invoice-id',                             'SaleReturnController@getInvoiceId')->name('customer.get-invoice-id');
    Route::get('get-invoice-id-for-serial-product',          'SaleReturnController@getInvoiceIdBySerial')->name('customer.get-invoice-id-serial');
    Route::get('get-customer-data',                          'SaleReturnController@getCustomerData')->name('get-customer-data');
    Route::get('get-supplier-customer-due',                  'SaleReturnController@getSupplierCustomerDue')->name('sale-returns.get-supplier-customer-due');
    Route::get('get-supplier-products',                      'SaleReturnController@getSupplierProducts')->name('sale-returns.get-supplier-products');



    // #######################     Settings   ######################
    Route::resource('companies',                         'CompanyController');

    Route::get('refresh-application',                         'SoftwareSettingController@junkClean')->name('refresh.application');
    Route::get('software-settings',                           'SoftwareSettingController@index')->name('software-settings');
    Route::post('software-settings',                          'SoftwareSettingController@update')->name('software-settings.update');

    Route::get('mail-settings',                               'MailSettingController@index')->name('mail-settings');
    Route::post('mail-settings',                              'MailSettingController@update')->name('mail-settings.update');

    Route::get('sdeveloper',                                  'DeveloperSettingController@index')->name('developer.settings');
    Route::post('sdeveloper',                                 'DeveloperSettingController@update')->name('developer.settings.update');

    Route::post('send-email/{id}',                            'Backend\SendEmailController@index')->name('email.send');
    Route::post('send-email-purchase/{id}',                   'Backend\SendEmailController@purchase')->name('email.send.purchase');

});

Route::get('/debug-log', function () {
    $logFile = storage_path('logs/laravel-' . date('Y-m-d') . '.log');
    if (!file_exists($logFile)) {
        $files = glob(storage_path('logs/laravel-*.log'));
        if (!empty($files)) {
            $logFile = end($files);
        } else {
            return 'No log file found.';
        }
    }
    $lines = file($logFile);
    $lastLines = array_slice($lines, -250);
    return response('<pre>' . implode("", $lastLines) . '</pre>');
});

// Temporary route to fix Cash Customer supplier balance mismatch
Route::get('/fix-customer-supplier-balance', function() {
    $customer = \App\Customer::where('customer_code', 'c-1')->first();
    if($customer) {
        \App\Models\CustomerSupplier::where('customer_id', $customer->id)->update(['balance' => 0]);
        $customer->update(['current_balance' => 0]);
        return "All supplier balances for Cash Customer have been reset to 0.";
    }
    return "Customer c-1 not found.";
});

// Temporary route to safely fix legacy negative balances in customer_suppliers
Route::get('/fix-customer-supplier-balances', function() {
    $records = \App\Models\CustomerSupplier::where('balance', '<', 0)->get();
    $fixed = 0;
    foreach($records as $record) {
        $customer = \App\Customer::find($record->customer_id);
        // If the customer has an overall positive balance (Due), this negative supplier balance is likely a bugged legacy record
        if ($customer && $customer->current_balance > 0) {
            $record->update(['balance' => abs($record->balance)]);
            $fixed++;
        }
    }
    return "<h1>Successfully fixed $fixed legacy negative balances!</h1><p>You can now check the Collection page.</p>";
});

// Temporary route to fix Energy Meter product cost
Route::get('/fix-energy-meter-cost', function() {
    $product = \App\Product::where('product_code', '1290341005')->first();
    if ($product) {
        $updated = \App\SalesDetails::where('fk_product_id', $product->id)
            ->where('product_cost', '>', 5000)
            ->update(['product_cost' => 1129.15]);
        return "<h1>Successfully fixed {$updated} sales records!</h1><p>The Profit & Loss reports should now be correct.</p>";
    }
    return "Product not found.";
});




Route::get('/fix-product-upload-tenancy-live', function() {
    try {
        \Artisan::call('fix:product-upload-tenancy');
        return 'Products Fixed Successfully! Output: ' . \Artisan::output();
    } catch (\Throwable $e) {
        return 'Error: ' . $e->getMessage() . '<br>Line: ' . $e->getLine() . '<br>File: ' . $e->getFile();
    }
});

Route::get('/fix-missing-due-1225', function() {
    $cId = 1264;
    $sId = 323;
    $cs = \App\Models\CustomerSupplier::where('customer_id', $cId)->where('supplier_id', $sId)->first();
    
    if (!$cs) {
        $cs = \App\Models\CustomerSupplier::create([
            'customer_id' => $cId,
            'supplier_id' => $sId,
            'balance' => 68536
        ]);
    } else {
        $cs->balance = 68536;
        $cs->save();
    }
    
    return "Fixed! New balance: " . $cs->balance;
});


