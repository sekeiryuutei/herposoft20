<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "programacionentregamercancia".
 *
 * @property int $id
 * @property int $idAgendaEntregaMercancia
 * @property int $idEmpleadoLogistica
 * @property int $idEstado
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Agendaentregamercancia $idAgendaEntregaMercancia0
 */
class Programacionentregamercancia extends \yii\db\ActiveRecord
{
    public $fechaDesde;
    public $fechaHasta;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'programacionentregamercancia';
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
    public function rules()
    {
        return [
            [['idAgendaEntregaMercancia', 'idUserConteo'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idAgendaEntregaMercancia', 'idEmpleadoLogistica', 'idEstado', 'created_by', 
            'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idAgendaEntregaMercancia', 'idEmpleadoLogistica'], 'unique', 'targetAttribute' => ['idAgendaEntregaMercancia', 'idEmpleadoLogistica'], 'message' => 'El Colaborador Ya Esta Asignado a la Orden de Compra.'],
            [['idAgendaEntregaMercancia'], 'exist', 'skipOnError' => true, 'targetClass' => Agendaentregamercancia::class, 'targetAttribute' => ['idAgendaEntregaMercancia' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idAgendaEntregaMercancia' => 'Radicado',
            'idEmpleadoLogistica' => 'Empleado',
            'idUserConteo' => 'Usuario Conteo',
            'idEstado' => 'Estado',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[AgendaEntregaMercancia]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAgendaEntregaMercancia()
    {
        return $this->hasOne(Agendaentregamercancia::class, ['id' => 'idAgendaEntregaMercancia']);
    }

    public function getEmpleadoLogistica()
    {
        return $this->hasOne(Empleadologistica::class, ['id' => 'idEmpleadoLogistica']);
    }

    public function getEstado()
    {
        return $this->hasOne(Estadoprogramacion::class, ['id' => 'idEstado']);
    }

    public function getUserConteo()
    {
        return $this->hasOne(Userconteo::class, ['id' => 'idUserConteo']);
    }

}


