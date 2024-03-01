<?php

$this->registerCss('
    .mi-gridview {
        font-size: 12px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc; /* Color y grosor de la línea */
        margin: 10px 0; /* Espacio alrededor de la línea */
    }
');

use frontend\models\Ordendecompradetalle;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\OrdendecompradetalleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Ordendecompradetalles';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ordendecompradetalle-index">

    <?= GridView::widget([
        'dataProvider' => $dataProviderDetalleOC,

        'summary' => '',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'columns' => [
            //'consecutivo',
            //'idItem',

            [
                'attribute' => 'item', // Nombre del atributo en el modelo
                'label' => 'Item', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'referencia', // Nombre del atributo en el modelo
                'label' => 'Referencia', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'subcategoria', // Nombre del atributo en el modelo
                'label' => 'Subcategoría', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'marca', // Nombre del atributo en el modelo
                'label' => 'Marca', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'talla', // Nombre del atributo en el modelo
                'label' => 'Talla', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'color', // Nombre del atributo en el modelo
                'label' => 'Color', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'unidadEmpaque', // Nombre del atributo en el modelo
                'label' => 'UND Empaque', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'cantidad', // Nombre del atributo en el modelo
                'label' => 'Unidades', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales

            ],

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                //'headerOptions' => ['width' => '15%'],
                'template' => '{select}',

                'buttons' => [

                    'select' => function ($url, $model) use ($idprogramacion) {                                
                        return Html::a('<i class="fa fa-check"></i>',
                                [   'select', 'idprogramacion' => $idprogramacion, 'iditem' => $model->idItem, ], 
                                [
                                    'title' => 'Seleccionar Referencia',
                                    'class' => 'btn btn-default btn_select_item',
                                ]
                        );
                    },

                ],

            ],
        ],
    ]); ?>


</div>
