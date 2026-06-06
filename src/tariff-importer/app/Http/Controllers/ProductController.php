<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ExcelImporterService;
use App\Models\Product;
use App\Services\ProductService;

class ProductController extends Controller
{
    /**
     * List imported products with seek-based pagination.
     *
     * Query params:
     * - brand (string, optional): filter by brand.
     * - reference (string, optional): filter by reference.
     * - limit (int, optional): page size (default 100, capped at 200).
     * - last_id (int, optional): cursor for next page.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(
        Request        $request,
        ProductService $service
    ): JsonResponse {
        $filters = $request->only(['brand', 'reference']);
        $limit = (int) $request->query('limit', 100);

        $result = $service->search($filters, $limit);

        return response()->json($result);
    }

    /**
     * Import products from an Excel file.
     *
     * Validates the uploaded Excel file, retrieves the provider ID,
     * and delegates the import logic to ProductImportService.
     * Returns a JSON summary of imported products, price tiers, and taxes.
     *
     * @param Request $request The HTTP request containing the Excel file and
     * provider_id.
     * @return JsonResponse Summary of the import process.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
        $providerId = (int) $request->input('provider_id');
        $message = (new ExcelImporterService)->import(
            $request->file('file'), $providerId
        );

        return response()->json([
            'message' => $message,
        ]);
    }
}

