<?php

namespace common\models;

use Yii;

use yii\base\Model;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\RequestEvent;

use frontend\models\Proveedor;

class ProveedoresWs extends Model
{
	
	public $Id;
	public $Nit;
	public $Razon_Social;
	public $Tipo_Identificacion;
	public $Sucursal;
	public $Descripcion_Sucursal;
	public $Contacto;
	public $Direccion;
	public $Pais;
	public $Departamento;
	public $Ciudad;
	public $Telefono;
	public $Email;
	public $Celular;
	public $CriterioMercancia;
	
    public function rules()
    {
        return [
            [['Id', 'Nit', 'Razon_Social', 'Tipo_Identificacion', 'Sucursal'], 'required'],
            [['Id', 'Nit', 'Razon_Social', 'Tipo_Identificacion', 'Sucursal', 'Descripcion_Sucursal',
                'Contacto', 'Direccion', 'Pais', 'Ciudad', 'Departamento', 'Telefono', 'Email',
                'Celular', 'CriterioMercancia'
            ], 'string'
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'Id' => 'ID', 
			'Nit' => 'Nit', 
			'Razon_Social' => 'Razon Social', 
			'Tipo_Identificacion' => 'Tipo IDE', 
			'Sucursal' => 'Sucursal', 
			'Descripcion_Sucursal' => 'Descripcion_Sucursal', 
			'Contacto' => 'Contacto',
			'Direccion' => 'Direccion', 
			'Pais' => 'Pais', 
			'Departamento' => 'Departamento', 
			'Ciudad' => 'Ciudad', 
			'Telefono' => 'Telefono', 
			'Email' => 'Email',
            'Celular' => 'Celular',
            'CriterioMercancia' => 'Criterio Mercancia'
        ];
    }
	
	public function getAllProveedoresWs ($nit = null){

		if ($nit){
			$this->Nit = $nit;
		}

		$idCompania = Yii::$app->params['idCompania'];
		$descripcion = 'Proveedores';

		$conniKey = Yii::$app->params['conniKey'];
		$conniToken = Yii::$app->params['conniToken'];

		$responseData = [];
		
		try {
			$cliente = new Client();

			$url ="https://connektaqa.siesacloud.com/api/v3/ejecutarconsulta";

			$request = $cliente->createRequest()
				->setMethod('GET')
				->setUrl($url)
				->setData([
					'idCompania' => Yii::$app->params['idCompania'],
					'descripcion' => 'Proveedores',
					'parametros' => 'Nit=' .$this->Nit,
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
			
			//$responseData = $response['detalle']['Table'];
		} catch (\Exception $e) {
			// Capturar y manejar cualquier excepción que ocurra durante la solicitud
			$errorMessage = 'Error al realizar la solicitud: ' . $e->getMessage();
			die($errorMessage);
		}
			
		return $responseData;
	}

	public static function sincronizarERP ($nit = null){
        $respuesta = false;
		if ($nit){
			$respuesta = true;
			$modelWS = new ProveedoresWs();

			$lista = $modelWS->getAllProveedoresWs($nit);

			foreach($lista as $proveedor){

				$model = Proveedor::findOne(['idProveedor' => $proveedor['Id'],
											'nit' => $proveedor['Nit'],
											'sucursal' => $proveedor['Sucursal']
										]);
				if ($model == null){
					$model = new Proveedor();
					$model->idProveedor = $proveedor['Id'];
					$model->nit = $proveedor['Nit'];
					$model->sucursal = $proveedor['Sucursal'];
				}

				$model->razonSocial = $proveedor['Razon_Social'];
				$model->tipoIdentificacion = $proveedor['Tipo_Identificacion'];
				$model->descripcionSucursal = $proveedor['Descripcion_Sucursal'];
				$model->contacto = $proveedor['Contacto'];
				$model->direccion = $proveedor['Direccion'];
				$model->pais = $proveedor['Pais'];
				$model->ciudad = $proveedor['Ciudad'];
				$model->departamento = $proveedor['Departamento'];
				$model->telefono = $proveedor['Telefono'];
				$model->email = $proveedor['Email'];
				$model->celular = $proveedor['Celular'];
				$model->criterioMercancia = $proveedor['CriterioMercancia'];

				$respuesta = $model->save(); 

				if (!$respuesta){
					break;
				}
			}
		}

        return $respuesta;
    }
}
