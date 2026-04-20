<?php

namespace App\Http\Controllers\Api;

use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        return $this->jsonResponse($products);
    }

    public function show(int $id): JsonResponse
    {
        return $this->jsonResponse(
            $this->mapProduct($this->productService->detail($id))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'productCode' => ['required', 'string'],
            'productName' => ['required', 'string'],
            'unitName' => ['required', 'string'],
        ]);

        $this->productService->create([
            'ProductCode' => $validated['productCode'],
            'ProductName' => $validated['productName'],
            'UnitName' => $validated['unitName'] ?? null,
        ], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'productCode' => ['required', 'string'],
            'productName' => ['required', 'string'],
            'unitName' => ['required', 'string'],
        ]);

        $this->productService->update([
            'Id' => $validated['id'],
            'ProductCode' => $validated['productCode'],
            'ProductName' => $validated['productName'],
            'UnitName' => $validated['unitName'] ?? null,
        ], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);

        $this->productService->delete((int) $validated['id'], $this->currentUserId() ?? 0);

        return $this->jsonResponse(true);
    }

    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:csv'],
        ]);

        return $this->jsonResponse(
            $this->productService->importExcel($validated['file'], $this->currentUserId() ?? 0)
        );
    }

    public function export(): StreamedResponse
    {
        return $this->productService->exportExcel();
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
