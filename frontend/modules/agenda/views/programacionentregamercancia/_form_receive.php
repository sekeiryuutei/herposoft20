<?php

$this->registerCss('

    .btn-create {
        width: 300px;
    }

    .centrar {
        text-align: center;
    }
    
    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc; /* Color y grosor de la línea */
        margin: 10px 0; /* Espacio alrededor de la línea */
    }
    
');

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\number\NumberControl;

/** @var yii\web\View $this */
/** @var frontend\models\Programacionentregamercancia $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="programacionentregamercancia-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-programacionentregamercancia',
                    'enableAjaxValidation' => true,
                ]); 
    ?>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'unidadesCumplidas')->widget(NumberControl::classname(), [
                    'options' => ['placeholder' => 'Enter value...'],
                    'maskedInputOptions' => [
                        //'prefix' => '$ ',
                        //'suffix' => ' ¢',
                        'min' => 0,
                        'digits' => 0,
                        'allowMinus' => false,
                    ],
                    //'required' => true,
                    'pluginOptions' => [
                        'autofocus' => true, 
                    ],
                ]);
            ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
