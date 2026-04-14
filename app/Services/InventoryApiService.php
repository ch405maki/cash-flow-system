<?php
// app/Services/InventoryApiService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InventoryApiService
{
    protected $baseUrl;
    
    public function __construct()
    {
        $this->baseUrl = config('services.inventory.base_url', 'http://192.168.0.145');
    }
    
    /**
     * Get all products from inventory system
     */
    public function getProducts(): array
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . '/api/productCode');
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }
            
            Log::error('Failed to fetch products from inventory', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return [
                'success' => false,
                'data' => [],
                'error' => 'Failed to fetch products'
            ];
            
        } catch (\Exception $e) {
            Log::error('Inventory API connection error', [
                'message' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'data' => [],
                'error' => 'Cannot connect to inventory system'
            ];
        }
    }
    
    /**
     * Get single product by ID
     */
    public function getProductById($id): array
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . "/api/productCode/{$id}");
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }
            
            return [
                'success' => false,
                'data' => null,
                'error' => 'Product not found'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a transaction in inventory system (Out/Deduction)
     */
    public function createTransaction(array $data): array
    {
        try {
            $response = Http::timeout(10)->post($this->baseUrl . '/api/transactions', $data);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status_code' => $response->status()
                ];
            }
            
            Log::error('Failed to create inventory transaction', [
                'status' => $response->status(),
                'body' => $response->body(),
                'data_sent' => $data
            ]);
            
            return [
                'success' => false,
                'error' => 'Inventory API returned error: ' . $response->status(),
                'status_code' => $response->status()
            ];
            
        } catch (\Exception $e) {
            Log::error('Inventory transaction connection error', [
                'message' => $e->getMessage(),
                'data_sent' => $data
            ]);
            
            return [
                'success' => false,
                'error' => 'Cannot connect to inventory system: ' . $e->getMessage()
            ];
        }
    }
}