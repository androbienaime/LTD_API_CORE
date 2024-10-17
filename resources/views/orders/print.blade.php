<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture - LesTruviens</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @tailwind base;
        @tailwind components;
        @tailwind utilities;

        /* Couleurs personnalisées basées sur le logo */
        .text-secondary { color: #F2B632; } /* Jaune du logo */
        .text-primary { color: #1E5A9D; } /* Bleu du logo */
        .bg-primary { background-color: #E5E5E5; }
    </style>
</head>
<body class="bg-primary p-10 font-[Arial]">
    <div class="max-w-4xl mx-auto bg-white shadow-md p-8">
        <!-- Header avec logo -->
        <header class="flex justify-between items-center mb-8">
            <div class="flex items-center">
                <div class="flex flex-col justify-between">
                    <!-- Logo -->
                    <img src="{{ asset('img/logo.png') }}" alt="LesTruviens Logo" class="h-32 mr-4">
                    <!-- Date alignée en bas du logo -->
                    <p class="text-sm">Date: <span class="font-medium">{{ $created_at }}</span></p>
                </div>
                <div class="text-right">
                    </div>
            </div>
            <div class="text-center">
                <p>76, Rue Poudrière, Trou-du-Nord</p>
                <p>Téléphones: (+509) 33 35 6231 / 48 33 4060</p>
                <p>E-mail: <a href="mailto:admin@lestruviens.com" class="text-primary">admin@lestruviens.com</a></p>                
            </div>
        </header>

        <!-- Infos Client -->
        <section class="mb-8">
            <p class="">Facture À : {{ $customer["fullname"] }}</p>
            
            @if(!is_null($customer['phone']))
                <p class="">Téléphone: +509 {{ $customer['phone'] }}</p>
            @endif

            <p>No de facture: <span class="font-medium">{{ $order["reference_order"]}}</span></p>

        </section>

       <!-- Table des articles -->
<table class="w-full mb-6 border-collapse border border-gray-300">
    <thead class="text-primary">
        <tr>
            <th class="border border-gray-300 px-4 py-2 w-1/6">Articles</th>
            <th class="border border-gray-300 px-4 py-2 w-1/4">Description</th>
            <th class="border border-gray-300 px-4 py-2 w-1/6">Images</th>
            <th class="border border-gray-300 px-4 py-2 w-1/10">P. Unit</th>
            <th class="border border-gray-300 px-4 py-2 w-1/16">Qté</th>
            <th class="border border-gray-300 px-4 py-2 w-1/10">P. Total</th>
        </tr>
    </thead>
    <tbody>
        @if(!is_null($orderProducts))
            @foreach($orderProducts as $orderProduct)
                <tr class="text-center">
                    <td class="border border-gray-300 px-4 py-2">{{ $orderProduct["products"]["name"] }}</td>
                    <td class="border border-gray-300 px-4 py-2">
                        <div class="break">{{ $orderProduct["products"]["description"] }}</div>

                        @if(!empty($orderProduct["declinations"]))
                            @foreach($orderProduct["declinations"] as $declinations)
                                @foreach($declinations as $key => $value)
                                    <div class="font-semibold text-left">{{ $key }} <span class="font-medium text-primary"> : {{ $value }}</span></div>
                                @endforeach
                            @endforeach
                        @endif
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <img src="{{ $orderProduct['products']['cover_image'] }}" class="w-32"/>
                    </td>
                    <td class="border border-gray-300 px-4 py-2">{{ $orderProduct['price_unit'] }} USD</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $orderProduct['quantity'] }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $orderProduct['sub_totals'] }}</td>
                </tr>
            @endforeach
        @endif

        @if($delivery["delivery_date"])
            <tr class="text-center">
                <td colspan="7" class="text-center">
                    <p class="text-secondary">
                        <span class="font-bold italic">Livraison prévue avant le {{ $delivery["delivery_date"] }}</span>
                    </p>
                </td>
            </tr>
        @endif
    </tbody>
</table>


        <!-- Détails de paiement -->
        <div class="mb-8">
        </div>

        <div class="flex justify-end mb-8">
            <div class="text-right">
                <p><span class="font-semibold">Total:</span> {{ $order["total_amount"] }} USD</p>
                @if($order["discount"] > 0)
                    <p><span class="font-semibold">Réduction:</span> {{ $order["discount"]}} USD</p>
                @endif
                <p><span class="font-semibold">Versement:</span> {{ $order["versement"]}} USD</p>
                <p class="font-semibold text-primary"><span>Balance:</span> {{ $order["balance"]}} USD</p>
            </div>
        </div>

        <!-- Note finale -->
        <div class="mt-8 text-sm pt-4">
            <p class="font-semibold text-center">N.B: Le paiement de la balance doit être versé avant la livraison.</p>
            <p class="mt-4 text-center font-semibold text-primary italic">Merci d'avoir choisi LesTruviens!!!</p>
        </div>
    </div>
</body>
</html>
