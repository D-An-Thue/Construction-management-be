<?php

namespace App\Http\Controllers\Api;

use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(): JsonResponse
    {
        $products = $this->productService->all()
            ->map(fn ($product) => $this->mapProduct($product))
            ->values();

        return response()->json($products);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(
            $this->mapProduct($this->productService->detail($id))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'productCode' => ['required', 'string'],
            'productName' => ['required', 'string'],
            'unitName' => ['nullable', 'string'],
        ]);

        $this->productService->create([
            'ProductCode' => $validated['productCode'],
            'ProductName' => $validated['productName'],
            'UnitName' => $validated['unitName'] ?? null,
        ], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'productCode' => ['required', 'string'],
            'productName' => ['required', 'string'],
            'unitName' => ['nullable', 'string'],
        ]);

        $this->productService->update([
            'Id' => $validated['id'],
            'ProductCode' => $validated['productCode'],
            'ProductName' => $validated['productName'],
            'UnitName' => $validated['unitName'] ?? null,
        ], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);

        $this->productService->delete((int) $validated['id'], $this->currentUserId() ?? 0);

        return response()->json(true);
    }

    public function import(): JsonResponse
    {
        return response()->json([
            'status' => 501,
            'title' => 'Not Implemented',
            'detail' => 'Product import endpoint is not migrated yet.',
        ], 501);
    }

    public function export(): JsonResponse
    {
        return response()->json([
            'status' => 501,
            'title' => 'Not Implemented',
            'detail' => 'Product export endpoint is not migrated yet.',
        ], 501);
    }

    private function mapProduct(object $product): array
    {
        return [
            'Id' => $product->Id,
            'ProductCode' => $product->ProductCode,
            'ProductName' => $product->ProductName,
            'UnitName' => $product->UnitName,
            'CreatedAt' => $product->CreatedAt,
            'UpdatedAt' => $product->UpdatedAt,
        ];
    }
}
