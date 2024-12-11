<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Ocurrence;
use Illuminate\Http\Request;
use App\Services\WaterManagerService;

class OccurrenceController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::all();
        $query = Ocurrence::with('product');

        // Filtro de produto
        if ($request->has('product_id') && $request->product_id != '') {
            $query->where('productid', $request->product_id);
        }

        // Filtro de resolvidas
        // Padrão: não resolvidas (resolved_filter = '0')
        // '1' = resolvidas, '' (vazio) = todas
        $resolvedFilter = $request->input('resolved_filter', '0');

        if ($resolvedFilter === '0') {
            // Não resolvidas
            $query->where('resolvida', false);
        } elseif ($resolvedFilter === '1') {
            // Resolvidas
            $query->where('resolvida', true);
        }
        // Caso seja '', não faz nenhum filtro, mostra todas

        $occurrences = $query->get();

        return view('occurrence.index', compact('occurrences', 'products'));
    }


    public function create()
    {
        $products = Product::all();
        return view('occurrence.create', compact('products'));
    }

    public function store(Request $request)
    {   
        $dados = $request->all();        

        //return response()->json($request->all());

        // Obtém o produto selecionado para determinar o tipo
        $product = Product::where('productid', $dados['productid'])->first();
        $value = null;        
        
        // Determina o valor baseado no tipo do produto
        switch (strtoupper($product->type)) {
            case 'INT':
                $value = $dados['value_int'];
                break;
            case 'FLOAT':
                $value = $dados['value_float'];
                break;
            case 'TEXT':
                $value = $dados['value_text'];
                break;
            case 'LOGIC':
                $value = $dados['value_logic'] === 'true' ? 1 : 0; // Convertendo para booleano numérico
                break;
            default:
                return redirect()->back()->withErrors(['productid' => 'Tipo de produto inválido.']);
        }        

        // Verifica se o valor foi preenchido
        if (is_null($value)) {
            return redirect()->back()->withErrors(['value' => 'O campo valor é obrigatório.']);
        }

        // Monta a URL com os dados ajustados
        $url = 'http://177.44.248.13:8080/WaterManager?op=INSERT' .
            '&VENDORID=649150' .
            '&PRODUCTID=' . $dados['productid'] .
            '&LATITUDE=' . $dados['latitude'] .
            '&LONGITUDE=' . $dados['longitude'] .
            '&VALUE=' . urlencode($value); // Codifica o valor para evitar problemas com caracteres especiais


        // Configura e executa o cURL
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

        // Importa ocorrências
        $importer = new WaterManagerService();
        $importer->importarOcurrences(true);

        // Redireciona com mensagem de sucesso
        return redirect()->route('index');
    }

    public function toggleResolution($id)
    {
        $occurrence = Ocurrence::findOrFail($id);
        // Inverte o valor de resolvida
        $occurrence->resolvida = !$occurrence->resolvida;
        $occurrence->save();

        $message = $occurrence->resolvida ? 'Ocorrência marcada como resolvida!' : 'Ocorrência reativada!';
        return redirect()->route('occurrence.index')->with('success', $message);
    }

}
