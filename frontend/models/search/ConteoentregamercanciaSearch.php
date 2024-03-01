<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Conteoentregamercancia;

/**
 * ConteoentregamercanciaSearch represents the model behind the search form of `frontend\models\Conteoentregamercancia`.
 */
class ConteoentregamercanciaSearch extends Conteoentregamercancia
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idProgramacionEntregaMercancia', 'idItem', 'unidadesConteo', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
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
    public function search($params, $idprogramacion = null)
    {
        if ($idprogramacion == null){
            $query = Conteoentregamercancia::find();
        }else{
            $query = Conteoentregamercancia::find()->where(['idProgramacionEntregaMercancia' => $idprogramacion]);
        }

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
            'id' => $this->id,
            'idProgramacionEntregaMercancia' => $this->idProgramacionEntregaMercancia,
            'idItem' => $this->idItem,
            'unidadesConteo' => $this->unidadesConteo,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        return $dataProvider;
    }
}
