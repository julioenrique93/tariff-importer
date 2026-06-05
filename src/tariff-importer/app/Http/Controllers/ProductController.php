<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ExcelImporterService;

class ProductController extends Controller
{
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

