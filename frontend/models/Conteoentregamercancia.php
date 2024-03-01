<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "conteoentregamercancia".
 *
 * @property int $id
 * @property int $idProgramacionEntregaMercancia
 * @property int $idItem
 * @property int $unidadesConteo
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Item $item
 * @property Programacionentregamercancia $programacionEntregaMercancia
 */
class Conteoentregamercancia extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conteoentregamercancia';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idProgramacionEntregaMercancia', 'idItem', 'unidadesConteo'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idProgramacionEntregaMercancia', 'idItem', 'unidadesConteo', 'unidadesAsignadas','created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idProgramacionEntregaMercancia'], 'exist', 'skipOnError' => true, 'targetClass' => Programacionentregamercancia::class, 'targetAttribute' => ['idProgramacionEntregaMercancia' => 'id']],
            [['idProgramacionEntregaMercancia', 'idItem'], 'unique', 'targetAttribute' => ['idProgramacionEntregaMercancia', 'idItem'], 'message' => 'La Referencia Ya Esta Asignado a la Orden de Compra.'],
            [['idItem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['idItem' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => function ($event) {
                    return Yii::$app->user->id;
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idProgramacionEntregaMercancia' => 'Programación',
            'idItem' => 'Item',
            'unidadesConteo' => 'UND Conteo',
            'unidadesAsignadas' => 'UND Asignadas',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem']);
    }

    /**
     * Gets query for [[ProgramacionEntregaMercancia]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProgramacionEntregaMercancia()
    {
        return $this->hasOne(Programacionentregamercancia::class, ['id' => 'idProgramacionEntregaMercancia']);
    }

    public static function grabarItemParaConteo ($idordencompra, $idprogramacionentregamercancia)
    {
        $query = Ordendecompradetalle::find()
                    ->select([  'det.idOrdenCompra', 
                                'det.idItem', 
                                'SUM(det.cantidadPendiente) AS cantidad'
                            ])
                    ->alias('det')
                    ->groupBy(['det.idOrdenCompra', 'det.idItem'])
                    ->andWhere(['det.idOrdenCompra' => $idordencompra]);

        $resultados = $query->all();

        foreach ($resultados as $resultado){
            $iditem = $resultado->idItem;

            $model = Conteoentregamercancia::findOne([
                                                        'idProgramacionEntregaMercancia' => $idprogramacionentregamercancia,
                                                        'idItem' => $iditem
                                                    ]);

            if (!$model){
                $model = new mercancia ();
                $model->idProgramacionEntregaMercancia = $idprogramacionentregamercancia;
                $model->idItem = $iditem;
                $model->unidadesConteo = 0;
                if ($model->save()){
                    return $model;
                }
            }

        }

        return null;

    }

}
