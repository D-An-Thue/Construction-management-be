<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $productCode = $this->normalizeProductCode((string) $attributes['ProductCode']);
        $productName = trim((string) ($attributes['ProductName'] ?? ''));
        $unitName = trim((string) ($attributes['UnitName'] ?? ''));

        $this->validateRequiredFields($productCode, $productName, $unitName);

        $duplicate = Product::query()
            ->notDeleted()
            ->where('ProductCode', $productCode)
            ->exists();

        if ($duplicate) {
            throw new \RuntimeException('Mã sản phẩm đã tồn tại.');
        }

        Product::query()->create([
            'ProductCode' => $productCode,
            'ProductName' => $productName,
            'UnitName' => $unitName,
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

        $productCode = $this->normalizeProductCode((string) $attributes['ProductCode']);
        $productName = trim((string) ($attributes['ProductName'] ?? ''));
        $unitName = trim((string) ($attributes['UnitName'] ?? ''));

        $this->validateRequiredFields($productCode, $productName, $unitName);

        $duplicate = Product::query()
            ->notDeleted()
            ->where('ProductCode', $productCode)
            ->where('Id', '!=', (int) $attributes['Id'])
            ->exists();

        if ($duplicate) {
            throw new \RuntimeException('Mã sản phẩm đã tồn tại.');
        }

        $product->fill([
            'ProductCode' => $productCode,
            'ProductName' => $productName,
            'UnitName' => $unitName,
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

    public function importExcel(UploadedFile $file, int $actorId): array
    {
        $filename = strtolower((string) $file->getClientOriginalName());

        if (! str_ends_with($filename, '.csv')) {
            throw new \RuntimeException('Môi trường hiện tại chưa hỗ trợ .xlsx (thiếu ext-zip). Vui lòng import file .csv.');
        }

        if (($file->getSize() ?? 0) <= 0) {
            throw new \RuntimeException('File import rỗng.');
        }

        if (($file->getSize() ?? 0) > 10 * 1024 * 1024) {
            throw new \RuntimeException('Kích thước file vượt quá giới hạn 10MB.');
        }

        $rows = array_map('str_getcsv', file($file->getRealPath()) ?: []);
        if (count($rows) < 2) {
            throw new \RuntimeException('File import không có dữ liệu.');
        }

        $existingCodes = Product::query()
            ->notDeleted()
            ->pluck('ProductCode')
            ->map(fn ($code) => mb_strtolower((string) $code))
            ->all();
        $existingLookup = array_fill_keys($existingCodes, true);

        $rowCodeLookup = [];
        $rowsToInsert = [];

        for ($index = 1; $index < count($rows); $index++) {
            $rowNumber = $index + 1;
            $row = $rows[$index];

            $productCode = $this->normalizeProductCode((string) ($row[0] ?? ''));
            $productName = trim((string) ($row[1] ?? ''));
            $unitName = trim((string) ($row[2] ?? ''));

            if ($productCode === '') {
                throw new \RuntimeException("Dòng {$rowNumber}: Mã sản phẩm là bắt buộc.");
            }
            if ($productName === '') {
                throw new \RuntimeException("Dòng {$rowNumber}: Tên sản phẩm là bắt buộc.");
            }
            if ($unitName === '') {
                throw new \RuntimeException("Dòng {$rowNumber}: Đơn vị tính là bắt buộc.");
            }

            $codeKey = mb_strtolower($productCode);
            if (isset($existingLookup[$codeKey]) || isset($rowCodeLookup[$codeKey])) {
                throw new \RuntimeException("Dòng {$rowNumber}: Mã sản phẩm đã tồn tại ({$productCode}).");
            }

            $rowCodeLookup[$codeKey] = true;
            $rowsToInsert[] = [
                'ProductCode' => $productCode,
                'ProductName' => $productName,
                'UnitName' => $unitName,
                'IsDeleted' => false,
                'CreatedBy' => $actorId,
                'UpdatedBy' => null,
                'DeleteBy' => null,
                'CreatedAt' => now(),
                'UpdatedAt' => null,
                'DeleteAt' => null,
            ];
        }

        if ($rowsToInsert === []) {
            throw new \RuntimeException('File import không có dữ liệu hợp lệ.');
        }

        Product::query()->insert($rowsToInsert);

        return [
            'ImportedCount' => count($rowsToInsert),
        ];
    }

    public function exportExcel(): StreamedResponse
    {
        $products = $this->all();
        $filename = 'products-'.now()->format('YmdHis').'.csv';

        return response()->streamDownload(function () use ($products): void {
            $handle = fopen('php://output', 'wb');
            if (! $handle) {
                throw new \RuntimeException('Không thể tạo file export.');
            }

            fputcsv($handle, ['productCode', 'productName', 'unitName']);

            foreach ($products as $product) {
                fputcsv($handle, [
                    (string) $product->ProductCode,
                    (string) $product->ProductName,
                    (string) $product->UnitName,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validateRequiredFields(string $productCode, string $productName, string $unitName): void
    {
        if ($productCode === '') {
            throw new \RuntimeException('Mã sản phẩm là bắt buộc.');
        }

        if ($productName === '') {
            throw new \RuntimeException('Tên sản phẩm là bắt buộc.');
        }

        if ($unitName === '') {
            throw new \RuntimeException('Đơn vị tính là bắt buộc.');
        }
    }

    private function normalizeProductCode(string $productCode): string
    {
        $normalized = trim($productCode);

        if (str_starts_with($normalized, '#')) {
            $normalized = trim(substr($normalized, 1));
        }

        if (str_contains($normalized, '#')) {
            throw new \RuntimeException('Mã sản phẩm không hợp lệ.');
        }

        return $normalized;
    }
}
