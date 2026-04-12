<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function all(): Collection
    {
        return Product::query()
            ->notDeleted()
            ->orderByDesc('Id')
            ->get();
    }

    public function detail(int $id): Product
    {
        return Product::query()->notDeleted()->findOrFail($id);
    }

    public function create(array $attributes, int $actorId): bool
    {
        Product::query()->create([
            'ProductCode' => $attributes['ProductCode'],
            'ProductName' => $attributes['ProductName'],
            'UnitName' => $attributes['UnitName'] ?? '',
            'IsDeleted' => false,
            'CreatedBy' => $actorId,
            'UpdatedBy' => null,
            'DeleteBy' => null,
            'CreatedAt' => now(),
            'UpdatedAt' => null,
            'DeleteAt' => null,
        ]);

        return true;
    }

    public function update(array $attributes, int $actorId): void
    {
        $product = Product::query()->notDeleted()->findOrFail((int) $attributes['Id']);

        $product->fill([
            'ProductCode' => $attributes['ProductCode'],
            'ProductName' => $attributes['ProductName'],
            'UnitName' => $attributes['UnitName'] ?? '',
            'UpdatedBy' => $actorId,
            'UpdatedAt' => now(),
        ]);

        $product->save();
    }

    public function delete(int $id, int $actorId): void
    {
        $product = Product::query()->notDeleted()->findOrFail($id);

        $product->fill([
            'IsDeleted' => true,
            'DeleteBy' => $actorId,
            'DeleteAt' => now(),
            'UpdatedBy' => $actorId,
            'UpdatedAt' => now(),
        ]);

        $product->save();
    }
}
