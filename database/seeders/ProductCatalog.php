<?php

namespace Database\Seeders;

/** Illustrative configurations and INR prices, not verified retailer inventory. */
class ProductCatalog
{
    public static function rows(): array
    {
        $catalog = [
            'laptops' => [
                ['Lenovo', 'ThinkPad T14 Gen 5', 84000, 'AMD Ryzen 7 PRO 8840U', 14, 1.38],
                ['Lenovo', 'ThinkBook 14 Gen 7', 59000, 'AMD Ryzen 5 7535HS', 14, 1.4],
                ['Dell', 'Latitude 5450', 79000, 'Intel Core Ultra 5 125U', 14, 1.4],
                ['Dell', 'XPS 13 9340', 139000, 'Intel Core Ultra 7 155H', 13.4, 1.19],
                ['HP', 'EliteBook 840 G11', 89000, 'Intel Core Ultra 5 125U', 14, 1.41],
                ['HP', 'Pavilion Plus 14', 68000, 'AMD Ryzen 7 7840U', 14, 1.44],
                ['ASUS', 'Zenbook 14 OLED UX3405', 81000, 'Intel Core Ultra 5 125H', 14, 1.2],
                ['Acer', 'Swift Go 14', 61000, 'Intel Core Ultra 5 125H', 14, 1.3],
                ['Apple', 'MacBook Air 13 M4', 99000, 'Apple M4', 13.6, 1.24],
                ['Framework', 'Laptop 13 AMD', 109000, 'AMD Ryzen 7 7840U', 13.5, 1.3],
                ['Lenovo', 'ThinkPad E14 Gen 6', 64000, 'AMD Ryzen 7 7735HS', 14, 1.42],
                ['Lenovo', 'ThinkPad L14 Gen 5', 72000, 'AMD Ryzen 7 PRO 7735U', 14, 1.4],
                ['Lenovo', 'ThinkPad T14 Gen 4', 74000, 'AMD Ryzen 7 PRO 7840U', 14, 1.37, [16, 32]],
                ['Lenovo', 'ThinkPad T14s Gen 4', 79000, 'AMD Ryzen 7 PRO 7840U', 14, 1.25, [16, 32]],
                ['Lenovo', 'ThinkPad X13 Gen 4', 77000, 'Intel Core i7-1355U', 13.3, 1.19, [16, 32]],
                ['Lenovo', 'Yoga Slim 7 14APU8', 75000, 'AMD Ryzen 7 7840S', 14, 1.35, [16, 32]],
                ['Acer', 'TravelMate P4 14', 65000, 'Intel Core i5-1335U', 14, 1.39],
                ['ASUS', 'ExpertBook B5 B5404', 72000, 'Intel Core i7-1355U', 14, 1.37],
            ],
            'monitors' => [
                ['Dell', 'UltraSharp U2724D', 35000, 27], ['Dell', 'P2425H', 17000, 24],
                ['LG', 'UltraFine 32UN650', 31000, 32], ['LG', 'DualUp 28MQ780', 59000, 28],
                ['BenQ', 'PD2705Q', 32000, 27], ['BenQ', 'GW2790', 13000, 27],
                ['Samsung', 'ViewFinity S6', 26000, 32], ['Samsung', 'Odyssey G5', 29000, 34],
                ['ASUS', 'ProArt PA278QV', 28000, 27], ['Acer', 'CB272', 14500, 27],
                ['Dell', 'S2722QC', 28500, 27], ['LG', 'UltraWide 34WQ75C', 42000, 34],
            ],
            'keyboards' => [
                ['Keychron', 'K2 Pro', 9500, true], ['Keychron', 'V1', 6500, false],
                ['Logitech', 'MX Keys S', 10500, true], ['Logitech', 'Signature K650', 4200, true],
                ['Razer', 'BlackWidow V4', 14500, false], ['Razer', 'Pro Type Ultra', 13900, true],
                ['Corsair', 'K65 Plus', 12500, true], ['Royal Kludge', 'RK84', 5800, true],
                ['Microsoft', 'Designer Compact', 5500, true], ['Dell', 'KB216', 1200, false],
                ['Keychron', 'K8 Pro', 8900, true],
            ],
            'mice' => [
                ['Logitech', 'MX Master 3S', 8500, true], ['Logitech', 'Lift', 5700, true],
                ['Logitech', 'M720 Triathlon', 3800, true], ['Razer', 'Pro Click', 7900, true],
                ['Razer', 'DeathAdder V3', 4200, false], ['Microsoft', 'Modern Mobile Mouse', 2200, true],
                ['Dell', 'MS3320W', 1500, true], ['HP', '930 Creator', 4600, true],
                ['Corsair', 'Harpoon RGB', 2400, false], ['Lenovo', 'Go Wireless Vertical', 3900, true],
                ['Logitech', 'MX Anywhere 3S', 6900, true],
            ],
            'headphones' => [
                ['Sony', 'WH-1000XM5', 26900, true], ['Sony', 'WH-CH720N', 8900, true],
                ['Bose', 'QuietComfort', 24500, true], ['Sennheiser', 'HD 560S', 15900, false],
                ['Sennheiser', 'Accentum Plus', 13900, true], ['Audio-Technica', 'ATH-M50x', 12900, false],
                ['JBL', 'Tune 770NC', 6200, true], ['Logitech', 'Zone Vibe 100', 9900, true],
                ['Jabra', 'Evolve2 65', 18900, true], ['HyperX', 'Cloud III', 7900, false],
            ],
            'webcams' => [
                ['Logitech', 'C920 HD Pro', 6500, false], ['Logitech', 'Brio 4K', 15500, false],
                ['Logitech', 'StreamCam', 11500, false], ['Razer', 'Kiyo Pro', 12900, false],
                ['Elgato', 'Facecam', 14900, false], ['Dell', 'UltraSharp WB7022', 18900, false],
                ['Anker', 'PowerConf C200', 7900, false], ['Insta360', 'Link', 24900, false],
                ['HP', '960 4K Streaming', 13900, false], ['Microsoft', 'Modern Webcam', 5900, false],
            ],
            'docking stations' => [
                ['CalDigit', 'TS4 Thunderbolt 4', 38900, false], ['CalDigit', 'SOHO Dock', 11900, false],
                ['Anker', '575 USB-C Dock', 18900, false], ['Anker', '555 USB-C Hub', 7900, false],
                ['Dell', 'WD22TB4', 26900, false], ['Lenovo', 'ThinkPad Universal USB-C Dock', 19900, false],
                ['HP', 'USB-C Dock G5', 15900, false], ['Belkin', 'Connect Pro Thunderbolt 4', 32900, false],
                ['UGREEN', 'Revodok Pro 209', 8900, false], ['OWC', 'Thunderbolt Go Dock', 34900, false],
            ],
        ];
        $rows = [];
        foreach ($catalog as $category => $models) {
            foreach ($models as $i => $model) {
                $ramOptions = $category === 'laptops' ? ($model[6] ?? ($model[0] === 'Apple' ? [16, 24, 32] : [16, 32, 64])) : [];
                $memoryConfigurations = count($ramOptions);
                for ($v = 0; $v < ($category === 'laptops' ? $memoryConfigurations * 2 : 3); $v++) {
                    [$brand, $name, $price] = $model;
                    $ram = $category === 'laptops' ? $ramOptions[$v % $memoryConfigurations] : null;
                    $storage = $category === 'laptops' ? ($v < $memoryConfigurations ? 512 : 1024) : null;
                    $suffix = $category === 'laptops' ? "{$ram}GB / {$storage}GB" : ['Standard', 'Desk Kit', 'Workspace Kit'][$v];
                    $description = match ($category) {
                        'laptops' => "Portable developer laptop with {$model[3]}, {$ram}GB RAM and {$storage}GB SSD. Suitable for coding, Laravel development and local containers. Wi-Fi connectivity.",
                        'monitors' => "{$model[3]}-inch external display for coding, documentation and side-by-side terminal windows. Adjustable desktop stand.",
                        'keyboards' => 'Comfortable keyboard for long coding sessions with a dedicated navigation layout.',
                        'mice' => 'Precision pointing device for daily development work and comfortable desktop navigation.',
                        'headphones' => 'Over-ear headphones for focused coding, listening and remote work.',
                        'webcams' => 'USB webcam for video calls, pair programming and screen-sharing sessions.',
                        'docking stations' => 'Wired desktop dock for connecting external displays, USB peripherals and a developer laptop.',
                    };
                    if ($category !== 'laptops') {
                        $description .= [' Standard retail configuration.', ' Includes an illustrative desk accessory bundle.', ' Includes an illustrative cable and workspace accessory bundle.'][$v];
                    }
                    $rows[] = [
                        'name' => "{$brand} {$name} — {$suffix}", 'brand' => $brand, 'category' => $category,
                        'description' => $description, 'price' => $price + ($category === 'laptops' ? ($v % $memoryConfigurations) * 9000 + ($v >= $memoryConfigurations ? 7000 : 0) : $v * 900),
                        'ram_gb' => $ram, 'storage_gb' => $storage, 'processor' => $category === 'laptops' ? $model[3] : null,
                        'screen_size' => $category === 'laptops' ? $model[4] : ($category === 'monitors' ? $model[3] : null),
                        'weight_kg' => $category === 'laptops' ? $model[5] : match ($category) {
                            'monitors' => 4.5 + $i * 0.2, 'keyboards' => 0.65 + $i * 0.03, 'mice' => 0.09 + $i * 0.005, 'headphones' => 0.25 + $i * 0.005, 'webcams' => 0.12 + $i * 0.01, default => 0.35 + $i * 0.04
                        },
                        'rating' => round(3.6 + (($i * 3 + $v) % 14) / 10, 1), 'in_stock' => ($i + $v) % 5 !== 0,
                        'wireless' => in_array($category, ['keyboards', 'mice', 'headphones']) ? $model[3] : null,
                        'created_at' => now()->startOfDay()->subDays(count($rows) % 180), 'updated_at' => now()->startOfDay(),
                    ];
                }
            }
        }

        return $rows;
    }
}
