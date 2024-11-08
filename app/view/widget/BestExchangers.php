<p class="widget-title">
    <?= $lang('Лучшие курсы') ?>
</p>
<div class="widget-slider  scroll-slider">
    <?php foreach ($bestCourses as $symbol => $data): ?>
        <?php $symbol = App::currency()->get($symbol) ?>
        <?php if ('price' === $symbol) continue; ?>
        <div class="widget-slider-item">
            <div class="widget-block def-box">
                <div class="widget-block-header">
                    <div class="widget-block-header-icon">
                        <img src="img/currency/<?= $symbol->getImage() ?>" alt="">
                    </div>
                    <div class="widget-block-header-text">
                        <p class="widget-block-header-text-title">
                            <?= $symbol ?>
                        </p>
                        <p class="widget-block-header-text-slogan">
                            <?= $lang("currency>{$symbol->name}") ?>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="widget-block-item">
                            <p class="widget-block-item-text">
                                <?= $lang('Куп.') ?>
                            </p>
                            <p class="widget-block-item-number">
                                <?= $data['buy']['price'] ?>
                            </p>

                            <div data-maxchild="1" class="mt-1">
                                <?php foreach($data['buy'] as $i => $name): ?>
                                    <?php if (!is_int($i)) continue; ?>
                                    <p class="widget-block-item-slogan"
                                       data-maxlen="15">
                                        <?= $lang("exchanger>{$name}") ?>
                                    </p>
                                <?php endforeach ?>
                                <div>
                                    <a class="widget-block-item-link"
                                       data-spoiler="open">
                                        <?= $lang('ещё') ?> +<?= count($data['buy']) - 2 ?>
                                    </a>
                                    <a class="widget-block-item-link d-none"
                                       data-spoiler="close">
                                        <?= $lang('Скрыть') ?>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-6">
                        <div class="widget-block-item">
                            <p class="widget-block-item-text">
                                <?= $lang('Прод.') ?>
                            </p>
                            <p class="widget-block-item-number">
                                <?= $data['sell']['price'] ?>
                            </p>

                            <div data-maxchild="1" class="mt-1">
                                <?php foreach($data['sell'] as $i => $name): ?>
                                    <?php if (!is_int($i)) continue; ?>
                                    <p class="widget-block-item-slogan"
                                       data-maxlen="15">
                                        <?= $lang("exchanger>{$name}") ?>
                                    </p>
                                <?php endforeach ?>
                                <div>
                                    <a class="widget-block-item-link"
                                       data-spoiler="open">
                                        <?= $lang('ещё') ?> +<?= count($data['sell']) - 2 ?>
                                    </a>
                                    <a class="widget-block-item-link d-none"
                                       data-spoiler="close">
                                        <?= $lang('Скрыть') ?>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach ?>
</div>
