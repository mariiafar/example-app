@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">

    <h2 class="text-xl font-bold mb-4">
        Оплата услуги "{{ $application->service->name }}"
    </h2>

    <p class="text-gray-700">
        Клиент: <strong>{{ $application->client_name }}</strong>
    </p>

    <p class="text-gray-700">
        Время записи: <strong>{{ $application->date }} {{ $application->time }}</strong>
    </p>

    <p class="text-lg font-semibold mt-4">
        Сумма к оплате: <span class="text-blue-600">{{ $application->service->price / 2 }} ₽</span>  
        <span class="text-sm text-gray-500">(50% от стоимости)</span>
    </p>

    <div class="mt-6 space-y-3">
        <form method="POST" action="{{ route('payment.success', $application->id) }}">
            @csrf
            <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded">
                Имитация успешной оплаты
            </button>
        </form>

        <form method="POST" action="{{ route('payment.fail', $application->id) }}">
            @csrf
            <button class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded">
                Имитация неуспешной оплаты
            </button>
        </form>
    </div>
</div>
@endsection