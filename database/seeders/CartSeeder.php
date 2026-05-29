<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Cart::create([
            'user_id'       => 2,
            'shipping_cost' => 15,
            'status'        => 'active'
        ]);

        Cart::create([
            'user_id'       => 3,
            'shipping_cost' => 15,
            'status'        => 'active'
        ]);

        CartItem::create([
            'cart_id' => 1,
            'design_id' => 1,
            'printify_product_id' => 'test',
            'printify_variant_id' => 'test',
            'quantity' => 1,
            'price' => 80,
            'product_name' => 'Heavy Cotton T-Shirt',
            'product_size' => 'M',
            'product_color' => 'White',
            'product_front_image' => 'design_element_image/front1.jpg',
        ]);

        CartItem::create([
            'cart_id' => 1,
            'design_id' => 2,
            'printify_product_id' => 'test1',
            'printify_variant_id' => 'test1',
            'quantity' => 1,
            'price' => 90,
            'product_name' => 'Heavy Cotton T-Shirt',
            'product_size' => 'L',
            'product_color' => 'Black',
            'product_front_image' => 'design_element_image/front2.jpg',
        ]);

        CartItem::create([
            'cart_id' => 2,
            'design_id' => 3,
            'printify_product_id' => 'test2',
            'printify_variant_id' => 'test2',
            'quantity' => 1,
            'price' => 80,
            'product_name' => 'Heavy Cotton T-Shirt',
            'product_size' => 'M',
            'product_color' => 'White',
            'product_front_image' => 'design_element_image/front1.jpg',
        ]);

        CartItem::create([
            'cart_id' => 2,
            'design_id' => 4,
            'printify_product_id' => 'test3',
            'printify_variant_id' => 'test3',
            'quantity' => 1,
            'price' => 90,
            'product_name' => 'Heavy Cotton T-Shirt',
            'product_size' => 'L',
            'product_color' => 'Black',
            'product_front_image' => 'design_element_image/front2.jpg',
        ]);
    }
}
