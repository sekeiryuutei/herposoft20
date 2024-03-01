<?php

namespace common\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

use yii\base\Model;
use yii\httpclient\Client;

class InventariosWs extends Model
{
	
	public $Item;
	public $Referencia;
	public $Descripcion;
	public $EAN;
	public $Extension1;
	public $Extension2;
	public $Bodega;
	public $NombreBodega;
	public $CantidadExistente;
	public $CantidadDisponible;
	public $CantidadComprometida;
	public $CostoPromedioUnitario;
	public $CantidadDisponible_POS;
	
    public function rules()
    {
        return [
            [['Item', 'Referencia', 'Descripcion'], 'required'],
			[['CantidadExistente', 'CantidadDisponible', 'CantidadComprometida', 'CantidadDisponible_POS'], 'integer'],
			[['CostoPromedioUnitario'], 'number'],
            [['Referencia', 'Descripcion', 'Extension1', 'Extension2', 'Bodega', 'NombreBodega', 'EAN'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'Referencia' => 'Referencia', 
			'Descripcion' => 'Descripción', 
			'Extension1' => 'Color', 
			'Extension2' => 'Talla', 
			'EAN' => 'EAN',
			'Bodega' => 'Bodega', 
			'NombreBodega' => 'Bodega', 
			'CantidadExistente' => 'Existencia', 
			'CantidadDisponible' => 'Disponible', 
			'CantidadComprometida' => 'Comprometida', 
			'CostoPromedioUnitario' => 'Costo Promedio',
			'CantidadDisponible_POS' => 'Disponible POS',
        ];
    }
	
	public function getAllInventariosSiesa (){
		$idCompania = Yii::$app->params['idCompania'];;
		$descripcion = 'Inventarios';

		$conniKey = Yii::$app->params['conniKey'];
		$conniToken = Yii::$app->params['conniToken'];

		$responseData = [];
		
		try {
			$cliente = new Client();

			$url = 'https://connektaqa.siesacloud.com/api/v3/ejecutarconsulta';

			$request = $cliente->createRequest()
				->setMethod('GET')
				->setUrl($url)
				->setData([
					'idCompania' => Yii::$app->params['idCompania'],
					'descripcion' => 'Inventarios',
					'parametros' => 'item=' .$this->Item,
				])
				->addHeaders([
					'conniKey' => $conniKey,
					'conniToken' => $conniToken,
				])
				->send();

			$response = json_decode($request->content, true);

			if ($response['codigo'] == 0) {
				$responseData = $response['detalle']['Table'];
			}else{
				$errorMessage = 'La solicitud no fue exitosa: ' . $response['codigo'] . ' - ' . $response['mensaje'];
			}

		} catch (\Exception $e) {
			// Capturar y manejar cualquier excepción que ocurra durante la solicitud
			$errorMessage = 'Error al realizar la solicitud: ' . $e->getMessage();
		}
			
		return $responseData;
	}
		
}
