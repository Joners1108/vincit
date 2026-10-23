<?php
/**
 * Produtos de demonstração — tema urbano / streetwear (fictícios).
 * Imagens via Unsplash (apenas demonstração local).
 */

/**
 * Descarrega uma imagem para assets/img/products/ se ainda não existir.
 */
function demoDownloadProductImage(string $uploadDir, string $filename, string $url): bool {
    $imgPath = $uploadDir . $filename;
    if (is_file($imgPath) && filesize($imgPath) > 500) {
        return true;
    }
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 25,
            'header' => "User-Agent: VincitDemoSeed/1.0 (demo local)\r\n",
        ],
    ]);
    $data = @file_get_contents($url, false, $ctx);
    if ($data !== false && strlen($data) > 500) {
        return file_put_contents($imgPath, $data) !== false;
    }
    return false;
}

function getDemoProductRows() {
    $u = 'https://images.unsplash.com';
    $q = '?w=720&h=900&fit=crop&auto=format&q=80';

    return [
        [
            'category_slug' => 'camisolas',
            'name' => 'Hoodie "Rua à Noite"',
            'slug' => 'hoodie-rua-a-noite',
            'short_description' => 'Capuz largo, interior felpudo. Para dias de estúdio ou saídas à cidade.',
            'description' => 'Hoodie unissex em mistura de algodão pesado. Corte relaxado inspirado no streetwear lisboeta. Peça fictícia de demonstração da loja.',
            'price' => 89.90,
            'sale_price' => null,
            'image' => 'demo-vnc-hood-rua.jpg',
            'image_source_url' => $u . '/photo-1556821840-3a63f95609a7' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-hood-rua-b.jpg', 'url' => $u . '/photo-1620799140408-b2b002a01e65' . $q],
                ['file' => 'demo-vnc-hood-rua-c.jpg', 'url' => $u . '/photo-1578587018452-892bacefd3f1' . $q],
            ],
            'stock' => 40,
            'brand' => 'Vincit',
            'size' => 'XS, S, M, L, XL, XXL',
            'color' => 'Preto',
            'material' => '80% algodão, 20% poliéster',
            'gender' => 'unissex',
            'featured' => 1,
            'meta_title' => 'Hoodie Rua à Noite | Vincit',
            'meta_description' => 'Hoodie streetwear unissex — demonstração.',
        ],
        [
            'category_slug' => 'camisolas',
            'name' => 'T-shirt "Versos na Veia"',
            'slug' => 't-shirt-versos-na-veia',
            'short_description' => 'Algodão orgânico, estética minimal. Base perfeita para qualquer outfit.',
            'description' => 'T-shirt de manga curta com grafismo inspirado na cena rap nacional. Leve e versátil. Produto fictício para preencher a loja.',
            'price' => 39.90,
            'sale_price' => 29.90,
            'image' => 'demo-vnc-ts-vers.jpg',
            'image_source_url' => $u . '/photo-1521572163474-6864f9cf17ab' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-ts-vers-b.jpg', 'url' => $u . '/photo-1583743814966-8936f5b7be1a' . $q],
                ['file' => 'demo-vnc-ts-vers-c.jpg', 'url' => $u . '/photo-1576566588028-4147f3842f27' . $q],
            ],
            'stock' => 80,
            'brand' => 'Vincit',
            'size' => 'XXS, XS, S, M, L, XL, XXL',
            'color' => 'Branco sujo',
            'material' => '100% algodão',
            'gender' => 'unissex',
            'featured' => 1,
            'meta_title' => 'T-shirt Versos na Veia | Vincit',
            'meta_description' => 'T-shirt rap tuga — demonstração.',
        ],
        [
            'category_slug' => 'cal-as',
            'name' => 'Calças cargo "Quebrada"',
            'slug' => 'calcas-cargo-quebrada',
            'short_description' => 'Bolsos laterais, corte recto. Resistentes ao dia a dia.',
            'description' => 'Cargo em sarja com inspiração no hip-hop dos anos 90 e no que se ouve hoje nas bairros. Fictício — para mostrar a loja.',
            'price' => 79.90,
            'sale_price' => null,
            'image' => 'demo-vnc-cargo-qbr.jpg',
            'image_source_url' => $u . '/photo-1542272604-787c3835535d' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-cargo-qbr-b.jpg', 'url' => $u . '/photo-1473966968600-fa80187a25f9' . $q],
                ['file' => 'demo-vnc-cargo-qbr-c.jpg', 'url' => $u . '/photo-1624378515193-6bbd1d3e4b0f' . $q],
            ],
            'stock' => 35,
            'brand' => 'Vincit',
            'size' => '28, 30, 32, 34, 36, 38, 40',
            'color' => 'Verde oliva',
            'material' => '97% algodão, 3% elastano',
            'gender' => 'homem',
            'featured' => 1,
            'meta_title' => 'Cargo Quebrada | Vincit',
            'meta_description' => 'Calças cargo street — demonstração.',
        ],
        [
            'category_slug' => 'camisolas',
            'name' => 'Sweatshirt "Slow Jam Sessions"',
            'slug' => 'sweatshirt-slow-jam-sessions',
            'short_description' => 'Tom pastel, vibe after-hours. Para quem curte o lado soul e R&B.',
            'description' => 'Sweatshirt com bordado discreto. Referência às sessões de slow jams e às playlists de madrugada. Peça de demonstração.',
            'price' => 64.90,
            'sale_price' => null,
            'image' => 'demo-vnc-sw-slow.jpg',
            'image_source_url' => $u . '/photo-1576566588028-4147f3842f27' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-sw-slow-b.jpg', 'url' => $u . '/photo-1556905055-8f358a7a47b2' . $q],
            ],
            'stock' => 28,
            'brand' => 'Vincit',
            'size' => 'XS, S, M, L, XL, XXL',
            'color' => 'Lilás acinzentado',
            'material' => 'Algodão french terry',
            'gender' => 'unissex',
            'featured' => 1,
            'meta_title' => 'Sweatshirt Slow Jam | Vincit',
            'meta_description' => 'Sweatshirt slow jam — demonstração.',
        ],
        [
            'category_slug' => 'acessorios',
            'name' => 'Boné "Batida Lusitana"',
            'slug' => 'bone-batida-lusitana',
            'short_description' => 'Aba curva, fecho clássico. Logo bordado.',
            'description' => 'Boné de 6 painéis em sarja. Homenagem às batidas e à língua que rimamos. Produto fictício.',
            'price' => 28.90,
            'sale_price' => null,
            'image' => 'demo-vnc-bon-bat.jpg',
            'image_source_url' => $u . '/photo-1588850561407-ed78c282e89b' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-bon-bat-b.jpg', 'url' => $u . '/photo-1509631179647-0177331693ae' . $q],
            ],
            'stock' => 60,
            'brand' => 'Vincit',
            'size' => 'Único (ajustável 54–62 cm)',
            'color' => 'Preto / branco',
            'material' => 'Sarja de algodão',
            'gender' => 'unissex',
            'featured' => 1,
            'meta_title' => 'Boné Batida Lusitana | Vincit',
            'meta_description' => 'Boné street — demonstração.',
        ],
        [
            'category_slug' => 'mulher',
            'name' => 'Top crop "Lisboa Groove"',
            'slug' => 'top-crop-lisboa-groove',
            'short_description' => 'Corte cropped, tecido macio. Dança ou concerto.',
            'description' => 'Top para combinar com calças largas ou saia. Estética club e batidas electrónicas. Fictício.',
            'price' => 32.90,
            'sale_price' => null,
            'image' => 'demo-vnc-top-lis.jpg',
            'image_source_url' => $u . '/photo-1469334031218-e382a71b716b' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-top-lis-b.jpg', 'url' => $u . '/photo-1515886657613-9f3515b0c78f' . $q],
                ['file' => 'demo-vnc-top-lis-c.jpg', 'url' => $u . '/photo-1496747611176-843222e1e57c' . $q],
            ],
            'stock' => 45,
            'brand' => 'Vincit',
            'size' => 'XXS, XS, S, M, L, XL',
            'color' => 'Bordô',
            'material' => 'Viscose e elastano',
            'gender' => 'mulher',
            'featured' => 0,
            'meta_title' => 'Top Lisboa Groove | Vincit',
            'meta_description' => 'Top crop — demonstração.',
        ],
        [
            'category_slug' => 'cal-as',
            'name' => 'Joggers "Estúdio 808"',
            'slug' => 'joggers-estudio-808',
            'short_description' => 'Cós elástico, punhos apertados. Para gravar ou relaxar.',
            'description' => 'Calças de fato de treino com nome a brincar com o som da caixa. Demonstração — não é merch oficial de nenhum artista.',
            'price' => 59.90,
            'sale_price' => null,
            'image' => 'demo-vnc-jog-808.jpg',
            'image_source_url' => $u . '/photo-1506629082955-511b1aa562c8' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-jog-808-b.jpg', 'url' => $u . '/photo-1517438476312-10d79c077fd2' . $q],
            ],
            'stock' => 50,
            'brand' => 'Vincit',
            'size' => 'XS, S, M, L, XL, XXL',
            'color' => 'Cinzento mescla',
            'material' => 'Poliéster e algodão',
            'gender' => 'homem',
            'featured' => 0,
            'meta_title' => 'Joggers Estúdio 808 | Vincit',
            'meta_description' => 'Joggers street — demonstração.',
        ],
        [
            'category_slug' => 'sapatos',
            'name' => 'Sneakers "Break"',
            'slug' => 'sneakers-break',
            'short_description' => 'Sola espessa, silhueta chunky. Urbano e confortável.',
            'description' => 'Sneakers de estilo lifestyle para combinar com denim ou cargo. Inspirados na cultura break e dança urbana. Fictício.',
            'price' => 119.90,
            'sale_price' => null,
            'image' => 'demo-vnc-sn-brk.jpg',
            'image_source_url' => $u . '/photo-1542291026-7eec264c27ff' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-sn-brk-b.jpg', 'url' => $u . '/photo-1595950653106-6c7ebd46d9ae' . $q],
                ['file' => 'demo-vnc-sn-brk-c.jpg', 'url' => $u . '/photo-1549298916-b41d501d3772' . $q],
                ['file' => 'demo-vnc-sn-brk-d.jpg', 'url' => $u . '/photo-1460353581641-37baddab0fa2' . $q],
            ],
            'stock' => 22,
            'brand' => 'Vincit',
            'size' => '36, 37, 38, 39, 40, 41, 42, 43, 44, 45',
            'color' => 'Preto / sola creme',
            'material' => 'Couro sintético, sola em borracha',
            'gender' => 'unissex',
            'featured' => 1,
            'meta_title' => 'Sneakers Break | Vincit',
            'meta_description' => 'Sneakers urbanos — demonstração.',
        ],
        [
            'category_slug' => 'homem',
            'name' => 'Casaco "Prime"',
            'slug' => 'casaco-prime',
            'short_description' => 'Acolchoado leve, gola alta. Entre estações.',
            'description' => 'Casaco curto com fecho e bolsos laterais. Linha mais premium da coleção fictícia.',
            'price' => 149.90,
            'sale_price' => null,
            'image' => 'demo-vnc-jkt-prm.jpg',
            'image_source_url' => $u . '/photo-1594938298603-c8148c1dae0d' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-jkt-prm-b.jpg', 'url' => $u . '/photo-1591047139829-d851c5c3aaea' . $q],
                ['file' => 'demo-vnc-jkt-prm-c.jpg', 'url' => $u . '/photo-1544022613-e87ca75a784a' . $q],
            ],
            'stock' => 18,
            'brand' => 'Vincit',
            'size' => 'S, M, L, XL, XXL',
            'color' => 'Azul noite',
            'material' => 'Nylon e enchimento sintético',
            'gender' => 'homem',
            'featured' => 0,
            'meta_title' => 'Casaco Prime | Vincit',
            'meta_description' => 'Casaco homem — demonstração.',
        ],
        [
            'category_slug' => 'mulher',
            'name' => 'Body "Microfone"',
            'slug' => 'body-microfone',
            'short_description' => 'Segunda pele, decote subido. Palco ou street.',
            'description' => 'Body de manga longa com detalhe gráfico minimal. Produto de demonstração com vibe performance.',
            'price' => 42.90,
            'sale_price' => null,
            'image' => 'demo-vnc-bdy-mic.jpg',
            'image_source_url' => $u . '/photo-1552374196-1ab2a2c0e0d0' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-bdy-mic-b.jpg', 'url' => $u . '/photo-1539008835657-9e8f3779036c' . $q],
            ],
            'stock' => 30,
            'brand' => 'Vincit',
            'size' => 'XXS, XS, S, M, L, XL',
            'color' => 'Preto',
            'material' => 'Malha de algodão',
            'gender' => 'mulher',
            'featured' => 0,
            'meta_title' => 'Body Microfone | Vincit',
            'meta_description' => 'Body mulher — demonstração.',
        ],
        [
            'category_slug' => 'acessorios',
            'name' => 'Mochila "Cypher"',
            'slug' => 'mochila-cypher',
            'short_description' => 'Compartimento para portátil, bolsos frontais.',
            'description' => 'Mochila urbana para o dia a dia — escola, estúdio ou festival. Nome a referir a rodas de improviso. Fictício.',
            'price' => 45.90,
            'sale_price' => null,
            'image' => 'demo-vnc-moc-cyp.jpg',
            'image_source_url' => $u . '/photo-1553062407-98eeb64c6a62' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-moc-cyp-b.jpg', 'url' => $u . '/photo-1622560480605-d83c853bc5c3' . $q],
                ['file' => 'demo-vnc-moc-cyp-c.jpg', 'url' => $u . '/photo-1581605400709-a6d685dbc123' . $q],
            ],
            'stock' => 25,
            'brand' => 'Vincit',
            'size' => 'Único (capacidade ~22 L)',
            'color' => 'Cáqui',
            'material' => 'Poliéster reciclado',
            'gender' => 'unissex',
            'featured' => 0,
            'meta_title' => 'Mochila Cypher | Vincit',
            'meta_description' => 'Mochila street — demonstração.',
        ],
        [
            'category_slug' => 'acessorios',
            'name' => 'Meias "Kick & Snare"',
            'slug' => 'meias-kick-snare',
            'short_description' => 'Pack de dois pares. Cano médio.',
            'description' => 'Meias com padrão subtil a lembrar o essencial da bateria. Pack fictício para completar o look.',
            'price' => 12.90,
            'sale_price' => null,
            'image' => 'demo-vnc-mei-ks.jpg',
            'image_source_url' => $u . '/photo-1586350977771-b3b0abd50c82' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-mei-ks-b.jpg', 'url' => $u . '/photo-1585292776963-39f4c00a7c73' . $q],
            ],
            'stock' => 100,
            'brand' => 'Vincit',
            'size' => '35–38, 39–42, 43–46',
            'color' => 'Preto / cinzento',
            'material' => 'Algodão e poliamida',
            'gender' => 'unissex',
            'featured' => 0,
            'meta_title' => 'Meias Kick & Snare | Vincit',
            'meta_description' => 'Meias pack — demonstração.',
        ],
        [
            'category_slug' => 'homem',
            'name' => 'Jaqueta de ganga "Blue Note"',
            'slug' => 'jaqueta-ganga-blue-note',
            'short_description' => 'Denim médio, cortes clássicos. Sobreposição com hoodies.',
            'description' => 'Jaqueta de ganga com bolsos de peito e punhos com botão. Visual inspirado no jazz e na cidade à noite — produto fictício.',
            'price' => 98.00,
            'sale_price' => 84.90,
            'image' => 'demo-vnc-dnm-bln.jpg',
            'image_source_url' => $u . '/photo-1576995853123-5a10305d93c0' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-dnm-bln-b.jpg', 'url' => $u . '/photo-1541099649105-f69ad21f3246' . $q],
                ['file' => 'demo-vnc-dnm-bln-c.jpg', 'url' => $u . '/photo-1601925260368-ae2f83cf8b7f' . $q],
            ],
            'stock' => 26,
            'brand' => 'Vincit Lab',
            'size' => 'XS, S, M, L, XL, XXL',
            'color' => 'Azul médio lavado',
            'material' => '100% algodão denim',
            'gender' => 'homem',
            'featured' => 1,
            'meta_title' => 'Jaqueta ganga Blue Note | Vincit',
            'meta_description' => 'Jaqueta denim — demonstração.',
        ],
        [
            'category_slug' => 'camisolas',
            'name' => 'Polo piqué "Sample Clear"',
            'slug' => 'polo-pique-sample-clear',
            'short_description' => 'Gola com botões, corte regular. Do escritório ao after.',
            'description' => 'Polo em piqué respirável. Nome ironiza samples limpos antes do master — peça de demonstração.',
            'price' => 48.90,
            'sale_price' => null,
            'image' => 'demo-vnc-pl-smp.jpg',
            'image_source_url' => $u . '/photo-1586790170083-2f9ceadc732d' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-pl-smp-b.jpg', 'url' => $u . '/photo-1625910513413-e577a9a1444c' . $q],
            ],
            'stock' => 55,
            'brand' => 'Vincit',
            'size' => 'S, M, L, XL, XXL',
            'color' => 'Verde musgo',
            'material' => 'Algodão piqué',
            'gender' => 'homem',
            'featured' => 0,
            'meta_title' => 'Polo Sample Clear | Vincit',
            'meta_description' => 'Polo piqué — demonstração.',
        ],
        [
            'category_slug' => 'homem',
            'name' => 'Shorts mesh "Arena"',
            'slug' => 'shorts-mesh-arena',
            'short_description' => 'Tecido respirável, cós com cordão. Treino ou verão urbano.',
            'description' => 'Shorts com forro interior e painéis em mesh. Coleção fictícia inspirada em palcos ao ar livre.',
            'price' => 44.90,
            'sale_price' => null,
            'image' => 'demo-vnc-sh-arn.jpg',
            'image_source_url' => $u . '/photo-1591195853828-11db59a44f6b' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-sh-arn-b.jpg', 'url' => $u . '/photo-1617137968427-85924c2a5634' . $q],
            ],
            'stock' => 38,
            'brand' => 'Vincit',
            'size' => 'S, M, L, XL, XXL',
            'color' => 'Preto / cinza',
            'material' => 'Poliéster mesh',
            'gender' => 'homem',
            'featured' => 0,
            'meta_title' => 'Shorts Arena | Vincit',
            'meta_description' => 'Shorts desporto — demonstração.',
        ],
        [
            'category_slug' => 'sapatos',
            'name' => 'Ténis court "Fresh Press"',
            'slug' => 'tenis-court-fresh-press',
            'short_description' => 'Silhueta baixa, sola em borracha. Branco puro para qualquer fit.',
            'description' => 'Sneakers estilo court branco — par versátil para streetwear ou look mais limpo. Produto fictício de demonstração.',
            'price' => 89.90,
            'sale_price' => 74.90,
            'image' => 'demo-vnc-tn-frs.jpg',
            'image_source_url' => $u . '/photo-1606107557195-0e29a4b5b4aa' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-tn-frs-b.jpg', 'url' => $u . '/photo-1560769629-975ec94e6a10' . $q],
                ['file' => 'demo-vnc-tn-frs-c.jpg', 'url' => $u . '/photo-1608231387042-66d1773070a5' . $q],
                ['file' => 'demo-vnc-tn-frs-d.jpg', 'url' => $u . '/photo-1595341888016-39293b6b0ffb' . $q],
            ],
            'stock' => 34,
            'brand' => 'Vincit Lab',
            'size' => '36, 37, 38, 39, 40, 41, 42, 43, 44, 45',
            'color' => 'Branco / sola creme',
            'material' => 'Couro sintético',
            'gender' => 'unissex',
            'featured' => 1,
            'meta_title' => 'Ténis Fresh Press | Vincit',
            'meta_description' => 'Ténis brancos court — demonstração.',
        ],
        [
            'category_slug' => 'acessorios',
            'name' => 'Cinto couro "Loop"',
            'slug' => 'cinto-couro-loop',
            'short_description' => 'Fivela minimalista, couro cheio. Acabamento mate.',
            'description' => 'Cinto unissex para jeans ou cargo. Demonstração com fotos de produto em estúdio.',
            'price' => 34.90,
            'sale_price' => null,
            'image' => 'demo-vnc-cin-lp.jpg',
            'image_source_url' => $u . '/photo-1624222247344-550fb60583fd' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-cin-lp-b.jpg', 'url' => $u . '/photo-1622495806645-e6369d1e66b5' . $q],
            ],
            'stock' => 42,
            'brand' => 'Vincit',
            'size' => '80, 85, 90, 95, 100, 105 cm',
            'color' => 'Preto',
            'material' => 'Couro bovino',
            'gender' => 'unissex',
            'featured' => 0,
            'meta_title' => 'Cinto Loop | Vincit',
            'meta_description' => 'Cinto couro — demonstração.',
        ],
        [
            'category_slug' => 'camisolas',
            'name' => 'Casaco polar "Cold Tape"',
            'slug' => 'casaco-polar-cold-tape',
            'short_description' => 'Tecido polar quente, fecho integral. Ideal para inverno.',
            'description' => 'Casaco técnico estilo fleece para camadas. Nome brinca com mixtapes e noites frias — fictício.',
            'price' => 72.00,
            'sale_price' => null,
            'image' => 'demo-vnc-flc-ctp.jpg',
            'image_source_url' => $u . '/photo-1618354691373-d851c5c3a990' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-flc-ctp-b.jpg', 'url' => $u . '/photo-1434389677669-e08b4cac3105' . $q],
                ['file' => 'demo-vnc-flc-ctp-c.jpg', 'url' => $u . '/photo-1441986300917-64674bd600d8' . $q],
            ],
            'stock' => 31,
            'brand' => 'Vincit',
            'size' => 'XS, S, M, L, XL, XXL',
            'color' => 'Creme',
            'material' => 'Poliéster polar reciclado',
            'gender' => 'unissex',
            'featured' => 0,
            'meta_title' => 'Casaco polar Cold Tape | Vincit',
            'meta_description' => 'Fleece street — demonstração.',
        ],
        [
            'category_slug' => 'homem',
            'name' => 'Overshirt xadrez "Sampling"',
            'slug' => 'overshirt-xadrez-sampling',
            'short_description' => 'Camada intermédia, xadrez clássico. Aberto ou fechado.',
            'description' => 'Overshirt em flanela para sobrepor t-shirts. Referência ao sampling na produção — peça de demo.',
            'price' => 69.00,
            'sale_price' => null,
            'image' => 'demo-vnc-osh-smp.jpg',
            'image_source_url' => $u . '/photo-1596755094514-f87e34085b87' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-osh-smp-b.jpg', 'url' => $u . '/photo-1504196606670-baf8868de872' . $q],
                ['file' => 'demo-vnc-osh-smp-c.jpg', 'url' => $u . '/photo-1617127365659-c47fa864d8bc' . $q],
            ],
            'stock' => 24,
            'brand' => 'Vincit Lab',
            'size' => 'S, M, L, XL, XXL',
            'color' => 'Azul / vermelho',
            'material' => 'Algodão flanela',
            'gender' => 'homem',
            'featured' => 1,
            'meta_title' => 'Overshirt Sampling | Vincit',
            'meta_description' => 'Overshirt xadrez — demonstração.',
        ],
        [
            'category_slug' => 'mulher',
            'name' => 'Saia plissada "Neon Bairro"',
            'slug' => 'saia-plissada-neon-bairro',
            'short_description' => 'Comprimento médio, plissado fino. Movimento no passo.',
            'description' => 'Saia plissada para combinar com sneakers ou botas. Visual nocturno urbano — produto fictício.',
            'price' => 54.90,
            'sale_price' => null,
            'image' => 'demo-vnc-sai-nbn.jpg',
            'image_source_url' => $u . '/photo-1583496661160-fb5886a0aa0b' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-sai-nbn-b.jpg', 'url' => $u . '/photo-1496747611176-843222e1e57c' . $q],
            ],
            'stock' => 20,
            'brand' => 'Vincit',
            'size' => '34, 36, 38, 40, 42, 44',
            'color' => 'Preto',
            'material' => 'Poliéster plissado',
            'gender' => 'mulher',
            'featured' => 0,
            'meta_title' => 'Saia Neon Bairro | Vincit',
            'meta_description' => 'Saia plissada — demonstração.',
        ],
        [
            'category_slug' => 'acessorios',
            'name' => 'Tote canvas "Press Kit"',
            'slug' => 'tote-canvas-press-kit',
            'short_description' => 'Alças largas, lona resistente. Cabe vinyl e portátil.',
            'description' => 'Saco tote em canvas para o dia a dia criativo. Demonstração com fotos de lifestyle.',
            'price' => 26.90,
            'sale_price' => null,
            'image' => 'demo-vnc-tot-pk.jpg',
            'image_source_url' => $u . '/photo-1590874103328-eac38a683ce7' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-tot-pk-b.jpg', 'url' => $u . '/photo-1544816155-12df9643f363' . $q],
            ],
            'stock' => 48,
            'brand' => 'Vincit',
            'size' => 'Único (42 × 38 cm)',
            'color' => 'Natural cru',
            'material' => 'Lona de algodão',
            'gender' => 'unissex',
            'featured' => 0,
            'meta_title' => 'Tote Press Kit | Vincit',
            'meta_description' => 'Tote bag — demonstração.',
        ],
        [
            'category_slug' => 'acessorios',
            'name' => 'Óculos de sol "Gold Chain"',
            'slug' => 'oculos-sol-gold-chain',
            'short_description' => 'Armação acetato, lentes escuras. Protecção UV400.',
            'description' => 'Óculos de sol unissex com estética retro-hip-hop. Produto fictício; fotos de produto genéricas.',
            'price' => 59.00,
            'sale_price' => 49.90,
            'image' => 'demo-vnc-oc-gch.jpg',
            'image_source_url' => $u . '/photo-1511499767150-a48a237f0083' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-oc-gch-b.jpg', 'url' => $u . '/photo-1572635196237-14b3f281503f' . $q],
            ],
            'stock' => 33,
            'brand' => 'Vincit Lab',
            'size' => 'Único',
            'color' => 'Tartaruga / dourado',
            'material' => 'Acetato, lentes policarbonato',
            'gender' => 'unissex',
            'featured' => 1,
            'meta_title' => 'Óculos Gold Chain | Vincit',
            'meta_description' => 'Óculos de sol — demonstração.',
        ],
        [
            'category_slug' => 'cal-as',
            'name' => 'Ganga mom "Tape Deck"',
            'slug' => 'ganga-mom-tape-deck',
            'short_description' => 'Cintura alta, perna afunilada. Lavagem anos 90.',
            'description' => 'Calças de ganga corte mom para mulher. Nome evoca cassetes e nostalgia — demonstração.',
            'price' => 76.00,
            'sale_price' => null,
            'image' => 'demo-vnc-gng-tdk.jpg',
            'image_source_url' => $u . '/photo-1541099649105-f69ad21f3246' . $q,
            'gallery' => [
                ['file' => 'demo-vnc-gng-tdk-b.jpg', 'url' => $u . '/photo-1562157873-818bc0726f68' . $q],
                ['file' => 'demo-vnc-gng-tdk-c.jpg', 'url' => $u . '/photo-1582418702059-220654af1b31' . $q],
            ],
            'stock' => 27,
            'brand' => 'Vincit',
            'size' => '32, 34, 36, 38, 40, 42, 44',
            'color' => 'Azul vintage',
            'material' => '98% algodão, 2% elastano',
            'gender' => 'mulher',
            'featured' => 1,
            'meta_title' => 'Ganga mom Tape Deck | Vincit',
            'meta_description' => 'Ganga mulher — demonstração.',
        ],

        // ===================================================================
        // PACK EXTRA — +15 peças (PAP). Slugs novos, imagens Unsplash livres.
        // ===================================================================
        [
            'category_slug' => 'camisolas', 'name' => 'Camisola de gola alta "Névoa"', 'slug' => 'camisola-gola-alta-nevoa',
            'short_description' => 'Malha canelada quentinha, gola alta. Para o frio da cidade.',
            'description' => 'Camisola unissex em malha canelada, gola alta ajustada. Peça fictícia de demonstração.',
            'price' => 54.90, 'sale_price' => 44.90, 'image' => 'demo-vnc-x01.jpg',
            'image_source_url' => $u . '/photo-1489987707025-afc232f7ea0f' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x01b.jpg', 'url' => $u . '/photo-1554568218-0f1715e72254' . $q] ],
            'stock' => 55, 'brand' => 'Vincit', 'size' => 'S, M, L, XL', 'color' => 'Cinza', 'material' => '70% algodão, 30% lã', 'gender' => 'unissex', 'featured' => 0,
            'meta_title' => 'Camisola gola alta Névoa | Vincit', 'meta_description' => 'Camisola canelada — demonstração.',
        ],
        [
            'category_slug' => 'camisolas', 'name' => 'T-shirt oversize "Eco"', 'slug' => 't-shirt-oversize-eco',
            'short_description' => 'Corte largo, algodão orgânico. Conforto total.',
            'description' => 'T-shirt oversize de algodão orgânico, costuras reforçadas. Produto fictício de demonstração.',
            'price' => 24.90, 'sale_price' => null, 'image' => 'demo-vnc-x02.jpg',
            'image_source_url' => $u . '/photo-1602293589930-45aad59ba3ab' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x02b.jpg', 'url' => $u . '/photo-1583743814966-8936f5b7be1a' . $q] ],
            'stock' => 120, 'brand' => 'Vincit', 'size' => 'XS, S, M, L, XL, XXL', 'color' => 'Bege', 'material' => '100% algodão orgânico', 'gender' => 'unissex', 'featured' => 1,
            'meta_title' => 'T-shirt oversize Eco | Vincit', 'meta_description' => 'T-shirt oversize — demonstração.',
        ],
        [
            'category_slug' => 'camisolas', 'name' => 'Sweat com fecho "Trilho"', 'slug' => 'sweat-fecho-trilho',
            'short_description' => 'Sweat com fecho e bolsos. Vibe outdoor urbana.',
            'description' => 'Sweatshirt com fecho integral e dois bolsos laterais. Peça fictícia de demonstração.',
            'price' => 64.90, 'sale_price' => null, 'image' => 'demo-vnc-x03.jpg',
            'image_source_url' => $u . '/photo-1594633312681-425c7b97ccd1' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x03b.jpg', 'url' => $u . '/photo-1503341504253-dff4815485f1' . $q] ],
            'stock' => 45, 'brand' => 'Vincit', 'size' => 'S, M, L, XL', 'color' => 'Verde-musgo', 'material' => '80% algodão, 20% poliéster', 'gender' => 'homem', 'featured' => 0,
            'meta_title' => 'Sweat com fecho Trilho | Vincit', 'meta_description' => 'Sweat com fecho — demonstração.',
        ],
        [
            'category_slug' => 'cal-as', 'name' => 'Jeans slim "Meia-Noite"', 'slug' => 'jeans-slim-meia-noite',
            'short_description' => 'Ganga escura, corte slim. Combina com tudo.',
            'description' => 'Jeans de corte slim em ganga elástica azul-escura. Produto fictício de demonstração.',
            'price' => 69.90, 'sale_price' => 54.90, 'image' => 'demo-vnc-x04.jpg',
            'image_source_url' => $u . '/photo-1542291026-7eec264c27ff' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x04b.jpg', 'url' => $u . '/photo-1596755094514-f87e34085b2c' . $q] ],
            'stock' => 60, 'brand' => 'Vincit', 'size' => '38, 40, 42, 44, 46', 'color' => 'Azul-escuro', 'material' => '98% algodão, 2% elastano', 'gender' => 'homem', 'featured' => 1,
            'meta_title' => 'Jeans slim Meia-Noite | Vincit', 'meta_description' => 'Jeans slim — demonstração.',
        ],
        [
            'category_slug' => 'cal-as', 'name' => 'Calças chino "Domingo"', 'slug' => 'calcas-chino-domingo',
            'short_description' => 'Chino leve e casual. Do escritório ao café.',
            'description' => 'Calças chino em sarja leve, corte reto. Peça fictícia de demonstração.',
            'price' => 49.90, 'sale_price' => null, 'image' => 'demo-vnc-x05.jpg',
            'image_source_url' => $u . '/photo-1473966968600-fa801b869a1a' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x05b.jpg', 'url' => $u . '/photo-1618932260643-eee4a2f652a6' . $q] ],
            'stock' => 70, 'brand' => 'Vincit', 'size' => '38, 40, 42, 44', 'color' => 'Caqui', 'material' => '100% algodão', 'gender' => 'homem', 'featured' => 0,
            'meta_title' => 'Calças chino Domingo | Vincit', 'meta_description' => 'Chino casual — demonstração.',
        ],
        [
            'category_slug' => 'cal-as', 'name' => 'Leggings "Fluxo"', 'slug' => 'leggings-fluxo',
            'short_description' => 'Leggings de treino, tecido respirável.',
            'description' => 'Leggings de cintura alta em tecido técnico respirável. Produto fictício de demonstração.',
            'price' => 34.90, 'sale_price' => 27.90, 'image' => 'demo-vnc-x06.jpg',
            'image_source_url' => $u . '/photo-1548036328-c9fa89d128fa' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x06b.jpg', 'url' => $u . '/photo-1547949003-9792a18a2601' . $q] ],
            'stock' => 90, 'brand' => 'Vincit', 'size' => 'XS, S, M, L', 'color' => 'Preto', 'material' => '88% poliéster, 12% elastano', 'gender' => 'mulher', 'featured' => 0,
            'meta_title' => 'Leggings Fluxo | Vincit', 'meta_description' => 'Leggings de treino — demonstração.',
        ],
        [
            'category_slug' => 'cal-as', 'name' => 'Fato de treino "Bloco"', 'slug' => 'joggers-bloco',
            'short_description' => 'Joggers com punhos elásticos. Conforto de estúdio.',
            'description' => 'Joggers em felpa com punhos e cintura elásticos. Peça fictícia de demonstração.',
            'price' => 44.90, 'sale_price' => null, 'image' => 'demo-vnc-x07.jpg',
            'image_source_url' => $u . '/photo-1552374196-c4e7ffc6e126' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x07b.jpg', 'url' => $u . '/photo-1611312449408-fcece27cdbb7' . $q] ],
            'stock' => 65, 'brand' => 'Vincit', 'size' => 'S, M, L, XL', 'color' => 'Cinza-mesclado', 'material' => '80% algodão, 20% poliéster', 'gender' => 'unissex', 'featured' => 0,
            'meta_title' => 'Joggers Bloco | Vincit', 'meta_description' => 'Joggers de estúdio — demonstração.',
        ],
        [
            'category_slug' => 'sapatos', 'name' => 'Sneakers "Asfalto"', 'slug' => 'sneakers-asfalto',
            'short_description' => 'Ténis urbanos, sola chunky. Para andar o dia todo.',
            'description' => 'Sneakers de silhueta chunky em pele sintética. Produto fictício de demonstração.',
            'price' => 99.90, 'sale_price' => 84.90, 'image' => 'demo-vnc-x08.jpg',
            'image_source_url' => $u . '/photo-1600185365483-26d7a4cc7519' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x08b.jpg', 'url' => $u . '/photo-1567401893414-76b7b1e5a7a5' . $q] ],
            'stock' => 40, 'brand' => 'Vincit', 'size' => '39, 40, 41, 42, 43, 44', 'color' => 'Branco', 'material' => 'Pele sintética / borracha', 'gender' => 'unissex', 'featured' => 1,
            'meta_title' => 'Sneakers Asfalto | Vincit', 'meta_description' => 'Sneakers urbanos — demonstração.',
        ],
        [
            'category_slug' => 'sapatos', 'name' => 'Ténis running "Vento"', 'slug' => 'tenis-running-vento',
            'short_description' => 'Leves e com boa amortização. Para correr ou passear.',
            'description' => 'Ténis de corrida leves com entressola em espuma. Peça fictícia de demonstração.',
            'price' => 79.90, 'sale_price' => null, 'image' => 'demo-vnc-x09.jpg',
            'image_source_url' => $u . '/photo-1542291026-7eec264c27ff' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x09b.jpg', 'url' => $u . '/photo-1595950653106-6c9ebd614d3a' . $q] ],
            'stock' => 50, 'brand' => 'Vincit', 'size' => '39, 40, 41, 42, 43', 'color' => 'Azul', 'material' => 'Malha técnica / borracha', 'gender' => 'unissex', 'featured' => 0,
            'meta_title' => 'Ténis running Vento | Vincit', 'meta_description' => 'Ténis de corrida — demonstração.',
        ],
        [
            'category_slug' => 'sapatos', 'name' => 'Botas "Cascalho"', 'slug' => 'botas-cascalho',
            'short_description' => 'Botas robustas para o inverno urbano.',
            'description' => 'Botas de cano médio em pele sintética com sola tratorada. Produto fictício de demonstração.',
            'price' => 109.90, 'sale_price' => null, 'image' => 'demo-vnc-x10.jpg',
            'image_source_url' => $u . '/photo-1608667508764-33cf0726b13a' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x10b.jpg', 'url' => $u . '/photo-1544022613-e87ca75a784a' . $q] ],
            'stock' => 30, 'brand' => 'Vincit', 'size' => '40, 41, 42, 43, 44', 'color' => 'Castanho', 'material' => 'Pele sintética / borracha', 'gender' => 'homem', 'featured' => 0,
            'meta_title' => 'Botas Cascalho | Vincit', 'meta_description' => 'Botas de inverno — demonstração.',
        ],
        [
            'category_slug' => 'acessorios', 'name' => 'Gorro de malha "Bruma"', 'slug' => 'gorro-malha-bruma',
            'short_description' => 'Gorro quentinho, malha macia. Toque final do outfit.',
            'description' => 'Gorro em malha canelada, dobra frontal. Peça fictícia de demonstração.',
            'price' => 16.90, 'sale_price' => 12.90, 'image' => 'demo-vnc-x11.jpg',
            'image_source_url' => $u . '/photo-1576871337622-98d48d1cf531' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x11b.jpg', 'url' => $u . '/photo-1517841905240-472988babdf9' . $q] ],
            'stock' => 100, 'brand' => 'Vincit', 'size' => 'Único', 'color' => 'Preto', 'material' => '100% acrílico', 'gender' => 'unissex', 'featured' => 0,
            'meta_title' => 'Gorro de malha Bruma | Vincit', 'meta_description' => 'Gorro de malha — demonstração.',
        ],
        [
            'category_slug' => 'acessorios', 'name' => 'Óculos de sol "Eclipse"', 'slug' => 'oculos-sol-eclipse',
            'short_description' => 'Armação redonda, lentes escuras. Estilo garantido.',
            'description' => 'Óculos de sol com armação redonda e proteção UV400. Produto fictício de demonstração.',
            'price' => 29.90, 'sale_price' => null, 'image' => 'demo-vnc-x12.jpg',
            'image_source_url' => $u . '/photo-1511499767150-a48a237f0083' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x12b.jpg', 'url' => $u . '/photo-1560243563-062bfc001d68' . $q] ],
            'stock' => 80, 'brand' => 'Vincit', 'size' => 'Único', 'color' => 'Preto', 'material' => 'Acetato / vidro', 'gender' => 'unissex', 'featured' => 1,
            'meta_title' => 'Óculos de sol Eclipse | Vincit', 'meta_description' => 'Óculos de sol — demonstração.',
        ],
        [
            'category_slug' => 'acessorios', 'name' => 'Carteira de couro "Nó"', 'slug' => 'carteira-couro-no',
            'short_description' => 'Carteira compacta, couro genuíno. Cabe no bolso.',
            'description' => 'Carteira de couro com compartimentos para cartões e notas. Peça fictícia de demonstração.',
            'price' => 34.90, 'sale_price' => 27.90, 'image' => 'demo-vnc-x13.jpg',
            'image_source_url' => $u . '/photo-1627123424574-724758594e93' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x13b.jpg', 'url' => $u . '/photo-1556306535-0f09a537f0a3' . $q] ],
            'stock' => 75, 'brand' => 'Vincit', 'size' => 'Único', 'color' => 'Castanho', 'material' => 'Couro genuíno', 'gender' => 'unissex', 'featured' => 0,
            'meta_title' => 'Carteira de couro Nó | Vincit', 'meta_description' => 'Carteira de couro — demonstração.',
        ],
        [
            'category_slug' => 'mulher', 'name' => 'Vestido midi "Maré"', 'slug' => 'vestido-midi-mare',
            'short_description' => 'Vestido fluido de comprimento midi. Leve e elegante.',
            'description' => 'Vestido midi em viscose fluida, cintura marcada. Produto fictício de demonstração.',
            'price' => 59.90, 'sale_price' => null, 'image' => 'demo-vnc-x14.jpg',
            'image_source_url' => $u . '/photo-1595950653106-6c9ebd614d3a' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x14b.jpg', 'url' => $u . '/photo-1509631179647-0177331693ae' . $q] ],
            'stock' => 40, 'brand' => 'Vincit', 'size' => 'XS, S, M, L, XL', 'color' => 'Verde-água', 'material' => '100% viscose', 'gender' => 'mulher', 'featured' => 1,
            'meta_title' => 'Vestido midi Maré | Vincit', 'meta_description' => 'Vestido midi — demonstração.',
        ],
        [
            'category_slug' => 'homem', 'name' => 'Camisa de linho "Costa"', 'slug' => 'camisa-linho-costa',
            'short_description' => 'Camisa de linho fresca. Perfeita para o verão.',
            'description' => 'Camisa de manga comprida em linho leve, corte regular. Peça fictícia de demonstração.',
            'price' => 47.90, 'sale_price' => 39.90, 'image' => 'demo-vnc-x15.jpg',
            'image_source_url' => $u . '/photo-1602810318383-e386cc2a3ccf' . $q,
            'gallery' => [ ['file' => 'demo-vnc-x15b.jpg', 'url' => $u . '/photo-1620799139507-2a76f79a2f4d' . $q] ],
            'stock' => 55, 'brand' => 'Vincit', 'size' => 'S, M, L, XL', 'color' => 'Branco', 'material' => '100% linho', 'gender' => 'homem', 'featured' => 0,
            'meta_title' => 'Camisa de linho Costa | Vincit', 'meta_description' => 'Camisa de linho — demonstração.',
        ],
    ];
}

/**
 * Diretório FS para imagens de produto (funciona com ou sem config.php carregado — ex.: install_process).
 */
function vincit_demo_product_images_fs_dir(): string {
    if (function_exists('vincit_product_upload_fs_dir')) {
        return vincit_product_upload_fs_dir();
    }
    $base = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
    return $base . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
}

/**
 * Insere produtos de demonstração (ignora slugs já existentes).
 * Descarrega imagens (capa + galeria) para site/assets/img/products/ quando possível.
 *
 * @return array{inserted: int, skipped: int}
 */
function seedDemoProducts(PDO $pdo) {
    require_once __DIR__ . '/product_sizes_lib.php';

    $rows = getDemoProductRows();
    $pair = $pdo->query('SELECT slug, id FROM categories')->fetchAll(PDO::FETCH_KEY_PAIR);
    $slugToId = $pair ?: [];

    $uploadDir = vincit_demo_product_images_fs_dir();
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $inserted = 0;
    $skipped = 0;

    $sql = 'INSERT INTO products (name, slug, description, short_description, price, sale_price, stock, category_id, brand, size, color, material, gender, image, gallery, featured, status, meta_title, meta_description)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
    $stmt = $pdo->prepare($sql);

    $checkStmt = $pdo->prepare('SELECT id FROM products WHERE slug = ?');

    foreach ($rows as $row) {
        $checkStmt->execute([$row['slug']]);
        if ($checkStmt->fetch()) {
            $skipped++;
            continue;
        }

        $cid = $slugToId[$row['category_slug']] ?? null;
        if (!$cid) {
            continue;
        }

        $image = null;
        if (!empty($row['image']) && !empty($row['image_source_url'])) {
            if (demoDownloadProductImage($uploadDir, $row['image'], $row['image_source_url'])) {
                $image = $row['image'];
            }
        }

        $galleryParts = [];
        $maxGallery = 2;
        if (!empty($row['gallery']) && is_array($row['gallery'])) {
            foreach ($row['gallery'] as $g) {
                if (count($galleryParts) >= $maxGallery) {
                    break;
                }
                if (empty($g['file']) || empty($g['url'])) {
                    continue;
                }
                if (demoDownloadProductImage($uploadDir, $g['file'], $g['url'])) {
                    $galleryParts[] = $g['file'];
                }
            }
        }
        $galleryStr = count($galleryParts) > 0 ? implode(',', $galleryParts) : null;

        $stmt->execute([
            $row['name'],
            $row['slug'],
            $row['description'],
            $row['short_description'],
            $row['price'],
            $row['sale_price'],
            $row['stock'],
            $cid,
            $row['brand'],
            $row['size'],
            $row['color'],
            $row['material'],
            $row['gender'],
            $image,
            $galleryStr,
            (int) $row['featured'],
            'active',
            $row['meta_title'],
            $row['meta_description'],
        ]);
        $newId = (int) $pdo->lastInsertId();
        if ($newId > 0) {
            try {
                vincit_seed_product_sizes_from_product_row($newId, (string) $row['size'], (int) $row['stock']);
            } catch (Throwable $e) {
                error_log('demo product_sizes: ' . $e->getMessage());
            }
        }
        $inserted++;
    }

    $updateImg = $pdo->prepare('UPDATE products SET image = ? WHERE slug = ? AND (image IS NULL OR image = \'\')');
    $updateGal = $pdo->prepare('UPDATE products SET gallery = ? WHERE slug = ? AND (gallery IS NULL OR gallery = \'\')');
    foreach ($rows as $row) {
        $galleryParts = [];
        $maxGallery = 2;
        if (!empty($row['gallery']) && is_array($row['gallery'])) {
            foreach ($row['gallery'] as $g) {
                if (count($galleryParts) >= $maxGallery) {
                    break;
                }
                if (empty($g['file']) || empty($g['url'])) {
                    continue;
                }
                if (demoDownloadProductImage($uploadDir, $g['file'], $g['url'])) {
                    $galleryParts[] = $g['file'];
                }
            }
        }
        $gStr = count($galleryParts) > 0 ? implode(',', $galleryParts) : null;

        if (!empty($row['image']) && !empty($row['image_source_url'])) {
            demoDownloadProductImage($uploadDir, $row['image'], $row['image_source_url']);
        }
        $imgPath = $uploadDir . ($row['image'] ?? '');
        if (is_file($imgPath)) {
            $updateImg->execute([$row['image'], $row['slug']]);
        }
        if ($gStr !== null && $gStr !== '') {
            $updateGal->execute([$gStr, $row['slug']]);
        }
    }

    return ['inserted' => $inserted, 'skipped' => $skipped];
}
