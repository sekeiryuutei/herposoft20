<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Programacionentregamercancia;

/**
 * ProgramacionentregamercanciaSearch represents the model behind the search form of `frontend\models\Programacionentregamercancia`.
 */
class ProgramacionentregamercanciaSearch extends Programacionentregamercancia
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idAgendaEntregaMercancia', 'idEmpleadoLogistica', 'idEstado', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at',  'fechaDesde', 'fechaHasta',], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $id=null)
    {
        if ($id){
            $query = Programacionentregamercancia::find()->where(['det.idAgendaEntregaMercancia' => $id]);
        }else{
            $query = Programacionentregamercancia::find();
        }

        $query->alias('det');

        $query->join('INNER JOIN', 'agendaentregamercancia ag', 'det.idAgendaEntregaMercancia = ag.id');

        $query->orderBy(['ag.fechaCita' => SORT_DESC, 'ag.idOrdenCompra' => SORT_ASC]);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'det.id' => $this->id,
            'det.idAgendaEntregaMercancia' => $this->idAgendaEntregaMercancia,
            'det.idEmpleadoLogistica' => $this->idEmpleadoLogistica,
            'det.idEstado' => $this->idEstado,
            'det.created_at' => $this->created_at,
            'det.created_by' => $this->created_by,
            'det.updated_at' => $this->updated_at,
            'det.updated_by' => $this->updated_by,
        ]);

        //var_dump($this->fechaDesde); die("hola");

        if ($this->fechaDesde && $this->fechaHasta) {
            $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
            $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
        
            // Aplicar filtro de rango de fechas
            $query->andFilterWhere(['between', 'CONVERT(VARCHAR(10), ag.fechaCita, 23)', $fechaInicio, $fechaFin]);
        }

        return $dataProvider;
    }
}
