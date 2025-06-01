<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Ordre important : d'abord les données de base
            UserSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            
            // Ensuite les produits (qui dépendent des catégories et fournisseurs)
            ProductSeeder::class,
            
            // Puis les clients
            ClientSeeder::class,
            
            // Ensuite les ventes (qui dépendent des produits et clients)
            SaleSeeder::class,
            
            // Les ordonnances (qui dépendent des clients et produits)
            PrescriptionSeeder::class,
            
            // Les achats (qui dépendent des fournisseurs et produits)
            PurchaseSeeder::class,
            
            // Enfin les données de base pour compléter
            BasicDataSeeder::class,
        ]);
    }
}