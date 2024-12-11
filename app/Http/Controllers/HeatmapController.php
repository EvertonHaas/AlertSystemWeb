<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Ocurrence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\WaterManagerService;

class HeatmapController extends Controller
{
    public function getData(Request $request, $productid = null, $days = null, $status = null)
    {        
        $days = $days ?? $request->query('days');
        $productid = $productid ?? $request->query('productid');
        $status = $status ?? $request->query('status');

        // Query base
        $query = Ocurrence::select('latitude', 'longitude');

        // Filtrar por productId se for fornecido
        if ($productid) {
            $query->where('productid', $productid); // Ajuste o nome da coluna conforme necessário
        }

        // Filtrar por número de dias se for fornecido
        if ($days) {
            $query->where('dateinsert', '>=', now()->subDays($days));
        }

        
        if ($status) {
            switch ($status) {
                case 'pendentes':
                    $query->where('resolvida', false);
                    break;
                case 'resolvidas':
                    $query->where('resolvida', true);
                    break;
                case 'todas':
                    break;
                default:
                    break;
            }
        }
        //dd($query->toSql(), $query->getBindings());
        // Obter os resultados
        $data = $query->get();
        
        return response()->json($data);
    }

    public function index()
    {
        $importer = new WaterManagerService();
        $importer->importarOcurrences();

        Carbon::setLocale('pt_BR');

        // Dataset para gráfico de pizza (Total de ocorrências por categoria)
        $totalData = Ocurrence::join('products', 'ocurrences.productid', '=', 'products.productid')
            ->selectRaw('products.description as category, COUNT(ocurrences.id) as total')
            ->groupBy('products.description')
            ->orderBy('total', 'desc')
            ->get();

        $labelTotal = $totalData->pluck('category')->toArray();
        $dataTotal = $totalData->pluck('total')->toArray();

        // Último ano
        $yearData = Ocurrence::join('products', 'ocurrences.productid', '=', 'products.productid')
            ->selectRaw('products.description as category, COUNT(ocurrences.id) as total')
            ->where('ocurrences.dateinsert', '>=', Carbon::now()->subYear())
            ->groupBy('products.description')
            ->orderBy('total', 'desc')
            ->get();

        $labelYear = $yearData->pluck('category')->toArray();
        $dataYear = $yearData->pluck('total')->toArray();

        // Último mês
        $monthData = Ocurrence::join('products', 'ocurrences.productid', '=', 'products.productid')
            ->selectRaw('products.description as category, COUNT(ocurrences.id) as total')
            ->where('ocurrences.dateinsert', '>=', Carbon::now()->subMonth())
            ->groupBy('products.description')
            ->orderBy('total', 'desc')
            ->get();

        $labelMonth = $monthData->pluck('category')->toArray();
        $dataMonth = $monthData->pluck('total')->toArray();

        // Última semana
        $weekData = Ocurrence::join('products', 'ocurrences.productid', '=', 'products.productid')
            ->selectRaw('products.description as category, COUNT(ocurrences.id) as total')
            ->where('ocurrences.dateinsert', '>=', Carbon::now()->subWeek())
            ->groupBy('products.description')
            ->orderBy('total', 'desc')
            ->get();

        $labelWeek = $weekData->pluck('category')->toArray();
        $dataWeek = $weekData->pluck('total')->toArray();

        // Último dia
        $dayData = Ocurrence::join('products', 'ocurrences.productid', '=', 'products.productid')
            ->selectRaw('products.description as category, COUNT(ocurrences.id) as total')
            ->where('ocurrences.dateinsert', '>=', Carbon::now()->subDay())
            ->groupBy('products.description')
            ->orderBy('total', 'desc')
            ->get();

        $labelDay = $dayData->pluck('category')->toArray();
        $dataDay = $dayData->pluck('total')->toArray();

        $lastOccurrence = Ocurrence::latest('dateinsert')->first();

    if ($lastOccurrence) {
        // Obtenha as coordenadas da última ocorrência
        $latitude = $lastOccurrence->latitude; // Certifique-se de que há colunas latitude e longitude
        $longitude = $lastOccurrence->longitude;

        // Construa a frase
        $timeSinceLastOccurrence = sprintf(
            "A ocorrência %s ocorreu %s",
            $lastOccurrence->product->description ?? 'desconhecida', // Certifique-se de que há relação com a categoria
            Carbon::parse($lastOccurrence->dateinsert)->diffForHumans()
        );
    } else {
        $timeSinceLastOccurrence = 'Nenhuma ocorrência registrada';
    }

        $resolvedOccurrences = Ocurrence::where('resolvida', true)->count();
        $pendingOccurrences = Ocurrence::where('resolvida', false)->count();

        $products = Product::all();

        return view('heatmap', compact(
            'products',
            'timeSinceLastOccurrence',
            'labelTotal',
            'dataTotal',
            'labelYear',
            'dataYear',
            'labelMonth',
            'dataMonth',
            'labelWeek',
            'dataWeek',
            'labelDay',
            'dataDay',
            'resolvedOccurrences',
            'pendingOccurrences'
        ));
    }

}
