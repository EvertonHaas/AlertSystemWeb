<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Product;
use App\Models\Ocurrence;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\WaterManagerService;

class ApiController extends Controller
{
    // Registro de usuário
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'token' => Str::random(60), // Gera um token
            ]);

            return response()->json([
                'message' => 'User registered successfully',
                'token' => $user->token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed'], 500);
        }
    }

    // Login do usuário
    public function login(Request $request)
    {
        
        $validated = $request->all();
        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['senha'], $user->password) && $validated['email'] == $user->email) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Atualiza o token para o login atual
        $user->token = Str::random(60);
        $user->save();

        return response()->json([
            'message' => 'Login successful',
            'token' => $user->token,
        ]);
    }

    public function insert(Request $request)
    {
        $request->validate([
            'productid' => 'required|string', 
            'value' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $dados = $request->all();

        $product = Product::where('productid', $dados['productid'])->first();

        if (!$product) {
            return response()->json(['error' => 'Categoria não encontrada.'], 404);
        }

        $value = null;

        switch (strtoupper($product->type)) {
            case 'INT':
                if (!is_numeric($dados['value']) || intval($dados['value']) != $dados['value']) {
                    return response()->json(['error' => 'O valor deve ser um inteiro.'], 400);
                }
                $value = intval($dados['value']);
                break;

            case 'FLOAT':
                if (!is_numeric($dados['value'])) {
                    return response()->json(['error' => 'O valor deve ser um número decimal.'], 400);
                }
                $value = floatval($dados['value']);
                break;

            case 'TEXT':
                $value = $dados['value'];
                break;

            case 'LOGIC':
                if (!in_array(strtolower($dados['value']), ['true', 'false', '1', '0'], true)) {
                    return response()->json(['error' => 'O valor deve ser um booleano (true/false ou 0/1).'], 400);
                }
                $value = filter_var($dados['value'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                break;

            default:
                return response()->json(['error' => 'Tipo de produto inválido.'], 400);
        }

        $url = 'http://177.44.248.13:8080/WaterManager?op=INSERT' .
            '&VENDORID=649150' .
            '&PRODUCTID=' . $dados['productid'] .
            '&LATITUDE=' . $dados['latitude'] .
            '&LONGITUDE=' . $dados['longitude'] .
            '&VALUE=' . urlencode($value);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        // Se necessário, chame a importação de ocorrências
        $importer = new WaterManagerService();
        $importer->importarOcurrences(true);

        // Retorna uma resposta indicando sucesso
        return response()->json(['success' => 'Dados inseridos com sucesso.', 'response' => $response], 201);
    }

    public function getProducts()
    {
        $products = Product::select('productid', 'description', 'type')->get();
        return response()->json($products, 200);
    }

    public function getStats()
    {
        // Contagem total de ocorrências
        $total = Ocurrence::count();

        // Contagem no último mês
        $lastMonth = Ocurrence::where('dateinsert', '>=', Carbon::now()->subMonth())->count();

        // Contagem na última semana
        $lastWeek = Ocurrence::where('dateinsert', '>=', Carbon::now()->subWeek())->count();

        // Contagem no último dia
        $lastDay = Ocurrence::where('dateinsert', '>=', Carbon::now()->subDay())->count();

        $resolvedOccurrences = Ocurrence::where('resolvida', true)->count();
        $pendingOccurrences = Ocurrence::where('resolvida', false)->count();

        // Última ocorrência com eager loading do produto
        $lastOccurrence = Ocurrence::with('product')->latest('dateinsert')->first();

        if ($lastOccurrence) {
            $timeSinceLastOccurrence = sprintf(
                "A ocorrência %s ocorreu %s",
                $lastOccurrence->product->description ?? 'desconhecida',
                Carbon::parse($lastOccurrence->dateinsert)->diffForHumans()
            );
        } else {
            $timeSinceLastOccurrence = 'Nenhuma ocorrência encontrada.';
        }

        $response = [
            'total' => $total,
            'last_month' => $lastMonth,
            'last_week' => $lastWeek,
            'last_day' => $lastDay,
            'last_occurrence' => $timeSinceLastOccurrence,
            'resolved_occurrences' => $resolvedOccurrences,
            'pending_occurrences' => $pendingOccurrences,
        ];

        return response()->json($response);
    }
}
