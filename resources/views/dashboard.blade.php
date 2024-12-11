@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center mb-4 display-4 text-primary">Dashboard</h1>
    <div class="row row-cols-1 row-cols-md-2 g-4">
        <!-- Total de Ocorrências -->
        <div class="col">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Total de Ocorrências</h5>
                    <p class="display-4 fw-bold">{{ $totalOccurrences }}</p>
                    <i class="bi bi-list-check display-1"></i>
                </div>
            </div>
        </div>

        <!-- Ocorrências nos últimos 7 dias -->
        <div class="col">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Ocorrências nos Últimos 7 Dias</h5>
                    <p class="display-4 fw-bold">{{ $occurrencesLast7Days }}</p>
                    <i class="bi bi-calendar-check display-1"></i>
                </div>
            </div>
        </div>

        <!-- Tempo desde a última ocorrência -->
        <div class="col">
            <div class="card text-white bg-warning shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Tempo desde a Última Ocorrência</h5>
                    <p class="fs-4 fw-bold">{{ $timeSinceLastOccurrence }}</p>
                    <i class="bi bi-clock-history display-1"></i>
                </div>
            </div>
        </div>

        <!-- Categoria mais frequente -->
        <div class="col">
            <div class="card text-white bg-danger shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Ocorrência Mais Frequente</h5>
                    <p class="fs-4 fw-bold">{{ $mostFrequentCategoryDescription }}</p>
                    <i class="bi bi-bar-chart-line display-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
