<?php /* Carte avant/après. Attend $r = ['avant','apres','travaux','ville'] */ ?>
<figure class="compare-card">
  <div class="compare" style="--pos:50%">
    <div class="compare-layer after"><?= photo($r['apres'], 'Après : ' . $r['travaux'] . ' à ' . $r['ville'], 'cmp-img') ?><span class="tag tag-after">Après</span></div>
    <div class="compare-layer before"><?= photo($r['avant'], 'Avant : ' . $r['travaux'] . ' à ' . $r['ville'], 'cmp-img') ?><span class="tag tag-before">Avant</span></div>
    <div class="compare-handle" aria-hidden="true"><span></span></div>
    <input class="compare-range" type="range" min="0" max="100" value="50" aria-label="Comparer avant et après : <?= e($r['travaux']) ?>">
  </div>
  <figcaption><b><?= e($r['travaux']) ?></b><span><?= icon('pin') ?><?= e($r['ville']) ?></span></figcaption>
</figure>
