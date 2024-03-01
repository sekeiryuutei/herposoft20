<?php

namespace frontend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "estadoagenda".
 *
 * @property int $id
 * @property string $nombre
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Agendaentregamercancia[] $agendaentregamercancias
 */
class Estadoagenda extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'estadoagenda';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'codigo'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['nombre'], 'string', 'max' => 50],
            ['nombre', 'unique', 'message' => 'Nombre Estado Agenda ya está registrado.'],
            ['codigo', 'unique', 'message' => 'Código Estado Agenda ya está registrado.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'codigo' => 'Código',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Agendaentregamercancias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAgendaentregamercancias()
    {
        return $this->hasMany(Agendaentregamercancia::class, ['idEstado' => 'id']);
    }

    public static  function  getListaData(){
        $data = Estadoagenda::find()
                        ->select(['id', 'nombre'])
                        ->orderBy('nombre')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }
}
