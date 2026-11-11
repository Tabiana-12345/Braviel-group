<?php

namespace Tests\Feature;

use App\Models\AdminRecord;
// use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_update_and_delete_a_module_record(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne(['role' => 'admin']);
        $this->actingAs($admin);

        $this->get(route('admin.modules.create', 'sales'))
            ->assertOk()
            ->assertSee('Add record');

        $this->post(route('admin.modules.store', 'sales'), [
            'title' => 'First entered sale',
            'data' => ['customer' => 'Customer entered by admin', 'total_amount' => '42.50'],
        ])->assertRedirect(route('admin.modules.index', 'sales'));

        $record = AdminRecord::where('module', 'sales')->firstOrFail();
        $this->assertSame('42.50', $record->data['total_amount']);
        $this->assertDatabaseHas('audit_logs', ['module' => 'sales', 'action' => 'created']);

        $this->get(route('admin.modules.index', 'sales'))
            ->assertOk()
            ->assertSee('First entered sale');

        $this->get(route('admin.modules.edit', ['sales', $record->id]))
            ->assertOk()
            ->assertSee('First entered sale');

        $this->put(route('admin.modules.update', ['sales', $record->id]), [
            'title' => 'Updated sale',
            'data' => ['customer' => 'Updated customer', 'total_amount' => '50.00'],
        ])->assertRedirect(route('admin.modules.index', 'sales'));

        $this->assertDatabaseHas('admin_records', ['id' => $record->id, 'title' => 'Updated sale']);
        $this->delete(route('admin.modules.destroy', ['sales', $record->id]))
            ->assertRedirect(route('admin.modules.index', 'sales'));

        $this->assertDatabaseMissing('admin_records', ['id' => $record->id]);
        $this->assertDatabaseCount('audit_logs', 3);
    }

    public function test_signed_in_staff_can_access_the_dashboard_and_management_routes(): void
    {
        /** @var User $staff */
        $staff = User::factory()->createOne(['role' => 'staff']);
        $this->actingAs($staff);

        $this->get(route('dashboard'))->assertOk()->assertSee('Dashboard');
        $this->get(route('admin.modules.index', 'products'))->assertOk()->assertSee('Product Management');
    }

    public function test_administrator_can_manage_users_without_removing_the_last_administrator(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('admin.users.store'), [
            'name' => 'Operations staff',
            'username' => 'operations_staff',
            'email' => 'operations@example.test',
            'role' => 'staff',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertRedirect(route('admin.users.index'));

        $staff = User::where('email', 'operations@example.test')->firstOrFail();
        $this->assertSame('staff', $staff->role);
        $this->get(route('admin.users.edit', $staff))
            ->assertOk()
            ->assertSee('Operations staff')
            ->assertSee('New password')
            ->assertSee('Confirm password');

        $this->put(route('admin.users.update', $staff), [
            'name' => $staff->name,
            'username' => $staff->username,
            'email' => $staff->email,
            'role' => $staff->role,
            'password' => 'updated-secure-password',
            'password_confirmation' => 'updated-secure-password',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('updated-secure-password', $staff->fresh()->password));

        $this->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'username' => $admin->username,
            'email' => $admin->email,
            'role' => 'staff',
            'password' => '',
            'password_confirmation' => '',
        ])->assertUnprocessable();

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_database_seeder_does_not_create_sample_users_or_business_records(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('admin_records', 0);
    }

    public function test_administrator_can_view_reports_and_audit_logs_and_download_a_backup_without_passwords(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne(['role' => 'admin']);
        $this->actingAs($admin);

        $this->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('No report data yet');

        $this->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSee('Audit Logs and Backup')
            ->assertSee('No activity recorded');

        $response = $this->post(route('admin.audit.backup'));
        $response->assertOk()->assertHeader('content-type', 'application/json');
        $backup = json_decode($response->streamedContent(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('records', $backup);
        $this->assertArrayHasKey('users', $backup);
        $this->assertArrayNotHasKey('password', $backup['users'][0]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'backup-exported']);
    }

    public function test_command_can_promote_an_existing_user_to_administrator(): void
    {
        $user = User::factory()->createOne(['role' => 'staff']);

        $this->artisan('app:make-admin', ['email' => $user->email])
            ->assertExitCode(0);

        $this->assertSame('admin', $user->fresh()->role);
    }

    public function test_command_can_create_the_first_administrator_without_seed_data(): void
    {
        $this->artisan('app:make-admin', ['email' => 'first-admin@example.test', '--name' => 'First Admin'])
            ->expectsQuestion('Password (at least 8 characters)', 'secure-password')
            ->expectsQuestion('Confirm password', 'secure-password')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'first-admin@example.test',
            'name' => 'First Admin',
            'role' => 'admin',
        ]);
    }

    public function test_product_and_opening_stock_csv_imports_create_records(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne(['role' => 'admin']);
        $this->actingAs($admin);
        $this->createProductOption('brands', 'Braviel Brand');
        $this->createProductOption('categories', 'Office');
        $this->createProductOption('units', 'Piece');

        $this->get(route('admin.modules.import.form', 'products'))
            ->assertOk()
            ->assertSee('Import Products')
            ->assertSee('File To Import:')
            ->assertSee('Submit')
            ->assertSee('Download template file')
            ->assertSee(route('admin.modules.products.import-template'), false);

        $this->post(route('admin.modules.import', 'products'), [
            'file' => UploadedFile::fake()->createWithContent(
                'products.csv',
                "title,sku,brand,category,unit,purchase_price,sale_price,stock_quantity\nDesk,SKU-100,Braviel Brand,Office,Piece,12.50,20.00,4\n"
            ),
        ])->assertRedirect(route('admin.modules.index', 'products'));

        $this->assertDatabaseHas('admin_records', [
            'module' => 'products',
            'title' => 'Desk',
        ]);

        $this->post(route('admin.modules.import', 'inventory'), [
            'file' => UploadedFile::fake()->createWithContent(
                'opening-stock.csv',
                "title,product,quantity,movement_type,movement_date,notes\nOpening balance,Desk,4,opening stock,2026-11-11,Initial count\n"
            ),
        ])->assertRedirect(route('admin.modules.index', 'inventory'));

        $this->assertDatabaseHas('admin_records', [
            'module' => 'inventory',
            'title' => 'Opening balance',
        ]);
    }

    public function test_product_import_template_download_contains_product_headers_only(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('admin.modules.products.import-template'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition', 'attachment; filename="product-import-template.csv"')
            ->assertSee('title,sku,brand,category,unit,purchase_price,sale_price,stock_quantity');
    }

    public function test_contacts_csv_import_creates_customer_and_supplier_records(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne(['role' => 'admin']);
        $this->actingAs($admin);

        $this->get(route('admin.modules.import.form', 'contacts'))
            ->assertOk()
            ->assertSee('Import Contacts')
            ->assertSee('title,type,contact_name,email,phone,address')
            ->assertSee('customer or supplier')
            ->assertSee('href="'.route('admin.modules.index', 'customers').'"', false);

        $this->post(route('admin.modules.import', 'contacts'), [
            'file' => UploadedFile::fake()->createWithContent(
                'contacts.csv',
                "title,type,contact_name,email,phone,address\nFresh Market,customer,Amina,customer@example.test,0712345678,Central\nReliable Supply,supplier,Jon,supplier@example.test,0798765432,North\n"
            ),
        ])->assertRedirect(route('admin.modules.index', 'customers'))
            ->assertSessionHas('status', '2 contacts imported.');

        $this->assertDatabaseHas('admin_records', [
            'module' => 'customers',
            'title' => 'Fresh Market',
        ]);
        $this->assertDatabaseHas('admin_records', [
            'module' => 'suppliers',
            'title' => 'Reliable Supply',
        ]);
        $this->assertDatabaseCount('audit_logs', 2);
    }

    public function test_excel_workbooks_import_contacts_and_products(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne(['role' => 'admin']);
        $this->actingAs($admin);

        $this->get(route('admin.modules.import.form', 'contacts'))
            ->assertOk()
            ->assertSee('CSV or Excel file')
            ->assertSee('.xlsx');

        $this->post(route('admin.modules.import', 'contacts'), [
            'file' => $this->makeXlsxUpload([
                ['title', 'type', 'contact_name', 'email', 'phone', 'address'],
                ['Excel Customer', 'customer', 'Alex', 'excel-customer@example.test', '0711111111', 'West'],
                ['Excel Supplier', 'supplier', 'Sam', 'excel-supplier@example.test', '0722222222', 'East'],
            ]),
        ])->assertRedirect(route('admin.modules.index', 'customers'))
            ->assertSessionHas('status', '2 contacts imported.');

        $this->assertDatabaseHas('admin_records', ['module' => 'customers', 'title' => 'Excel Customer']);
        $this->assertDatabaseHas('admin_records', ['module' => 'suppliers', 'title' => 'Excel Supplier']);
        $this->createProductOption('brands', 'Excel Brand');
        $this->createProductOption('categories', 'Excel Office');
        $this->createProductOption('units', 'Box');

        $this->post(route('admin.modules.import', 'products'), [
            'file' => $this->makeXlsxUpload([
                ['title', 'sku', 'brand', 'category', 'unit', 'purchase_price', 'sale_price', 'stock_quantity'],
                ['Excel Desk', 'EXCEL-1', 'Excel Brand', 'Excel Office', 'Box', '12.50', '20.00', '6'],
            ]),
        ])->assertRedirect(route('admin.modules.index', 'products'))
            ->assertSessionHas('status', '1 records imported.');

        $this->assertDatabaseHas('admin_records', ['module' => 'products', 'title' => 'Excel Desk']);
    }

    public function test_product_form_uses_existing_brands_categories_and_units_as_select_options(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->createProductOption('brands', 'Acme Brand');
        $this->createProductOption('categories', 'Home Goods');
        $this->createProductOption('units', 'Carton', ['abbreviation' => 'ctn']);

        $this->get(route('admin.modules.create', 'products'))
            ->assertOk()
            ->assertSee('id="data-brand" name="data[brand]"', false)
            ->assertSee('value="Acme Brand"', false)
            ->assertSee('value="Home Goods"', false)
            ->assertSee('value="Carton"', false)
            ->assertSee('Carton (ctn)');

        $this->post(route('admin.modules.store', 'products'), [
            'title' => 'Selected product',
            'data' => [
                'brand' => 'Acme Brand',
                'category' => 'Home Goods',
                'unit' => 'Carton',
            ],
        ])->assertRedirect(route('admin.modules.index', 'products'));

        $product = AdminRecord::where('module', 'products')->where('title', 'Selected product')->firstOrFail();
        $this->assertSame([
            'brand' => 'Acme Brand',
            'category' => 'Home Goods',
            'unit' => 'Carton',
        ], $product->data);
    }

    public function test_brand_and_unit_forms_allow_short_name_to_be_optional(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        foreach (['brands' => 'short_name', 'units' => 'abbreviation'] as $module => $shortNameField) {
            $this->get(route('admin.modules.create', $module))
                ->assertOk()
                ->assertSee('Name <span aria-hidden="true">*</span>', false)
                ->assertSee('Short name')
                ->assertDontSee('Short name <span aria-hidden="true">*</span>', false)
                ->assertSee('name="data['.$shortNameField.']"', false)
                ->assertSee('required', false);

            $this->post(route('admin.modules.store', $module), [
                'title' => 'Entered '.$module,
            ])->assertRedirect(route('admin.modules.index', $module));

            $this->assertDatabaseHas('admin_records', [
                'module' => $module,
                'title' => 'Entered '.$module,
            ]);
            $record = AdminRecord::where('module', $module)->where('title', 'Entered '.$module)->firstOrFail();
            $this->assertSame([], $record->data);

            $this->post(route('admin.modules.store', $module), [
                'title' => 'Entered '.$module.' with short name',
                'data' => [$shortNameField => 'Short'],
            ])->assertRedirect(route('admin.modules.index', $module));

            $recordWithShortName = AdminRecord::where('module', $module)
                ->where('title', 'Entered '.$module.' with short name')
                ->firstOrFail();
            $this->assertSame([$shortNameField => 'Short'], $recordWithShortName->data);

            $this->get(route('admin.modules.index', $module))
                ->assertOk()
                ->assertDontSee('<th>Created</th>', false)
                ->assertSee('<th>Actions</th>', false);
        }
    }

    public function test_brand_and_unit_name_remain_required(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        foreach (['brands', 'units'] as $module) {
            $this->from(route('admin.modules.create', $module))
                ->post(route('admin.modules.store', $module), [])
                ->assertRedirect(route('admin.modules.create', $module))
                ->assertSessionHasErrors('title');
        }
    }

    private function createProductOption(string $module, string $title, array $data = []): AdminRecord
    {
        return AdminRecord::create([
            'module' => $module,
            'title' => $title,
            'data' => $data,
        ]);
    }

    /**
     * @param  list<list<string>>  $rows
     */
    private function makeXlsxUpload(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'braviel-xlsx-');
        if ($path === false) {
            throw new \RuntimeException('Unable to create the Excel test workbook.');
        }

        $zip = new \ZipArchive;
        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            unlink($path);
            throw new \RuntimeException('Unable to open the Excel test workbook.');
        }

        $sharedStrings = [];
        $worksheetRows = '';
        $columnName = static function (int $number): string {
            $name = '';
            while ($number > 0) {
                $number--;
                $name = chr(65 + ($number % 26)).$name;
                $number = intdiv($number, 26);
            }

            return $name;
        };
        foreach ($rows as $rowIndex => $row) {
            $worksheetRows .= '<row r="'.($rowIndex + 1).'">';
            foreach ($row as $columnIndex => $value) {
                $sharedStringIndex = array_search($value, $sharedStrings, true);
                if ($sharedStringIndex === false) {
                    $sharedStringIndex = count($sharedStrings);
                    $sharedStrings[] = $value;
                }
                $worksheetRows .= '<c r="'.$columnName($columnIndex + 1).($rowIndex + 1).'" t="s"><v>'.$sharedStringIndex.'</v></c>';
            }
            $worksheetRows .= '</row>';
        }

        $sharedStringXml = '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($sharedStrings).'" uniqueCount="'.count($sharedStrings).'">';
        foreach ($sharedStrings as $value) {
            $sharedStringXml .= '<si><t>'.htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</t></si>';
        }
        $sharedStringXml .= '</sst>';

        $zip->addFromString('xl/workbook.xml', '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Contacts" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/sharedStrings.xml', $sharedStringXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$worksheetRows.'</sheetData></worksheet>');
        $zip->close();

        $contents = file_get_contents($path);
        unlink($path);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read the Excel test workbook.');
        }

        return UploadedFile::fake()->createWithContent('import.xlsx', $contents);
    }

    public function test_roles_menu_page_lists_defined_roles(): void
    {
        $this->actingAs(User::query()->create(['role' => 'admin']))
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('Administrator')
            ->assertSee('Staff')
            ->assertSee('Add role')
            ->assertSee('Actions')
            ->assertSee('Delete')
            ->assertDontSee('Assigned users')
            ->assertDontSee('<th>Type</th>', false);
    }

    public function test_role_can_be_created_edited_assigned_and_deleted_with_menu_permissions(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne(['role' => 'admin']);
        $this->actingAs($admin);

        $this->get(route('admin.roles.create'))
            ->assertOk()
            ->assertSee('Role Name')
            ->assertSee('Select all permissions')
            ->assertSee('Suppliers')
            ->assertSee('Import opening stock')
            ->assertSee('Download backup');

        $this->post(route('admin.roles.store'), [
            'name' => 'Warehouse Manager',
            'permissions' => [
                'products' => ['view_product' => '1', 'add_product' => '1'],
                'inventory' => ['view_inventory' => '1', 'edit_inventory' => '1'],
            ],
        ])->assertRedirect(route('admin.roles.index'));

        $role = \App\Models\Role::where('name', 'Warehouse Manager')->firstOrFail();
        $this->assertSame([
            'view_product' => true,
            'add_product' => true,
        ], $role->permissions['products']);
        $this->assertSame([
            'view_inventory' => true,
            'edit_inventory' => true,
        ], $role->permissions['inventory']);

        $this->get(route('admin.roles.edit', $role))
            ->assertOk()
            ->assertSee('Warehouse Manager')
            ->assertSee('name="permissions[products][view_product]" type="checkbox" value="1" data-permission checked', false);

        $this->post(route('admin.users.store'), [
            'name' => 'Warehouse Operator',
            'username' => 'warehouse_operator',
            'email' => 'warehouse@example.test',
            'role' => $role->key,
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertRedirect(route('admin.users.index'));

        $this->delete(route('admin.roles.destroy', $role))
            ->assertSessionHasErrors('role');

        $operator = User::where('email', 'warehouse@example.test')->firstOrFail();
        $this->assertSame($role->key, $operator->role);

        $operator->delete();
        $this->delete(route('admin.roles.destroy', $role))
            ->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_builtin_roles_cannot_be_deleted(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne(['role' => 'admin']);
        $this->actingAs($admin);
        $adminRole = \App\Models\Role::where('key', 'admin')->firstOrFail();

        $this->delete(route('admin.roles.destroy', $adminRole))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('roles', ['id' => $adminRole->id]);
        $this->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('disabled title="Built-in roles cannot be deleted"', false);
    }

    public function test_signed_in_user_can_open_account_menu_and_update_own_profile(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne(['role' => 'staff']);
        $this->actingAs($user);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My Profile')
            ->assertSee('Sign out')
            ->assertSee('admin-menu-icon');

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('My Profile')
            ->assertSee($user->email);

        $this->put(route('profile.update'), [
            'name' => 'Updated Profile',
            'username' => 'updated_profile',
            'email' => 'updated-profile@example.test',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Profile',
            'username' => 'updated_profile',
            'email' => 'updated-profile@example.test',
            'role' => 'staff',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'module' => 'profile',
            'action' => 'updated',
        ]);
    }

    public function test_users_table_search_pagination_detail_and_exports_work(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne(['role' => 'admin']);
        User::factory()->create(['name' => 'Target User', 'username' => 'target_user', 'email' => 'target@example.test']);
        User::factory()->count(12)->create();
        $this->actingAs($admin);

        $this->get(route('admin.users.index', ['search' => 'target_user', 'page_size' => 10]))
            ->assertOk()
            ->assertSee('Username')
            ->assertSee('Name')
            ->assertSee('Role')
            ->assertSee('Email')
            ->assertSee('Action')
            ->assertSee('target_user')
            ->assertSee('Showing 1 to 1 of 1 entries')
            ->assertSee('Delete Target User')
            ->assertDontSee('Column visibility');

        $this->get(route('admin.users.index', ['search' => $admin->username]))
            ->assertOk()
            ->assertSee('title="You cannot delete your own account">Delete</button>', false);

        $this->get(route('admin.users.index', ['page_size' => 10]))
            ->assertOk()
            ->assertSee('Showing 1 to 10 of 14 entries');

        $this->get(route('admin.users.index', ['search' => 'Administrator']))
            ->assertOk()
            ->assertSee($admin->username);

        $target = User::where('username', 'target_user')->firstOrFail();
        $this->get(route('admin.users.show', $target))
            ->assertOk()
            ->assertSee('target@example.test')
            ->assertSee('target_user');

        $csv = $this->get(route('admin.users.export', ['format' => 'csv', 'search' => 'target_user']));
        $csv->assertOk()->assertHeader('content-disposition', 'attachment; filename=users.csv');
        $this->assertStringContainsString('target_user,"Target User",Staff,target@example.test', $csv->streamedContent());

        $excel = $this->get(route('admin.users.export', ['format' => 'excel', 'search' => 'target_user']));
        $excel->assertOk()->assertHeader('content-disposition', 'attachment; filename=users.xml');
        $this->assertStringContainsString('target@example.test', $excel->streamedContent());
    }
}
