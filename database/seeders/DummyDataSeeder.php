<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Kot;
use App\Models\KotItem;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\OrderTax;
use App\Models\Payment;
use App\Models\Table;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();
        if (!$branch) {
            $this->command->error('No branch found to seed dummy data.');
            return;
        }

        // Ensure we have menu items
        if (MenuItem::where('branch_id', $branch->id)->count() == 0) {
            $this->command->info('No menu items found. Running MenuItemSeeder...');
            $seeder = new MenuItemSeeder();
            $seeder->run($branch);
        }

        // Ensure we have tables
        if (Table::where('branch_id', $branch->id)->count() == 0) {
            $this->command->info('No tables found. Running TableSeeder...');
            $seeder = new TableSeeder();
            $seeder->run($branch);
        }

        // Ensure we have taxes
        if (Tax::withoutGlobalScopes()->where('branch_id', $branch->id)->count() == 0) {
            $this->command->info('No taxes found. Running TaxSeeder...');
            $seeder = new TaxSeeder();
            $seeder->run($branch);
        }

        $tableIds = Table::where('branch_id', $branch->id)->pluck('id')->all();
        $waiterIds = User::where('branch_id', $branch->id)->pluck('id')->all();
        $menuItems = MenuItem::where('branch_id', $branch->id)->get(['id', 'price']);
        $taxes = Tax::withoutGlobalScopes()->where('branch_id', $branch->id)->get(['id', 'tax_percent']);

        if (empty($tableIds) || empty($waiterIds) || $menuItems->isEmpty()) {
            $this->command->error('Missing tables, waiters or menu items for seeding. Please run general seeders first.');
            return;
        }

        $orderNumberCounter = ((int) Order::where('branch_id', $branch->id)->max('id')) + 1;
        $kotNumberCounter = ((int) Kot::max('id')) + 1;

        $this->command->info('Creating 20 dummy orders...');

        for ($i = 0; $i < 20; $i++) {
            $customer = new Customer();
            $customer->restaurant_id = $branch->restaurant_id;
            $customer->name = fake()->firstName() . ' ' . fake()->lastName();
            $customer->email = fake()->unique()->safeEmail();
            $customer->delivery_address = fake()->address();
            $customer->save();

            $this->placeOrder(
                $customer,
                $branch,
                rand(0, 1) == 1, // random today or previous
                $tableIds,
                $waiterIds,
                $menuItems,
                $taxes,
                $orderNumberCounter,
                $kotNumberCounter
            );
        }

        $this->command->info('20 dummy orders created successfully!');
    }

    private function placeOrder($customer, $branch, $isToday, $tableIds, $waiterIds, $menuItems, $taxes, &$orderNumberCounter, &$kotNumberCounter)
    {
        $tableId = $tableIds[array_rand($tableIds)];
        $waiterId = $waiterIds[array_rand($waiterIds)];
        $orderNumber = $orderNumberCounter++;
        $kotNumber = $kotNumberCounter++;
        $now = now();

        $order = Order::create([
            'order_number' => (string) $orderNumber,
            'table_id' => $tableId,
            'customer_id' => $customer->id,
            'waiter_id' => $waiterId,
            'date_time' => $isToday ? $now->toDateTimeString() : $now->copy()->subDays(rand(1, 10))->toDateTimeString(),
            'sub_total' => 0,
            'total' => 0,
            'status' => 'paid',
            'branch_id' => $branch->id,
            'placed_via' => 'pos',
            'amount_paid' => 0,
        ]);

        $kot = Kot::create([
            'kot_number' => (string) $kotNumber,
            'order_id' => $order->id,
            'branch_id' => $branch->id,
            'status' => 'served',
        ]);

        $itemsCount = rand(1, 4);
        $selectedItems = $menuItems->random($itemsCount);
        $subTotal = 0.0;

        foreach ($selectedItems as $value) {
            $quantity = rand(1, 3);
            $amount = round($quantity * $value->price, 2);
            $subTotal += $amount;

            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $value->id,
                'quantity' => $quantity,
                'price' => $value->price,
                'amount' => $amount,
                'branch_id' => $branch->id,
            ]);

            KotItem::create([
                'kot_id' => $kot->id,
                'menu_item_id' => $value->id,
                'quantity' => $quantity,
            ]);
        }

        $total = $subTotal;
        foreach ($taxes as $value) {
            OrderTax::create([
                'order_id' => $order->id,
                'tax_id' => $value->id,
            ]);
            $total += ($value->tax_percent / 100) * $subTotal;
        }

        $total = round($total);
        $order->update(['sub_total' => $subTotal, 'total' => $total, 'amount_paid' => $total]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => ['card', 'cash', 'upi'][rand(0, 2)],
            'amount' => $total,
            'branch_id' => $branch->id,
        ]);
    }
}
