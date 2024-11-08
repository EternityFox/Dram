<div class="tab def-box">
    <p class="tab-title">
        <?= $lang('Международные котировки') ?>
    </p>
    <div class="tab-btn scroll-slider">
        <div data-tab="0" class="tab-btn-item active">
            <?= $lang('Популярное') ?>
        </div>
        <div data-tab="1" class="tab-btn-item">
            <?= $lang('Курсы ЦБ') ?>
        </div>
        <div data-tab="2" class="tab-btn-item">
            <?= $lang('Криптовалюта') ?>
        </div>
<!--        <div data-tab="3" class="tab-btn-item">-->
<!--            Пункт 4-->
<!--        </div>-->
        <!--        <div data-tab="4" class="tab-btn-item idle">-->
        <!--            Пункт 5-->
        <!--        </div>-->
        <!--        <div data-tab="5" class="tab-btn-item idle">-->
        <!--            Пункт 6-->
        <!--        </div>-->
        <!--        <div data-tab="6" class="tab-btn-item idle">-->
        <!--            Пункт 7-->
        <!--        </div>-->
        <!--        <div data-tab="6" class="tab-btn-item hidden">-->
        <!--            hidden-->
        <!--        </div>-->
    </div>
    <div class="tab-content">

        <?php $num = 0; ?>
        <?php foreach ($intlCourses as $list) : ?>
            <?php ++$num ?>
            <div class="tab-content-item">
                <div class="tab-block">
                    <div class="tab-block-item">
                        <div data-maxchild="6">
                            <?php $active = true; ?>
                            <?php foreach ($list as $data) : ?>
                                <?php
                                if (is_string($data['diff']))
                                    $class = '';
                                else
                                    [$class, $data['diff']] = 0 > $data['diff']
                                        ? [' red', $data['diff']]
                                        : [' green', "+{$data['diff']}"];
                                ?>
                                <div class="tab-inner<?= $active ? ' active' : '' ?>" data-graph="<?= $data['symbol'] ?>" data-graph-num="<?= $num ?>">
                                    <div class="tab-inner-icon">
                                        <img src="img/currency/<?= $data['symbol']->getImage() ?>" alt="">
                                    </div>
                                    <div class="tab-inner-center">
                                        <p class="tab-inner-center-title">
                                            <?= $data['symbol'] ?>
                                        </p>
                                        <p class="tab-inner-center-text">
                                            <?= $lang("{$data['symbol']->name}") ?>
                                        </p>
                                    </div>
                                    <div class="tab-inner-right">
                                        <p class="tab-inner-right-title">
                                            <?= $data['price'] ?> <?= \App\App::$currencySymbols[$data['symbol']->symbol] ?? '' ?>
                                        </p>
                                        <p class="tab-inner-right-text<?= $class ?>">
                                            <?= $data['diff'] ?>
                                        </p>
                                    </div>
                                </div>
                                <?php $active = false; ?>
                            <?php endforeach ?>

                            <div class="d-block text-center">
                                <a class="widget-block-item-link d-none" data-spoiler="open" tabindex="0"><?= $lang('Показать всё') ?></a>
                                <a class="widget-block-item-link d-none" data-spoiler="close" tabindex="0"><?= $lang('Скрыть') ?></a>
                            </div>
                        </div>

                        <p class="tab-text">
                            <?= $lang('sources_info') ?>
                        </p>
                    </div>
                    <div class="tab-block-item">
                        <div style="width:100%;height:262px">
                            <canvas class="graph" id="graph_<?= $num ?>">
                                <!--<img src="img/graph.png" alt="">-->
                            </canvas>
                        </div>
                        <div class="reklama">
                            <?= random_elem($settings['banner_footer_small'], $settings['banner_footer_small_2'], $settings['banner_footer_small_3']) ?>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                $.ajax({
                                    url: 'ajax/chart/<?= $list[array_key_first($list)]['symbol'] ?>'
                                }).done(function(response) {
                                    let elem = document.getElementById('graph_<?= $num ?>')
                                        .getContext('2d');
                                    if (undefined === document.chart)
                                        document.chart = {};
                                    document.chart[<?= $num ?>] = new Chart(
                                        elem, {
                                            type: 'line',
                                            data: {
                                                labels: response.labels,
                                                datasets: [{
                                                    label: response.symbol,
                                                    backgroundColor: '#2D84EC',
                                                    borderColor: '#2D84EC',
                                                    data: response.data,
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false
                                            }
                                        });
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        <?php endforeach ?>

        <div class="tab-content-item">
            <div class="tab-block">
                <div class="tab-block-item">
                    <div class="tab-inner">
                        <div class="tab-inner-icon">
                            <img src="img/tab-icon.png" alt="">
                        </div>
                        <div class="tab-inner-center">
                            <p class="tab-inner-center-title">
                                Пункт 1
                            </p>
                            <p class="tab-inner-center-text">
                                XXX
                            </p>
                        </div>
                        <div class="tab-inner-right">
                            <p class="tab-inner-right-title">
                                10 000
                            </p>
                            <p class="tab-inner-right-text green">
                                0.05
                            </p>
                        </div>
                    </div>
                    <div class="tab-inner">
                        <div class="tab-inner-icon">
                            <img src="img/tab-icon.png" alt="">
                        </div>
                        <div class="tab-inner-center">
                            <p class="tab-inner-center-title">
                                Пункт 2
                            </p>
                            <p class="tab-inner-center-text">
                                XXX
                            </p>
                        </div>
                        <div class="tab-inner-right">
                            <p class="tab-inner-right-title">
                                10 000
                            </p>
                            <p class="tab-inner-right-text red">
                                -0.5
                            </p>
                        </div>
                    </div>
                    <div class="tab-inner">
                        <div class="tab-inner-icon">
                            <img src="img/tab-icon.png" alt="">
                        </div>
                        <div class="tab-inner-center">
                            <p class="tab-inner-center-title">
                                Пункт 3
                            </p>
                            <p class="tab-inner-center-text">
                                XXX
                            </p>
                        </div>
                        <div class="tab-inner-right">
                            <p class="tab-inner-right-title">
                                10 000
                            </p>
                            <p class="tab-inner-right-text red">
                                -0.5
                            </p>
                        </div>
                    </div>
                    <div class="tab-inner">
                        <div class="tab-inner-icon">
                            <img src="img/tab-icon.png" alt="">
                        </div>
                        <div class="tab-inner-center">
                            <p class="tab-inner-center-title">
                                Пункт 4
                            </p>
                            <p class="tab-inner-center-text">
                                XXX
                            </p>
                        </div>
                        <div class="tab-inner-right">
                            <p class="tab-inner-right-title">
                                10 000
                            </p>
                            <p class="tab-inner-right-text red">
                                -0.5
                            </p>
                        </div>
                    </div>
                    <div class="tab-inner">
                        <div class="tab-inner-icon">
                            <img src="img/tab-icon.png" alt="">
                        </div>
                        <div class="tab-inner-center">
                            <p class="tab-inner-center-title">
                                Пункт 5
                            </p>
                            <p class="tab-inner-center-text">
                                XXX
                            </p>
                        </div>
                        <div class="tab-inner-right">
                            <p class="tab-inner-right-title">
                                10 000
                            </p>
                            <p class="tab-inner-right-text red">
                                -0.5
                            </p>
                        </div>
                    </div>
                    <div class="tab-inner">
                        <div class="tab-inner-icon">
                            <img src="img/tab-icon.png" alt="">
                        </div>
                        <div class="tab-inner-center">
                            <p class="tab-inner-center-title">
                                Пункт 6
                            </p>
                            <p class="tab-inner-center-text">
                                XXX
                            </p>
                        </div>
                        <div class="tab-inner-right">
                            <p class="tab-inner-right-title">
                                10 000
                            </p>
                            <p class="tab-inner-right-text red">
                                -0.5
                            </p>
                        </div>
                    </div>
                    <p class="tab-text">
                        <?= $lang('sources_info') ?>
                    </p>
                </div>
                <div class="tab-block-item">
                    <canvas class="graph">
                        <!--<img src="img/graph.png" alt="">-->
                    </canvas>
                    <div class="reklama">
                        <img src="img/reklama.png" alt="">
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-content-item">
            <div class="tab-block">
                <div class="tab-block-item">
                    <div class="tab-inner">
                        <div class="tab-inner-icon">
                            <img src="img/tab-icon.png" alt="">
                        </div>
                        <div class="tab-inner-center">
                            <p class="tab-inner-center-title">
                                Пункт 1
                            </p>
                            <p class="tab-inner-center-text">
                                XXX
                            </p>
                        </div>
                        <div class="tab-inner-right">
                            <p class="tab-inner-right-title">
                                10 000
                            </p>
                            <p class="tab-inner-right-text green">
                                0.05
                            </p>
                        </div>
                    </div>
                    <div class="tab-inner">
                        <div class="tab-inner-icon">
                            <img src="img/tab-icon.png" alt="">
                        </div>
                        <div class="tab-inner-center">
                            <p class="tab-inner-center-title">
                                Пункт 2
                            </p>
                            <p class="tab-inner-center-text">
                                XXX
                            </p>
                        </div>
                        <div class="tab-inner-right">
                            <p class="tab-inner-right-title">
                                10 000
                            </p>
                            <p class="tab-inner-right-text red">
                                -0.5
                            </p>
                        </div>
                    </div>
                    <div class="tab-inner">
                        <div class="tab-inner-icon">
                            <img src="img/tab-icon.png" alt="">
                        </div>
                        <div class="tab-inner-center">
                            <p class="tab-inner-center-title">
                                Пункт 3
                            </p>
                            <p class="tab-inner-center-text">
                                XXX
                            </p>
                        </div>
                        <div class="tab-inner-right">
                            <p class="tab-inner-right-title">
                                10 000
                            </p>
                            <p class="tab-inner-right-text red">
                                -0.5
                            </p>
                        </div>
                    </div>
                    <div class="tab-inner">
                        <div class="tab-inner-icon">
                            <img src="img/tab-icon.png" alt="">
                        </div>
                        <div class="tab-inner-center">
                            <p class="tab-inner-center-title">
                                Пункт 4
                            </p>
                            <p class="tab-inner-center-text">
                                XXX
                            </p>
                        </div>
                        <div class="tab-inner-right">
                            <p class="tab-inner-right-title">
                                10 000
                            </p>
                            <p class="tab-inner-right-text red">
                                -0.5
                            </p>
                        </div>
                    </div>
                    <div class="tab-inner">
                        <div class="tab-inner-icon">
                            <img src="img/tab-icon.png" alt="">
                        </div>
                        <div class="tab-inner-center">
                            <p class="tab-inner-center-title">
                                Пункт 5
                            </p>
                            <p class="tab-inner-center-text">
                                XXX
                            </p>
                        </div>
                        <div class="tab-inner-right">
                            <p class="tab-inner-right-title">
                                10 000
                            </p>
                            <p class="tab-inner-right-text red">
                                -0.5
                            </p>
                        </div>
                    </div>
                    <div class="tab-inner">
                        <div class="tab-inner-icon">
                            <img src="img/tab-icon.png" alt="">
                        </div>
                        <div class="tab-inner-center">
                            <p class="tab-inner-center-title">
                                Пункт 6
                            </p>
                            <p class="tab-inner-center-text">
                                XXX
                            </p>
                        </div>
                        <div class="tab-inner-right">
                            <p class="tab-inner-right-title">
                                10 000
                            </p>
                            <p class="tab-inner-right-text red">
                                -0.5
                            </p>
                        </div>
                    </div>
                    <p class="tab-text">
                        <?= $lang('sources_info') ?>
                    </p>
                </div>
                <div class="tab-block-item">
                    <canvas class="graph">
                        <!--<img src="img/graph.png" alt="">-->
                    </canvas>
                    <div class="reklama">
                        <img src="img/reklama.png" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /*
<script>
document.addEventListener('DOMContentLoaded', function(){
    $.ajax({
        url: 'ajax/chart/USD'
    }).done(function(response) {
        let elem = document.querySelector('.graph').getContext('2d'),
            chart = new Chart(
            elem,
            {
                type: 'line',
                data: {
                    labels: response.labels,
                    datasets: [{
                        label: 'Курс',
                        backgroundColor: '#2D84EC',
                        borderColor: '#2D84EC',
                        data: response.data,
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false}
            }
        );
    });
});
</script>*/ ?>