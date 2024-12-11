<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Ocurrence;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class WaterManagerService
{
    private string $apiConsultaProductsUrl = 'http://177.44.248.13:8080/WaterManager/productID.jsp?FORMAT=JSON';
    private string $apiConsultaOcurrencesUrl = 'http://177.44.248.13:8080/WaterManager/?op=SELECT&FORMAT=JSON&DATEINI=';

    function buscaDataUltimaConsulta(): string
    {
        $existingSetting = Setting::find(1);

        if (!$existingSetting) {            
            $defaultDate = Carbon::createFromFormat('Y-m-d H:i:s', '2000-01-01 00:00:00');
            Setting::create([
                'id' => 1,
                'ultimaconsulta' => $defaultDate,
            ]);

            return $defaultDate->format('Y-m-d H:i:s');
        } else {
            return Carbon::parse($existingSetting->ultimaconsulta)->format('Y-m-d H:i:s');
        }
    }

    function verificaUltimaImportacao(): bool
    {
        $existingSetting = Setting::find(1);

        if ($existingSetting) {
            $ultimaConsulta = Carbon::parse($existingSetting->ultimaconsulta);
            $diferencaEmMinutos = $ultimaConsulta->diffInMinutes(Carbon::now());

            // verifica se faz mais de 5 minutos
            return $diferencaEmMinutos > 5;
        }

        // se não existir configuração, sempre permite importar
        return true;
    }

    public function importarProducts()
    {
        try {
            // Busca os dados da API
            $jsonData = file_get_contents($this->apiConsultaProductsUrl);

            $jsonData = trim($jsonData);
            $jsonData = preg_replace('/\x{FEFF}/u', '', $jsonData);
            $jsonData = preg_replace('/\\\\/', '\\\\\\\\', $jsonData);

            // Decodificar o JSON
            $products = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Erro ao decodificar JSON: ' . json_last_error_msg());
            }

            // Processa os produtos
            foreach ($products as $productData) {
                $this->insereAtualizaProduct($productData);
            }

        } catch (\Exception $e) {
            echo "Erro durante a importação de produtos: " . $e->getMessage() . "\n";
        }
    }

    public function importarOcurrences($forcaSincronizar = false)
    {
        // Validar se a última importação foi há mais de 5 minutos
        if (!$this->verificaUltimaImportacao() && !$forcaSincronizar) {
            Log::info('Ocorrências não atualizadas pois a última sincronização foi realizada há menos de 5 minutos.');
            return;
        }

        try {
            $ultimaConsultaData = $this->buscaDataUltimaConsulta();
            $urlData = $this->apiConsultaOcurrencesUrl . urlencode($ultimaConsultaData);

            $jsonData = file_get_contents($urlData);

            $jsonData = trim($jsonData);
            $jsonData = preg_replace('/\x{FEFF}/u', '', $jsonData);
            $jsonData = preg_replace('/\\\\/', '\\\\\\\\', $jsonData);

            // Decodificar o JSON
            $ocurrences = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Erro ao decodificar JSON: ' . json_last_error_msg());
            }

            // Processa as ocorrências
            $totalSincronizadas = 0;
            foreach ($ocurrences as $ocurrenceData) {
                $this->insereAtualizaOcurrence($ocurrenceData);
                $totalSincronizadas++;
            }

            // Atualizar a data da última consulta
            $this->atualizaUltimaConsulta();

            // Grava log com base no tipo de sincronização
            if ($forcaSincronizar) {
                Log::info("Ocorrências sincronizadas forçadamente. Total: {$totalSincronizadas}");
            } else {
                Log::info("Ocorrências sincronizadas. Total: {$totalSincronizadas}");
            }

        } catch (\Exception $e) {
            // Grava o erro no log
            Log::info('Erro ao atualizar as ocorrências: ' . $e->getMessage());
        }
    }

    private function insereAtualizaProduct(array $productData)
    {
        $existingProduct = Product::find($productData['id']);

        if ($existingProduct) {
            $existingProduct->update([
                'productid' => $productData['productid'],
                'description' => $productData['description'],
                'type' => $productData['type'],
                'example' => $productData['example'],
                'validateexpression' => $productData['validateexpression'],
            ]);
        } else {
            Product::create([
                'id' => $productData['id'],
                'productid' => $productData['productid'],
                'description' => $productData['description'],
                'type' => $productData['type'],
                'example' => $productData['example'],
                'validateexpression' => $productData['validateexpression'],
            ]);
        }
    }

    private function insereAtualizaOcurrence(array $ocurrenceData)
    {
        $productExists = Product::where('productid', $ocurrenceData['productid'])->exists();

        if (!$productExists) {
            // força a importação dos produtos            
            $this->importarProducts();

            $productExists = Product::where('productid', $ocurrenceData['productid'])->exists();

            if (!$productExists) {
                // ignora essa ocorrência se não existe o product                
                return;
            }
        }

        // procura por uma ocorrência existente
        $existingOcurrence = Ocurrence::find($ocurrenceData['id']);

        if ($existingOcurrence) {
            $existingOcurrence->update([
                'vendorid' => $ocurrenceData['vendorid'],
                'productid' => $ocurrenceData['productid'],
                'latitude' => $ocurrenceData['latitude'],
                'longitude' => $ocurrenceData['longitude'],
                'value' => $ocurrenceData['value'],
                'dateinsert' => $ocurrenceData['dateinsert'],
            ]);
        } else {
            Ocurrence::create([
                'id' => $ocurrenceData['id'],
                'vendorid' => $ocurrenceData['vendorid'],
                'productid' => $ocurrenceData['productid'],
                'latitude' => $ocurrenceData['latitude'],
                'longitude' => $ocurrenceData['longitude'],
                'value' => $ocurrenceData['value'],
                'dateinsert' => $ocurrenceData['dateinsert'],
            ]);
        }
    }

    private function atualizaUltimaConsulta()
    {
        $existingSetting = Setting::find(1);

        if ($existingSetting) {
            $existingSetting->update([
                'ultimaconsulta' => Carbon::now(),
            ]);
        } else {
            Setting::create([
                'id' => 1,
                'ultimaconsulta' => Carbon::now(),
            ]);
        }
    }
}
