<?php
// app/Http/Controllers/Api/InventoryController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InventoryApiService;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    protected $inventoryApi;
    
    public function __construct(InventoryApiService $inventoryApi)
    {
        $this->inventoryApi = $inventoryApi;
    }
    
    /**
     * Get all products from inventory system
     */
    public function getProducts(): JsonResponse
    {
        $result = $this->inventoryApi->getProducts();
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => $result['data']
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Failed to fetch products',
            'data' => []
        ], 500);
    }
}