<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Size;
use App\Models\Color;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockTransaction;
use App\Traits\GeneratesUniqueCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use GeneratesUniqueCode;

    public function test(Request $request)
    {
        $allcategories = Category::with('parent')->get()->map(function ($category) {
            $category->hierarchy_string = $category->hierarchy_string; // Access it
            return $category;
        });
        return Inertia::render('Products/index2', [
            'categories' => $allcategories
        ]);
    }

    public function fetchProducts(Request $request)
    {
        $query = $request->input('search');
        $sortOrder = $request->input('sort');
        $selectedColor = $request->input('color');
        $selectedSize = $request->input('size');
        $stockStatus = $request->input('stockStatus');
        $selectedCategory = $request->input('selectedCategory');

$productsQuery = Product::with('category', 'color', 'size', 'supplier')
        ->whereNotNull('products.name')
        ->with([
        'promotionItems:id,promotion_id,product_id,quantity',
        'promotionItems.product:id,name'
    ])
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where(function ($subQuery) use ($query) {
                    $subQuery->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%")
                        ->orWhere('barcode', 'like', "%{$query}%");
                });
            })
            ->when($selectedColor, function ($queryBuilder) use ($selectedColor) {
                $queryBuilder->whereHas('color', function ($colorQuery) use ($selectedColor) {
                    $colorQuery->where('name', $selectedColor);
                });
            })
            ->when($selectedSize, function ($queryBuilder) use ($selectedSize) {
                $queryBuilder->whereHas('size', function ($sizeQuery) use ($selectedSize) {
                    $sizeQuery->where('name', $selectedSize);
                });
            })
            ->when(in_array($sortOrder, ['asc', 'desc']), function ($queryBuilder) use ($sortOrder) {
                $queryBuilder->orderBy('retail_price', $sortOrder);
            })
            ->when($stockStatus, function ($queryBuilder) use ($stockStatus) {
                if ($stockStatus === 'in') {
                    $queryBuilder->where('stock_quantity', '>', 0);
                } elseif ($stockStatus === 'out') {
                    $queryBuilder->where('stock_quantity', '<=', 0);
                }
            })
            ->when($selectedCategory, function ($queryBuilder) use ($selectedCategory) {
                $queryBuilder->where('category_id', $selectedCategory);
            });

        $products = $productsQuery->orderBy('created_at', 'desc')->paginate(8);

        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $request->input('search');
        $sortOrder = $request->input('sort');
        $selectedColor = $request->input('color');
        $selectedSize = $request->input('size');
        $stockStatus = $request->input('stockStatus');
        $selectedCategory = $request->input('selectedCategory');


        $productsQuery = Product::with('category', 'color', 'size', 'supplier')
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where(function ($subQuery) use ($query) {
                    $subQuery->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%")
                        ->orWhere('barcode', 'like', "%{$query}%");
                });
            })
            ->when($selectedColor, function ($queryBuilder) use ($selectedColor) {
                $queryBuilder->whereHas('color', function ($colorQuery) use ($selectedColor) {
                    $colorQuery->where('name', $selectedColor);
                });
            })
            ->when($selectedSize, function ($queryBuilder) use ($selectedSize) {
                $queryBuilder->whereHas('size', function ($sizeQuery) use ($selectedSize) {
                    $sizeQuery->where('name', $selectedSize);
                });
            })
            ->when(in_array($sortOrder, ['asc', 'desc']), function ($queryBuilder) use ($sortOrder) {
                $queryBuilder->orderBy('retail_price', $sortOrder);
            })
            ->when($stockStatus, function ($queryBuilder) use ($stockStatus) {
                if ($stockStatus === 'in') {
                    $queryBuilder->where('stock_quantity', '>', 0); // In Stock
                } elseif ($stockStatus === 'out') {
                    $queryBuilder->where('stock_quantity', '<=', 0); // Out of Stock
                }
            })
            ->when($selectedCategory, function ($queryBuilder) use ($selectedCategory) {
                $queryBuilder->where('category_id', $selectedCategory); // Filter by category
            });


        $count = $productsQuery->count();

        $products = $productsQuery->orderBy('created_at', 'desc')->paginate(8);


        // $allcategories = Category::with('parent')->get();
        $allcategories = Category::with('parent')->get()->map(function ($category) {
            $category->hierarchy_string = $category->hierarchy_string; // Access it
            return $category;
        });
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();
        $suppliers = Supplier::orderBy('created_at', 'desc')->get();


        return Inertia::render('Products/Index', [
            'products' => $products,
            'allcategories' => $allcategories,
            'colors' => $colors,
            'sizes' => $sizes,
            'suppliers' => $suppliers,
            'totalProducts' => $count,
            'search' => $query,
            'sort' => $sortOrder,
            'color' => $selectedColor,
            'size' => $selectedSize,
            'stockStatus' => $stockStatus,
            'selectedCategory' => $selectedCategory
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     $categories = Category::all();
    //     $products = Product::all();
    //     $suppliers = Supplier::all();
    //     $colors = Color::all();
    //     $sizes = Size::all();



    //     return Inertia::render('Products/Create', [
    //         'products' => $products,
    //         'categories' => $categories,
    //         'suppliers' => $suppliers,
    //         'colors' => $colors,
    //         'sizes' => $sizes,
    //     ]);
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|max:50',
            // 'code' => [
            //     'string',
            //     'max:50',
            //     Rule::unique('products')->whereNull('deleted_at'),
            // ],
            'size_id' => 'nullable|exists:sizes,id',
            'color_id' => 'nullable|exists:colors,id',
            'cost_price' => 'nullable|numeric|min:0',
            'retail_price' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value !== null && $value < (float)$request->input('cost_price')) {
                        $fail('The retail price must be greater than or equal to the cost price.');
                    }
                },
            ],
            'retail_discount' => 'nullable|numeric|min:0|max:100',
            'discounted_retail_price' => 'nullable|numeric|min:0',
            'wholesale_price' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value !== null && $value < (float)$request->input('cost_price')) {
                        $fail('The wholesale price must be greater than or equal to the cost price.');
                    }
                },
            ],
            'wholesale_discount' => 'nullable|numeric|min:0|max:100',
            'discounted_wholesale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'barcode' => 'nullable|string|unique:products',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'expire_date' => 'nullable|date',
            'batch_no' => 'nullable|max:50',
            'purchase_date' => 'nullable|date',
        ]);

        // dd($validated);

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $fileExtension = $request->file('image')->getClientOriginalExtension();
                $fileName = 'product_' . date("YmdHis") . '.' . $fileExtension;
                $path = $request->file('image')->storeAs('products', $fileName, 'public');
                $validated['image'] = 'storage/' . $path;
            }

            if (empty($validated['barcode'])) {
                $validated['barcode'] = $this->generateUniqueCode(8);
            }
            $validated['total_quantity'] = $validated['stock_quantity'] ?? 0;
            
            // Set default value for wholesale_price if it is not provided
            $validated['wholesale_price'] = $validated['wholesale_price'] ?? 0;
            $validated['wholesale_discount'] = $validated['wholesale_discount'] ?? 0;
            $validated['retail_discount'] = $validated['retail_discount'] ?? 0;

            // Create the product
            $product = Product::create($validated);
            // $product->update(['code' => 'PROD-' . $product->id]);

            // Add stock transaction if stock quantity is provided
            $stockQuantity = $validated['stock_quantity'] ?? 0; // Default to 0 if not provided
            if ($stockQuantity > 0) {
                StockTransaction::create([
                    'product_id' => $product->id,
                    'transaction_type' => 'Added',
                    'quantity' => $stockQuantity,
                    'transaction_date' => now(),
                    'supplier_id' => $validated['supplier_id'] ?? null,
                    'reason' => 'Initial stock',
                ]);
            }

            // Redirect with success message
            return redirect()->route('products.index')->banner('Product created successfully');
        } catch (\Exception $e) {
            // Log error and redirect back with an error message
            \Log::error('Error creating product: ' . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while creating the product. Please try again.');
        }
    }




    public function productVariantStore(Request $request)
    {

        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            // 'code' => 'required|string|max:50|unique:products,code, NULL,id,deleted_at,NULL',
            'barcode' => 'nullable|string|unique:products',
            'size_id' => 'nullable|exists:sizes,id',
            'color_id' => 'nullable|exists:colors,id',
            'cost_price' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'retail_discount' => 'nullable|numeric|min:0|max:100',
            'discounted_retail_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'wholesale_discount' => 'nullable|numeric|min:0|max:100',
            'discounted_wholesale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'image' => 'nullable|max:2048',
            'expire_date' => 'nullable|date',
        ]);


        try {


            if ($request->hasFile('image')) {
                $fileExtension = $request->file('image')->getClientOriginalExtension();
                $fileName = 'product_' . date("YmdHis") . '.' . $fileExtension;
                $path = $request->file('image')->storeAs('products', $fileName, 'public');
                $validated['image'] = 'storage/' . $path;
            }


            // Product::create($validated);

            if (empty($validated['barcode'])) {
                $validated['barcode'] = $this->generateUniqueCode(8);
            }

            // Set default value for wholesale_price if it is not provided
            $validated['wholesale_price'] = $validated['wholesale_price'] ?? 0;
            $validated['wholesale_discount'] = $validated['wholesale_discount'] ?? 0;
            $validated['retail_discount'] = $validated['retail_discount'] ?? 0;

            $product = Product::create($validated);


            // Add stock transaction if stock quantity is provided
            $stockQuantity = $validated['stock_quantity'] ?? 0; // Default to 0 if not provided
            if ($stockQuantity > 0) {
                StockTransaction::create([
                    'product_id' => $product->id,
                    'transaction_type' => 'Added',
                    'quantity' => $stockQuantity,
                    'transaction_date' => now(),
                    'supplier_id' => $validated['supplier_id'] ?? null,
                ]);
            }







            // Redirect with success message
            return redirect()->route('products.index')->banner('Product created successfully');
        } catch (\Exception $e) {
            // Log error and redirect back with an error message
            \Log::error('Error creating product: ' . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while creating the product. Please try again.');
        }
    }




    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }
        // $categories = Category::all();
        // $sizes = Size::all();
        // $suppliers = Supplier::all();
        // $colors = Color::all();
        $categories = Category::orderBy('created_at', 'desc')->get();
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();
        $suppliers = Supplier::orderBy('created_at', 'desc')->get();

        $product->load('category', 'color', 'size', 'suppliers');

        return Inertia::render('Products/Show', [

            'categories' => $categories,
            'product' => $product,
            'suppliers' => $suppliers,
            'colors' => $colors,
            'sizes' => $sizes,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('created_at', 'desc')->get();
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();
        $suppliers = Supplier::orderBy('created_at', 'desc')->get();

        return inertia('Products/Edit', [
            'product' => $product,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'colors' => $colors,
            'sizes' => $sizes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */



    public function update(Request $request, Product $product)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'string|max:255',
            // 'code' => 'nullable|string|max:50',
            // 'code' => 'string|max:50|unique:products,code,' . $product->id . ',id,deleted_at,NULL',
            'size_id' => 'nullable|exists:sizes,id',
            'color_id' => 'nullable|exists:colors,id',
            'cost_price' => 'numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'retail_discount' => 'nullable|numeric|min:0|max:100',
            'discounted_retail_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'wholesale_discount' => 'nullable|numeric|min:0|max:100',
            'discounted_wholesale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'image' => 'nullable|max:2048',
            'expire_date' => 'nullable|date',
            'batch_no' => 'nullable|max:50',
            'purchase_date' => 'nullable|date'
        ]);

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($product->image && Storage::disk('public')->exists(str_replace('storage/', '', $product->image))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $product->image));
            }

            // Save the new image
            $fileExtension = $request->file('image')->getClientOriginalExtension();
            $fileName = 'product_' . date("YmdHis") . '.' . $fileExtension;
            $path = $request->file('image')->storeAs('products', $fileName, 'public');
            $validated['image'] = 'storage/' . $path;
        } else {
            $validated['image'] = $product->image;
        }

        // Calculate stock change
        $stockChange = $validated['stock_quantity'] - $product->stock_quantity;

        // Determine transaction type
        $transactionType = $stockChange > 0 ? 'Added' : 'Deducted';
        $validated['total_quantity'] = $validated['stock_quantity'];

        // Set default values if fields are cleared
        $validated['wholesale_price'] = $validated['wholesale_price'] ?? 0;
        $validated['wholesale_discount'] = $validated['wholesale_discount'] ?? 0;
        $validated['retail_discount'] = $validated['retail_discount'] ?? 0;

        // Update product
        $product->update($validated);



        if ($stockChange !== 0) {
            // Determine transaction type
            $transactionType = $stockChange > 0 ? 'Added' : 'Deducted';

            StockTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => $transactionType,
                'quantity' => abs($stockChange),
                'transaction_date' => now(),
                'supplier_id' => $validated['supplier_id'] ?? null,
            ]);
        }

        return redirect()->route('products.index')->with('banner', 'Product updated successfully');
    }

    public function adjustStock(Request $request, Product $product)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'action' => ['required', Rule::in(['add', 'deduct'])],
            'quantity' => 'required|integer|min:1',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reason' => 'nullable|string|max:255',
            'cost_price' => 'nullable|numeric|min:0',
            'retail_price' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value !== null && $value < (float)$request->input('cost_price', 0)) {
                        $fail('The retail price must be greater than or equal to the cost price.');
                    }
                },
            ],
            'retail_discount' => 'nullable|numeric|min:0|max:100',
            'discounted_retail_price' => 'nullable|numeric|min:0',
            'wholesale_price' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value !== null && $value < (float)$request->input('cost_price', 0)) {
                        $fail('The wholesale price must be greater than or equal to the cost price.');
                    }
                },
            ],
            'wholesale_discount' => 'nullable|numeric|min:0|max:100',
            'discounted_wholesale_price' => 'nullable|numeric|min:0',
        ]);

        $quantity = $validated['quantity'];

        if ($validated['action'] === 'deduct') {
            if ($quantity > $product->stock_quantity) {
                return redirect()->back()->withErrors(['quantity' => 'Cannot deduct more than available total stock.']);
            }
            
            // Deduct stock from the latest/first available batch (FIFO logic)
            $remainingToDeduct = $quantity;
            $batches = $product->batches()->where('stock_quantity', '>', 0)->orderBy('created_at', 'asc')->get();
            
            foreach ($batches as $batch) {
                if ($remainingToDeduct <= 0) break;
                
                if ($batch->stock_quantity >= $remainingToDeduct) {
                    $batch->stock_quantity -= $remainingToDeduct;
                    $batch->save();
                    $remainingToDeduct = 0;
                } else {
                    $remainingToDeduct -= $batch->stock_quantity;
                    $batch->stock_quantity = 0;
                    $batch->save();
                }
            }

            $product->stock_quantity -= $quantity;
            $transactionType = 'Deducted';
        } else {
            // Action is ADD
            // Ensure proper default values for missing DB columns if any
            $validated['wholesale_price'] = $validated['wholesale_price'] ?? 0;
            $validated['wholesale_discount'] = $validated['wholesale_discount'] ?? 0;
            $validated['retail_discount'] = $validated['retail_discount'] ?? 0;

            // Check if user provided prices that differ from the main product's prices
            $pricesChanged = false;
            
            // Only check for new prices if they were explicitly provided in the request
            if ($request->filled('cost_price')) {
                if (
                    (float)$validated['cost_price'] !== (float)$product->cost_price ||
                    (float)$validated['retail_price'] !== (float)$product->retail_price ||
                    (float)$validated['wholesale_price'] !== (float)$product->wholesale_price
                ) {
                    $pricesChanged = true;
                }
            }

            if ($pricesChanged) {
                // Create a NEW batch
                \App\Models\ProductBatch::create([
                    'product_id' => $product->id,
                    'batch_no' => $product->batch_no ? $product->batch_no . '-' . time() : 'B-' . time(),
                    'stock_quantity' => $quantity,
                    'cost_price' => $validated['cost_price'],
                    'retail_price' => $validated['retail_price'],
                    'retail_discount' => $validated['retail_discount'] ?? 0,
                    'discounted_retail_price' => $validated['discounted_retail_price'] ?? null,
                    'wholesale_price' => $validated['wholesale_price'] ?? 0,
                    'wholesale_discount' => $validated['wholesale_discount'] ?? 0,
                    'discounted_wholesale_price' => $validated['discounted_wholesale_price'] ?? null,
                    'expire_date' => $product->expire_date,
                ]);

                // Update the main product to reflect the latest prices
                $product->update([
                    'cost_price' => $validated['cost_price'],
                    'retail_price' => $validated['retail_price'],
                    'retail_discount' => $validated['retail_discount'] ?? 0,
                    'discounted_retail_price' => $validated['discounted_retail_price'] ?? null,
                    'wholesale_price' => $validated['wholesale_price'] ?? 0,
                    'wholesale_discount' => $validated['wholesale_discount'] ?? 0,
                    'discounted_wholesale_price' => $validated['discounted_wholesale_price'] ?? null,
                ]);
            } else {
                // Find the latest batch and add to it
                $latestBatch = $product->batches()->latest()->first();
                if ($latestBatch) {
                    $latestBatch->stock_quantity += $quantity;
                    $latestBatch->save();
                } else {
                    // Fallback if no batches exist (shouldn't happen due to migration)
                    \App\Models\ProductBatch::create([
                        'product_id' => $product->id,
                        'stock_quantity' => $quantity,
                        'cost_price' => $product->cost_price,
                        'retail_price' => $product->retail_price,
                        'retail_discount' => $product->retail_discount,
                        'discounted_retail_price' => $product->discounted_retail_price,
                        'wholesale_price' => $product->wholesale_price,
                        'wholesale_discount' => $product->wholesale_discount,
                        'discounted_wholesale_price' => $product->discounted_wholesale_price,
                        'expire_date' => $product->expire_date,
                    ]);
                }
            }

            $product->stock_quantity += $quantity;
            $transactionType = 'Added';
        }

        $product->total_quantity = $product->stock_quantity;
        $product->save();

        StockTransaction::create([
            'product_id' => $product->id,
            'transaction_type' => $transactionType,
            'quantity' => $quantity,
            'transaction_date' => now(),
            'supplier_id' => $validated['supplier_id'] ?? $product->supplier_id,
            'reason' => $validated['reason'] ?? null,
        ]);

        return redirect()->route('products.index')->with('banner', 'Stock updated successfully');
    }


    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(Product $product)
    // {
    //     if (!Gate::allows('hasRole', ['Admin'])) {
    //         abort(403, 'Unauthorized');
    //     }

    //     $imagePath = str_replace('storage/', '', $product->image);

    //     // Check for other products using the same image
    //     $imageUsageCount = Product::where('image', $product->image)
    //         ->where('id', '!=', $product->id)
    //         ->count();

    //     if ($imageUsageCount === 0 && Storage::disk('public')->exists($imagePath)) {
    //         // Delete the image only if no other products are using it
    //         Storage::disk('public')->delete($imagePath);
    //     }

    //     $product->delete();

    //     return redirect()->route('products.index')->banner('Product Deleted successfully.');
    // }



    public function destroy(Product $product)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        // Prepare to delete the image
        $imagePath = str_replace('storage/', '', $product->image);
        $imageUsageCount = Product::where('image', $product->image)
            ->where('id', '!=', $product->id)
            ->count();

        if ($imageUsageCount === 0 && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        try {
            // Log the stock transaction
            StockTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => 'Deleted',
                'quantity' => $product->stock_quantity ?? 0, // Fallback to 0 if undefined
                'transaction_date' => now(),
                'supplier_id' => $product->supplier_id ?? null, // Handle potential null value
            ]);
        } catch (\Exception $e) {
            // Log error and return a failure message
            report($e);
            return redirect()->route('products.index')->withErrors('Failed to log stock transaction. Please try again.');
        }

        // Delete the product
        $product->delete();

        return redirect()->route('products.index')->banner('Product Deleted successfully.');
    }


     public function getPromotionItems($productId)
    {
        // Fetch promotion items where promotion_id equals $productId
        $promotionItems = PromotionItem::where('promotion_id', $productId)
            ->with('product') // Include related product details
            ->get();

        // Check if any promotion items are found
        if ($promotionItems->isEmpty()) {
            return response()->json(['error' => 'No promotion items found for this promotion ID.'], 404);
        }

        return response()->json([
            'promotion_items' => $promotionItems,
        ]);
    }


public function fetchProducts2(Request $request)
{
    $query = $request->input('search');
    $sortOrder = $request->input('sort');
    $selectedColor = $request->input('color');
    $selectedSize = $request->input('size');
    $stockStatus = $request->input('stockStatus');
    $selectedCategory = $request->input('selectedCategory');

      $productsQuery = Product::with('category', 'color', 'size', 'supplier')
        ->whereNotNull('products.name')
        ->where('products.is_promotion', '!=', 1)
        ->with([
        'promotionItems:id,promotion_id,product_id,quantity',
        'promotionItems.product:id,name',
        'batches' => function($q) {
            $q->where('stock_quantity', '>', 0)->orderBy('created_at', 'asc');
        }
    ])
        ->when($query, function ($qb) use ($query) {
            $qb->where(function ($sub) use ($query) {
                $sub->where('products.name', 'like', "%{$query}%")
                    ->orWhere('products.code', 'like', "%{$query}%")
                    ->orWhere('products.barcode', 'like', "%{$query}%");
            });
        })
        ->when($selectedColor, function ($qb) use ($selectedColor) {
            $qb->whereHas('color', function ($cq) use ($selectedColor) {
                $cq->where('name', $selectedColor);
            });
        })
        ->when($selectedSize, function ($qb) use ($selectedSize) {
            $qb->whereHas('size', function ($sq) use ($selectedSize) {
                $sq->where('name', $selectedSize);
            });
        })
        ->when($stockStatus, function ($qb) use ($stockStatus) {
            if ($stockStatus === 'in') {
                $qb->where('products.stock_quantity', '>', 0);
            } elseif ($stockStatus === 'out') {
                $qb->where('products.stock_quantity', '<=', 0);
            }
        })
        ->when($selectedCategory, function ($qb) use ($selectedCategory) {
            $qb->where('products.category_id', $selectedCategory);
        });

    // Always push out-of-stock to the end
    $productsQuery->orderByRaw("CASE WHEN products.stock_quantity > 0 THEN 0 ELSE 1 END");

    // Secondary sort: price or FIFO by created_at
    if (in_array($sortOrder, ['asc', 'desc'])) {
        $productsQuery->orderBy('products.retail_price', $sortOrder);
    } else {
        // FIFO: oldest first within each stock group
        $productsQuery->orderBy('products.created_at', 'desc');
    }

    $products = $productsQuery->paginate(8);


    return response()->json([
        'products' => $products,
    ]);
}

  public function addPromotion(Request $request)
    {
        $allcategories = Category::with('parent')->get()->map(function ($category) {
            $category->hierarchy_string = $category->hierarchy_string; // Access it
            return $category;
        });
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();


        return Inertia::render('Products/Promotions', [
            'allcategories' => $allcategories,
            'colors' => $colors,
            'sizes' => $sizes,
        ]);
    }






 public function submitPromotion(Request $request)
{


    if (!Gate::allows('hasRole', ['Admin'])) {
        abort(403, 'Unauthorized');
    }

    $validated = $request->validate([
        'category_id'       => 'required|exists:categories,id',
        'name'              => 'required|string|max:255',
        'size_id'           => 'nullable|exists:sizes,id',
        'color_id'          => 'nullable|exists:colors,id',
        'cost_price'        => 'required|numeric|min:0',
        'retail_price'      => 'required|numeric|min:0',
        'retail_discount'   => 'nullable|numeric|min:0|max:100',
        'discounted_retail_price' => 'nullable|numeric|min:0',
        'wholesale_price'   => 'nullable|numeric|min:0',
        'wholesale_discount' => 'nullable|numeric|min:0|max:100',
        'discounted_wholesale_price' => 'nullable|numeric|min:0',
        'stock_quantity'    => 'required|integer|min:0',
        'supplier_id'       => 'nullable|exists:suppliers,id',
        'barcode'           => ['nullable','string',\Illuminate\Validation\Rule::unique('products','barcode')->whereNull('deleted_at')],
        'image'             => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        'description'       => 'nullable|string',
        'products'                 => 'required|array|min:1',
        'products.*.id'            => 'required|exists:products,id',
        'products.*.quantity'      => 'required|integer|min:1',
    ], [
        'category_id.required' => 'Category is required.',
        'category_id.exists'   => 'The selected category is invalid.',
    ]);

    try {
        return \DB::transaction(function () use ($request, $validated) {
            $data = $validated;

            if ($request->hasFile('image')) {
                $ext  = $request->file('image')->getClientOriginalExtension();
                $name = 'product_' . now()->format('YmdHis') . '.' . $ext;
                $path = $request->file('image')->storeAs('products', $name, 'public');
                $data['image'] = 'storage/' . $path;
            }

            if (empty($data['barcode'])) {
                $data['barcode'] = $this->generateUniqueCode(8);
            }

            $items = collect($data['products'])
                ->groupBy('id')
                ->map(fn($g) => ['id' => $g->first()['id'], 'quantity' => $g->sum('quantity')])
                ->values()
                ->all();

            unset($data['products']);

            foreach ($items as $i) {
                $p = \App\Models\Product::lockForUpdate()->find($i['id']);
                if (!$p || $p->stock_quantity < $i['quantity']) {
                    abort(422, 'Insufficient stock for product ID '.$i['id']);
                }
            }

            $data['is_promotion']   = true;
            $data['total_quantity'] = (int)($data['stock_quantity'] ?? 0);

            $promotion = \App\Models\Product::create($data);
            $promotion->update(['code' => 'PROD-' . $promotion->id]);

            foreach ($items as $i) {
                \App\Models\PromotionItem::create([
                    'product_id'   => $i['id'],
                    'promotion_id' => $promotion->id,
                    'quantity'     => (int)$i['quantity'],
                ]);
            }

            foreach ($items as $i) {
                $p = \App\Models\Product::lockForUpdate()->find($i['id']);
                $p->stock_quantity  = (int)$p->stock_quantity - (int)$i['quantity'];
                $p->total_quantity  = (int)($p->total_quantity ?? $p->stock_quantity) - (int)$i['quantity'];
                if ($p->total_quantity < 0) $p->total_quantity = 0;
                $p->save();

                \App\Models\StockTransaction::create([
                    'product_id'       => $p->id,
                    'transaction_type' => 'Deducted',
                    'quantity'         => (int)$i['quantity'],
                    'transaction_date' => now(),
                    'supplier_id'      => $validated['supplier_id'] ?? null,
                ]);
            }

            return redirect()->route('products.index')->banner('Promotion created successfully');
        });
    } catch (\Throwable $e) {
        \Log::error('Error creating promotion', ['error' => $e->getMessage()]);
        return back()->with('error', 'An error occurred while creating the promotion. Please try again.')->withInput();
    }
}
}
