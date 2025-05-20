<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the products.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier']);

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtre par catégorie
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Filtre par stock
        if ($request->has('stock_status')) {
            if ($request->stock_status == 'low') {
                $query->whereColumn('stock_quantity', '<=', 'stock_threshold');
            } elseif ($request->stock_status == 'out') {
                $query->where('stock_quantity', '<=', 0);
            }
        }

        $products = $query->paginate(10);
        $categories = Category::all();
        
        return view('inventory.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        $categories = Category::all();
        $suppliers = Supplier::all();
        return view('inventory.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'stock_threshold' => 'required|integer|min:0',
            'barcode' => 'nullable|string|unique:products',
            'description' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'expiry_date' => 'nullable|date',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product = new Product();
        $product->fill($request->except('image'));

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image_path = $imagePath;
        }

        $product->save();

        return redirect()->route('inventory.index')
            ->with('success', 'Produit ajouté avec succès!');
    }

    /**
     * Display the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('inventory.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        $suppliers = Supplier::all();
        return view('inventory.edit', compact('product', 'categories', 'suppliers'));
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'stock_threshold' => 'required|integer|min:0',
            'barcode' => 'nullable|string|unique:products,barcode,'.$id,
            'description' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'expiry_date' => 'nullable|date',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product = Product::findOrFail($id);
        $product->fill($request->except('image'));

        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image_path = $imagePath;
        }

        $product->save();

        return redirect()->route('inventory.index')
            ->with('success', 'Produit mis à jour avec succès!');
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // Supprimer l'image si elle existe
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        
        $product->delete();

        return redirect()->route('inventory.index')
            ->with('success', 'Produit supprimé avec succès!');
    }
}