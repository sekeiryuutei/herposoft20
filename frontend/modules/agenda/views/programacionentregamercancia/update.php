<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Programacionentregamercancia $model */

$this->title = 'Update Programacionentregamercancia: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Programacionentregamercancias', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="programacionentregamercancia-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
