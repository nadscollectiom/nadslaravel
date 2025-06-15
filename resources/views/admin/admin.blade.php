<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard - All Products') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="productManager()">

            {{-- Top Bar --}}
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-700">All Products</h3>
                <a href="#" @click.prevent="openAdd = true"
                    class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    + Add New Product
                </a>
            </div>

            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-4 text-green-700 bg-green-100 border border-green-300 rounded p-3">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Products Table --}}
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Sizes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($products as $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="Image"
                                            class="w-16 h-16 object-cover rounded">
                                    @else
                                        <span class="text-gray-500">No Image</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $product->title }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $product->category->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    ${{ number_format($product->price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $product->stock }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($product->sizes && count($product->sizes) > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($product->sizes as $size)
                                                <span class="px-2 py-1 bg-gray-100 text-xs rounded">{{ $size }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-500">No sizes</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 space-x-2">
                                    <a href="#" @click.prevent='openEditModal(@json($product))'
                                        class="inline-block px-3 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $products->links() }}
            </div>

            <!-- Add Product Modal -->
            <div x-show="openAdd" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div @click.away="openAdd = false" class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 max-h-screen overflow-y-auto">
                    <h2 class="text-lg font-bold mb-4">Add New Product</h2>
                    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Title</label>
                            <input name="title" class="w-full border px-3 py-2 rounded" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Category</label>
                            <select name="category_id" class="w-full border px-3 py-2 rounded" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Price</label>
                            <input type="number" step="0.01" name="price"
                                class="w-full border px-3 py-2 rounded" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Stock</label>
                            <input type="number" name="stock" class="w-full border px-3 py-2 rounded" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Sizes</label>
                            <div class="space-y-3">
                                <!-- Predefined Sizes -->
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Select sizes:</label>
                                    <div class="flex flex-wrap gap-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="sizes[]" value="Small" class="mr-2">
                                            <span class="text-sm">Small</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="sizes[]" value="Medium" class="mr-2">
                                            <span class="text-sm">Medium</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="sizes[]" value="Large" class="mr-2">
                                            <span class="text-sm">Large</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="sizes[]" value="XL" class="mr-2">
                                            <span class="text-sm">XL</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="sizes[]" value="XXL" class="mr-2">
                                            <span class="text-sm">XXL</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Custom Size -->
                                <div class="border-t pt-3">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Custom size:</label>
                                    <div class="flex gap-2">
                                        <input type="text" x-model="customSize" 
                                               placeholder="Enter custom size" 
                                               class="flex-1 border px-3 py-2 rounded text-sm">
                                        <button type="button" @click="addCustomSize()" 
                                                class="px-3 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                                            Add
                                        </button>
                                    </div>
                                    <!-- Display added custom sizes -->
                                    <div class="mt-2 flex flex-wrap gap-2" x-show="customSizes.length > 0">
                                        <template x-for="(size, index) in customSizes" :key="index">
                                            <div class="flex items-center bg-blue-100 px-2 py-1 rounded">
                                                <span x-text="size" class="text-sm"></span>
                                                <input type="hidden" name="sizes[]" :value="size">
                                                <button type="button" @click="customSizes.splice(index, 1)" 
                                                        class="ml-2 text-red-500 hover:text-red-700 text-sm">×</button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Image</label>
                            <input type="file" name="image" class="w-full">
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" @click="openAdd = false"
                                class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Product Modal -->
            <div x-show="openEdit" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div @click.away="openEdit = false" class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 max-h-screen overflow-y-auto">
                    <h2 class="text-lg font-bold mb-4">Edit Product</h2>
                    <form :action="'/products/' + editProduct.id" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Title</label>
                            <input name="title" x-model="editProduct.title" class="w-full border px-3 py-2 rounded" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Category</label>
                            <select name="category_id" class="w-full border px-3 py-2 rounded" required>
                                <template x-for="category in categories" :key="category.id">
                                    <option :value="category.id" x-text="category.name"
                                        :selected="category.id == editProduct.category_id"></option>
                                </template>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Price</label>
                            <input type="number" step="0.01" name="price" x-model="editProduct.price"
                                class="w-full border px-3 py-2 rounded" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Stock</label>
                            <input type="number" name="stock" x-model="editProduct.stock"
                                class="w-full border px-3 py-2 rounded" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Sizes</label>
                            <div class="space-y-3">
                                <!-- Predefined Sizes -->
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Select sizes:</label>
                                    <div class="flex flex-wrap gap-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="sizes[]" value="Small" 
                                                   :checked="editProduct.sizes && editProduct.sizes.includes('Small')" 
                                                   class="mr-2">
                                            <span class="text-sm">Small</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="sizes[]" value="Medium" 
                                                   :checked="editProduct.sizes && editProduct.sizes.includes('Medium')" 
                                                   class="mr-2">
                                            <span class="text-sm">Medium</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="sizes[]" value="Large" 
                                                   :checked="editProduct.sizes && editProduct.sizes.includes('Large')" 
                                                   class="mr-2">
                                            <span class="text-sm">Large</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="sizes[]" value="XL" 
                                                   :checked="editProduct.sizes && editProduct.sizes.includes('XL')" 
                                                   class="mr-2">
                                            <span class="text-sm">XL</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="sizes[]" value="XXL" 
                                                   :checked="editProduct.sizes && editProduct.sizes.includes('XXL')" 
                                                   class="mr-2">
                                            <span class="text-sm">XXL</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Custom Sizes (existing + new) -->
                                <div class="border-t pt-3">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Custom sizes:</label>
                                    
                                    <!-- Display existing custom sizes -->
                                    <div class="mb-2 flex flex-wrap gap-2" x-show="editCustomSizes.length > 0">
                                        <template x-for="(size, index) in editCustomSizes" :key="index">
                                            <div class="flex items-center bg-blue-100 px-2 py-1 rounded">
                                                <span x-text="size" class="text-sm"></span>
                                                <input type="hidden" name="sizes[]" :value="size">
                                                <button type="button" @click="editCustomSizes.splice(index, 1)" 
                                                        class="ml-2 text-red-500 hover:text-red-700 text-sm">×</button>
                                            </div>
                                        </template>
                                    </div>
                                    
                                    <!-- Add new custom size -->
                                    <div class="flex gap-2">
                                        <input type="text" x-model="editCustomSize" 
                                               placeholder="Enter custom size" 
                                               class="flex-1 border px-3 py-2 rounded text-sm">
                                        <button type="button" @click="addEditCustomSize()" 
                                                class="px-3 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                                            Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Image</label>
                            <input type="file" name="image" class="w-full">
                            <template x-if="editProduct.image">
                                <div class="mt-2">
                                    <span class="text-sm text-gray-500">Current Image:</span>
                                    <img :src="'/storage/' + editProduct.image" class="w-16 h-16 object-cover rounded mt-1">
                                </div>
                            </template>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" @click="openEdit = false"
                                class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function productManager() {
            return {
                openAdd: false,
                openEdit: false,
                editProduct: {},
                categories: @json($categories ?? []),
                customSize: '',
                customSizes: [],
                editCustomSize: '',
                editCustomSizes: [],

                openEditModal(product) {
                    this.editProduct = {
                        ...product,
                        category_id: product.category_id || '',
                        sizes: product.sizes ? [...product.sizes] : []
                    };
                    
                    // Separate predefined sizes from custom sizes
                    const predefinedSizes = ['Small', 'Medium', 'Large', 'XL', 'XXL'];
                    this.editCustomSizes = this.editProduct.sizes ? 
                        this.editProduct.sizes.filter(size => !predefinedSizes.includes(size)) : [];
                    
                    this.editCustomSize = '';
                    this.openEdit = true;
                },

                addCustomSize() {
                    if (this.customSize.trim() && !this.customSizes.includes(this.customSize.trim())) {
                        this.customSizes.push(this.customSize.trim());
                        this.customSize = '';
                    }
                },

                addEditCustomSize() {
                    if (this.editCustomSize.trim() && !this.editCustomSizes.includes(this.editCustomSize.trim())) {
                        this.editCustomSizes.push(this.editCustomSize.trim());
                        this.editCustomSize = '';
                    }
                }
            }
        }
    </script>

</x-app-layout>