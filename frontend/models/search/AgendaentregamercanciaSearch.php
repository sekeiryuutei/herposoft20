<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Agendaentregamercancia;

/**
 * AgendaentregamercanciaSearch represents the model behind the search form of `frontend\models\Agendaentregamercancia`.
 */
class AgendaentregamercanciaSearch extends Agendaentregamercancia
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idOrdenCompra', 'numeroCajas', 'idTransportadora', 'idEstado', 
            'created_by', 'updated_by', 'idAgenda'], 'integer'],
            [['fechaCita', 'contacto', 'fechaContacto', 'numeroGuia', 'observacion', 
            'created_at', 'updated_at', 'razonSocial', 'codigoTipoDocumento',
            'codigoCentroOperacion', 'numeroOrdenCompra', 'nit',
            'fechaDesde', 'fechaHasta', 'nombreEstado'], 'safe'],
            [['unidades'], 'number'],
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
    public function search($params, $idagenda = null)
    {
        $query = Agendaentregamercancia::find()->alias('det');

        $query->join('INNER JOIN', 'ordendecompra oc', 'det.idOrdenCompra = oc.id');
        $query->join('INNER JOIN', 'agendapresupuesto ap', 'det.idAgenda = ap.id');
        $query->join('INNER JOIN', 'estadoagenda es', 'det.idEstado = es.id');
        $query->join('INNER JOIN', 'proveedor pr', 'oc.idProveedor = pr.id');
        $query->join('INNER JOIN', 'tipodocumento td', 'oc.idTipoDocumento = td.id');
        $query->join('INNER JOIN', 'centrooperacion co', 'oc.idCO = co.id');
        $query->join('LEFT JOIN', 'transportadora tr', 'det.idTransportadora = tr.id');

        $query->select([
            'det.id',
            'det.fechaCita',
            'det.idCategoria',
            'det.unidades',
            'det.unidadesCumplidas',
            'det.numeroCajas',
            'det.contacto',
            'det.fechaContacto',
            'det.numeroGuia',
            'det.observacion',
            'det.idEstado',
            'det.idTransportadora',
            'det.idOrdenCompra',
            'det.idAgendaEntregaMercancia',
            'ap.desde',
            'ap.hasta',
            'es.nombre AS nombreEstado',
            'pr.nit',
            'pr.razonSocial',
            'oc.idTipoDocumento',
            'oc.idCO',
            'oc.consecutivo AS numeroOrdenCompra',
            'td.codigo AS codigoTipoDocumento',
            'co.codigo AS codigoCentroOperacion',
            'tr.nombre AS nombreTransportadora',
        ]);

        //$query->andWhere(['>', 'det.cargosAbonos', 0]);

        if ($idagenda != null){
            $query->andWhere(['=', 'det.idAgenda', $idagenda]);  
        }
            
        $query->orderBy(['det.created_at' => SORT_DESC]);

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
            'det.idOrdenCompra' => $this->idOrdenCompra,
            'det.idAgenda' => $this->idAgenda,
            //'det.fechaCita' => $this->fechaCita,
            'det.unidades' => $this->unidades,
            'det.numeroCajas' => $this->numeroCajas,
            'det.idTransportadora' => $this->idTransportadora,
            'det.fechaContacto' => $this->fechaContacto,
            'det.idEstado' => $this->idEstado,
            'det.idEstadoConteo' => $this->idEstadoConteo,
            'det.created_at' => $this->created_at,
            'det.created_by' => $this->created_by,
            'det.updated_at' => $this->updated_at,
            'det.updated_by' => $this->updated_by,
            'co.codigo' => $this->codigoCentroOperacion,
            'td.codigo' => $this->codigoTipoDocumento,
            'oc.consecutivo' => $this->numeroOrdenCompra,
            'pr.nit' => $this->nit
        ]);

        $query->andFilterWhere(['like', 'det.contacto', $this->contacto])
            ->andFilterWhere(['like', 'det.numeroGuia', $this->numeroGuia])
            ->andFilterWhere(['like', 'pr.razonSocial', $this->razonSocial])
            ->andFilterWhere(['like', 'det.observacion', $this->observacion]);

        if ($this->fechaDesde && $this->fechaHasta) {
            $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
            $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
        
            // Aplicar filtro de rango de fechas
            $query->andFilterWhere(['between', 'CONVERT(VARCHAR(10), det.fechaCita, 23)', $fechaInicio, $fechaFin]);
        }

        return $dataProvider;
    }

    public function searchxEstado($params, $lista_estados, $menu = null)
    {
        //$ids = isset($lista_estados) ? explode(',', $lista_estados) : [];

        $query = Agendaentregamercancia::find()->alias('det');

        $query->join('INNER JOIN', 'ordendecompra oc', 'det.idOrdenCompra = oc.id');
        $query->join('INNER JOIN', 'agendapresupuesto ap', 'det.idAgenda = ap.id');
        $query->join('INNER JOIN', 'estadoagenda es', 'det.idEstado = es.id');
        $query->join('INNER JOIN', 'proveedor pr', 'oc.idProveedor = pr.id');
        $query->join('INNER JOIN', 'tipodocumento td', 'oc.idTipoDocumento = td.id');
        $query->join('INNER JOIN', 'centrooperacion co', 'oc.idCO = co.id');
        $query->join('LEFT JOIN', 'transportadora tr', 'det.idTransportadora = tr.id');
        $query->join('LEFT JOIN', 'estadoconteo esc', 'det.idEstadoConteo = esc.id');

        $query->select([
            'det.id',
            'det.fechaCita',
            'det.idCategoria',
            'det.unidades',
            'det.unidadesCumplidas',
            'det.numeroCajas',
            'det.contacto',
            'det.fechaContacto',
            'det.numeroGuia',
            'det.observacion',
            'det.idEstado',
            'det.idEstadoConteo',
            'det.idOrdenCompra',
            'det.idTransportadora',
            'ap.desde',
            'ap.hasta',
            'es.nombre AS nombreEstado',
            'pr.nit',
            'pr.razonSocial',
            'oc.idTipoDocumento',
            'oc.idCO',
            'oc.consecutivo AS numeroOrdenCompra',
            'td.codigo AS codigoTipoDocumento',
            'co.codigo AS codigoCentroOperacion',
            'tr.nombre AS nombreTransportadora'
        ]);

         $query->orderBy(['det.created_at' => SORT_DESC]);

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

        if ($menu == 'recepcion'){
            if (!empty($lista_estados)) {
                $query->andWhere(['IN', 'det.idEstado', $lista_estados]);
            }
        }else{
            if (!empty($lista_estados)) {
                $query->andWhere(['IN', 'det.idEstadoConteo', $lista_estados]);
            }
        }

        // grid filtering conditions
        $query->andFilterWhere([
            //'det.idEstado' => $ids,
            'oc.consecutivo' => $this->numeroOrdenCompra,
        ]);

        if ($this->fechaDesde && $this->fechaHasta) {
            $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
            $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
        
            // Aplicar filtro de rango de fechas
            $query->andFilterWhere(['between', 'CONVERT(VARCHAR(10), det.fechaCita, 23)', $fechaInicio, $fechaFin]);
        }

        return $dataProvider;
    }

}
