<?php $originalNames = require ROOT . '/app/original-projects.php'; ?>
      <div class="gallery">
        <?php foreach ($originalNames as $i => $title): ?>
        <figure class="gallery__item <?= in_array($i, [1,9], true) ? 'gallery__item--tall' : ($i === 4 ? 'gallery__item--wide' : '') ?> reveal">
          <img src="<?= h(sprintf('assets/jardin-%02d.jpeg', $i)) ?>" alt="Jardín residencial" loading="lazy" />
          <figcaption>
            <span><?= h(sprintf('%02d', $i)) ?></span>
            <h4><?= h($title) ?></h4>
            <p>Diseño exterior · 2025</p>
          </figcaption>
        </figure>
        <?php endforeach ?>
      </div>
