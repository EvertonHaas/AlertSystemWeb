@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Lista de Ocorrências</h1>

        <!-- Formulário para filtro -->
        <form method="GET" action="{{ route('occurrence.index') }}" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="product_id">Filtrar por Produto:</label>
                    <select name="product_id" id="product_id" class="form-control">
                        <option value="">Todos</option>
                        @foreach($products as $product)
                            <option value="{{ $product->productid }}" {{ request('product_id') == $product->productid ? 'selected' : '' }}>
                                {{ $product->description }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Filtro de status (resolvidas, não resolvidas, todas) -->
                <div class="col-md-4">
                    <label for="resolved_filter">Status:</label>
                    <select name="resolved_filter" id="resolved_filter" class="form-control">
                        <option value="0" {{ request('resolved_filter', '0') == '0' ? 'selected' : '' }}>Não resolvidas</option>
                        <option value="1" {{ request('resolved_filter') == '1' ? 'selected' : '' }}>Resolvidas</option>
                        <option value="" {{ request('resolved_filter') === '' ? 'selected' : '' }}>Todas</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </div>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Descrição</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Valor</th>
                    <th>Data</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($occurrences as $ocorrencia)
                <tr>
                    <td>{{ $ocorrencia->id }}</td>                    
                    <td>{{ $ocorrencia->productid }} - {{ $ocorrencia->product->description }} </td>
                    <td>{{ $ocorrencia->latitude }}</td>
                    <td>{{ $ocorrencia->longitude }}</td>
                    <td>{{ $ocorrencia->value }}</td>
                    <td>{{ \Carbon\Carbon::parse($ocorrencia->dateinsert)->format('d/m/y H:i') }}</td>
                    <td>
                        <form action="{{ route('occurrence.toggle', $ocorrencia->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            @if($ocorrencia->resolvida)
                                <button type="submit" class="btn btn-info btn-sm">Reativar</button>
                            @else
                                <button type="submit" class="btn btn-success btn-sm">Resolver</button>
                            @endif
                        </form>
                    </td>                    
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
