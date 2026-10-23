<?php
/**
 * Guia de tamanhos — referência roupa + tabela calçado (estilo RetroVintagePT)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

$pageTitle = 'Guia de Tamanhos';

include_once __DIR__ . '/../includes/header.php';
?>

<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li>Guia de Tamanhos</li>
            </ol>
        </nav>
    </div>
</section>

<main class="guia-page">
    <div class="container guia-container">
        <h1>Guia de Tamanhos</h1>
        <p class="guia-intro">Use estas tabelas como referência. As medidas podem variar entre marcas e modelos — em caso de dúvida, contacte-nos antes de encomendar.</p>

        <section class="guia-table-wrap">
            <h2>Roupa (unissexo — aprox.)</h2>
            <table class="guia-table">
                <thead>
                    <tr>
                        <th>Tamanho</th>
                        <th>Peito (cm)</th>
                        <th>Cintura (cm)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>XS</td><td>86–91</td><td>71–76</td></tr>
                    <tr><td>S</td><td>91–96</td><td>76–81</td></tr>
                    <tr><td>M</td><td>96–101</td><td>81–86</td></tr>
                    <tr><td>L</td><td>101–106</td><td>86–91</td></tr>
                    <tr><td>XL</td><td>106–111</td><td>91–96</td></tr>
                    <tr><td>XXL</td><td>111–116</td><td>96–101</td></tr>
                </tbody>
            </table>
        </section>

        <section class="guia-table-wrap">
            <h2>Calçado (EU / UK / CM)</h2>
            <table class="guia-table">
                <thead>
                    <tr>
                        <th>EU</th>
                        <th>UK</th>
                        <th>CM (comprimento pé)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>38</td><td>5</td><td>24</td></tr>
                    <tr><td>39</td><td>6</td><td>25</td></tr>
                    <tr><td>40</td><td>6.5</td><td>25,5</td></tr>
                    <tr><td>41</td><td>7.5</td><td>26</td></tr>
                    <tr><td>42</td><td>8</td><td>27</td></tr>
                    <tr><td>43</td><td>9</td><td>27,5</td></tr>
                    <tr><td>44</td><td>9.5</td><td>28</td></tr>
                    <tr><td>45</td><td>10.5</td><td>29</td></tr>
                </tbody>
            </table>
        </section>

        <p class="guia-tip">Dica: para calçado, meça o pé ao fim do dia e compare com a coluna CM. Entre dois tamanhos, o maior costuma ser mais seguro para quem usa meias mais grossas.</p>
    </div>
</main>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
