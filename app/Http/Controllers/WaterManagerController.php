<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Services\WaterManagerService;

class WaterManagerController extends Controller
{
    /**
     * Importa os produtos da API externa.
     *
     * @return JsonResponse
     */
    public function importProducts(): JsonResponse
    {
        try {
            $importer = new WaterManagerService();
            $importer->importarProducts();

            return response()->json(['message' => 'Importação concluída com sucesso.'], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function importOcurrences(): JsonResponse
    {
        try {
            $importer = new WaterManagerService();
            $importer->importarOcurrences();

            return response()->json(['message' => 'Importação concluída com sucesso.'], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
