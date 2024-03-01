<?php

namespace frontend\modules\agenda\controllers;

use Yii;
use frontend\models\Agendaentregamercancia;
use frontend\models\search\AgendaentregamercanciaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\web\Response;

use frontend\models\Ordendecompra;
use frontend\models\Agendapresupuesto;
use frontend\models\Estadoagenda;
use frontend\models\search\AgendapresupuestoSearch;
use frontend\models\search\AgendapresupuestosubcategoriaSearch;

use frontend\models\DataOrdenCompra;
use frontend\models\Centrooperacion;
use frontend\models\Ordendecompradetalle;
use frontend\models\search\OrdendecompradetalleSearch;

use common\models\OrdenesCompraWs;

/**
 * AgendaentregamercanciaController implements the CRUD actions for Agendaentregamercancia model.
 */
class AgendaentregamercanciaController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Agendapresupuesto models.
     *
     * @return string
     */
    public function actionIndexagendaperiodo()
    {
        $searchModel = new AgendapresupuestoSearch();
        $idestado = 1;
        $dataProvider = $searchModel->search($this->request->queryParams, $idestado);

        return $this->render('index_agenda_periodo', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Agendaentregamercancia models.
     *
     * @return string
     */
    public function actionIndex($id)
    {
        $modelagenda = Agendapresupuesto::findOne(['id' => $id]);

        $searchModel = new AgendaentregamercanciaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelagenda' => $modelagenda,
        ]);
    }

    /**
     * Displays a single Agendaentregamercancia model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Agendaentregamercancia model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($idagenda)
    {
        // $model = new Agendaentregamercancia();
        $model = new DataOrdenCompra();
        $modelagenda = Agendapresupuesto::findOne(['id' => $idagenda]);

        $modelco = Centrooperacion::findOne(['codigo' => '002']);
        $model->idCentroOperacion = $modelco->id;

        $model->idAgenda = $idagenda;
        $model->desde = $modelagenda->desde;
        $model->hasta = $modelagenda->hasta;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $id = null;
                $mensajeError = 'Error Actualizando Registro';

                if ($model->validate()){

                    $respuesta = Agendaentregamercancia::grabarOrdenCompraCategoria($model);

                    if ($respuesta){
                        Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
                    }else{
                        Yii::$app->session->setFlash( 'error', $mensajeError);
                    }
                }

                return $this->redirect(['index', 'id' => $model->idAgenda]);
            }
        } 

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('create_orden_compra', [
                'model' => $model,
            ]);
        }  
    }

    /**
     * Updates an existing Agendaentregamercancia model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $codigoEstado = $model->estado->codigo;

        // 0 -> Sin Asignar 
        if ($codigoEstado != 0){
            Yii::$app->session->setFlash( 'error', 'Error Estado de la Orden de Compra No Permite Agendamiento ');
            return $this->redirect(['index', 'id' => $model->idAgenda]);
        }

        $model->codigoCentroOperacion = $model->ordenCompra->cO->codigo;
        $model->codigoTipoDocumento = $model->ordenCompra->tipoDocumento->codigo;
        $model->numeroOrdenCompra = $model->ordenCompra->consecutivo;
        $model->fechaOrden = $model->ordenCompra->fecha;
        $model->nit = $model->ordenCompra->proveedor->nit;
        $model->razonSocial = $model->ordenCompra->proveedor->razonSocial;
        $model->totalCantidadPedida = $model->ordenCompra->totalCantidadPedida;
        $model->totalCantidadPendiente = $model->ordenCompra->totalCantidadPendiente;
        $model->totalCantidadEntrada = $model->ordenCompra->totalCantidadEntrada;
        $model->fechaEntrega = $model->ordenCompra->fechaEntrega;

        $model->fechaAgenda = $model->ordenCompra->fechaEntrega;
        $model->fechaContacto = date('Y-m-d');
        $categoria = $model->categoria->nombre;

        $searchModelSubcategoria = new OrdendecompradetalleSearch();
        $dataProviderSubcategoria = $searchModelSubcategoria->searchResumenCategoria($model->idOrdenCompra, $model->idCategoria);

        //$listaValores = ['CEDI', 'TIENDAS']; // Lista de valores a filtrar
        $listaValores = ['CEDI'];
        $searchModelPresupuestoSubcategoria = new AgendapresupuestosubcategoriaSearch();
        $dataProviderPresupuestoSubcategoria = $searchModelPresupuestoSubcategoria->searchResumenSubcategoria($model->idAgenda, $categoria, $listaValores);

        $dataProviderDia = $searchModelPresupuestoSubcategoria->searchPivotCupoSubcategoriaDia(null, $model->idAgenda, $listaValores, $categoria, null);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $id = null;
                if ($model->validate()){

                    $modelestado = Estadoagenda::findOne(['codigo' => 1]);
                    $model->fechaCita = $model->fechaAgenda . ' ' . $model->horaAgenda;

                    $model->idEstado = $modelestado->id;

                    $id = $model->save();  
                    
                    if ($id != null){
                        Agendaentregamercancia::actualizarUnidadesAgendamiento (
                                                            $model->idAgenda,
                                                            $model->idOrdenCompra, 
                                                            $model->fechaAgenda
                                                        );
                    }
                }

                if ($id != null){
                    Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
                }else{
                    Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro');
                }

                return $this->redirect(['index', 'id' => $model->idAgenda]);
            }
        }

        /*if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }*/

        // Agrupar los datos por bodega
        $dataByCrossDocking = [];
        foreach ($dataProviderDia->getModels() as $modelagenda) {
            $crossdocking = $modelagenda['crossDocking'];
            if (!isset($dataByCrossDocking[$crossdocking])) {
                $dataByCrossDocking[$crossdocking] = [];
            }
            $dataByCrossDocking[$crossdocking][] = $modelagenda;
        }

        return $this->render('_view_orden_compra', [
            'model' => $model,
            'dataProviderSubcategoria' => $dataProviderSubcategoria,
            'dataProviderPresupuestoSubcategoria' => $dataProviderPresupuestoSubcategoria,
            'categoria' => $model->categoria->nombre,
            'dataProviderDia' => $dataProviderDia,
            'dataByCrossDocking' => $dataByCrossDocking,
        ]);
    }

    public function actionChange($id)
    {
        $modelold = $this->findModel($id);

        $codigoEstado = $modelold->estado->codigo;

        // 1 -> Agendamiento 
        if ($codigoEstado != 1){
            Yii::$app->session->setFlash( 'error', 'Error Estado de la Orden de Compra No Permite Re-Agendamiento ');
            return $this->redirect(['index', 'id' => $modelold->idAgenda]);
        }

        $model = new Agendaentregamercancia();
        $model->idAgenda = $modelold->idAgenda;
        $model->idOrdenCompra = $modelold->idOrdenCompra;
        $model->unidades = $modelold->unidades;
        $model->numeroCajas = $modelold->numeroCajas;
        $model->idTransportadora = $modelold->idTransportadora;
        $model->idCategoria = $modelold->idCategoria;

        $model->codigoCentroOperacion = $model->ordenCompra->cO->codigo;
        $model->codigoTipoDocumento = $model->ordenCompra->tipoDocumento->codigo;
        $model->numeroOrdenCompra = $model->ordenCompra->consecutivo;
        $model->fechaOrden = $model->ordenCompra->fecha;
        $model->nit = $model->ordenCompra->proveedor->nit;
        $model->razonSocial = $model->ordenCompra->proveedor->razonSocial;
        $model->totalCantidadPedida = $model->ordenCompra->totalCantidadPedida;
        $model->totalCantidadPendiente = $model->ordenCompra->totalCantidadPendiente;
        $model->totalCantidadEntrada = $model->ordenCompra->totalCantidadEntrada;
        $model->fechaEntrega = $model->ordenCompra->fechaEntrega;

        if ($model->fechaCita instanceof \DateTime) {
            $fechaCita = $this->fechaCita;
        } else {
            $fechaCita = new \DateTime($model->fechaCita);
        }

        $model->fechaAgenda = $fechaCita->format('Y-m-d');
        $model->horaAgenda = $fechaCita->format('H:i');

        $model->fechaContacto = date('Y-m-d');

        $categoria = $model->categoria->nombre;

        $searchModelSubcategoria = new OrdendecompradetalleSearch();
        $dataProviderSubcategoria = $searchModelSubcategoria->searchResumenCategoria($model->idOrdenCompra, $model->idCategoria);

        //$listaValores = ['CEDI', 'TIENDAS']; // Lista de valores a filtrar
        $listaValores = ['CEDI'];
        $searchModelPresupuestoSubcategoria = new AgendapresupuestosubcategoriaSearch();
        $dataProviderPresupuestoSubcategoria = $searchModelPresupuestoSubcategoria->searchResumenSubcategoria($model->idAgenda, $categoria, $listaValores);

        $dataProviderDia = $searchModelPresupuestoSubcategoria->searchPivotCupoSubcategoriaDia(null, $model->idAgenda, $listaValores, $categoria, null);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $id = null;
                if ($model->validate()){

                    $modelestado = Estadoagenda::findOne(['codigo' => 1]);
                    $model->fechaCita = $model->fechaAgenda . ' ' . $model->horaAgenda;

                    $model->idEstado = $modelestado->id;
                    $model->idAgendaEntregaMercancia = $modelold->id;

                    $id = $model->save();  
                    
                    if ($id != null){
                        Agendaentregamercancia::actualizarUnidadesAgendamiento (
                                                            $model->idAgenda,
                                                            $model->idOrdenCompra, 
                                                            $model->fechaAgenda
                                                        );
                    }

                    if ($id){
                        //var_dump($model->fechaCita); die("hola");

                        //$fechaObjeto = \DateTime::createFromFormat('Y-m-d', $model->fechaCita);

                        $modelold->idAgendaEntregaMercancia = $model->id;
                        $modelold->fechaAgenda = $model->fechaCita;

                        //$modelold->fechaAgenda = $model->fechaCita->format('Y-m-d');

                        $modelestado = Estadoagenda::findOne(['codigo' => 6]);
                        $modelold->idEstado = $modelestado->id;

                        $id = $modelold->save();  
                        $operacion = "restar";
                        
                        if ($id != null){
                            Agendaentregamercancia::actualizarUnidadesAgendamiento (
                                                                $modelold->idAgenda,
                                                                $modelold->idOrdenCompra, 
                                                                $modelold->fechaAgenda,
                                                                $operacion
                                                            );
                        }
                    }
                }

                if ($id != null){
                    Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
                }else{
                    Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro');
                }

                return $this->redirect(['index', 'id' => $model->idAgenda]);
            }
        }

        /*if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }*/
        $dataByCrossDocking = [];
        foreach ($dataProviderDia->getModels() as $modelagenda) {
            $crossdocking = $modelagenda['crossDocking'];
            if (!isset($dataByCrossDocking[$crossdocking])) {
                $dataByCrossDocking[$crossdocking] = [];
            }
            $dataByCrossDocking[$crossdocking][] = $modelagenda;
        }

        return $this->render('_view_orden_compra', [
            'model' => $model,
            'dataProviderSubcategoria' => $dataProviderSubcategoria,
            'dataProviderPresupuestoSubcategoria' => $dataProviderPresupuestoSubcategoria,
            'categoria' => $model->categoria->nombre,
            'dataProviderDia' => $dataProviderDia,
            'dataByCrossDocking' => $dataByCrossDocking,
        ]);
    }


    /**
     * Deletes an existing Agendaentregamercancia model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $idAgenda = $model->idAgenda;
        $codigoEstado = $model->estado->codigo;

        // 1 -> Agendamiento 
        if (($codigoEstado == 1) || ($codigoEstado == 0)){
            $ok = true;
        } else{
            Yii::$app->session->setFlash( 'error', 'Error Estado de la Orden de Compra No Permite Eliminar Este Registro ');
            return $this->redirect(['index', 'id' => $model->idAgenda]);
        }

        if ($model->delete()){
            Yii::$app->session->setFlash( 'success', 'Registro Eliminado');
        }else{
            Yii::$app->session->setFlash( 'error', 'Error Eliminando Registro');
        }

        return $this->redirect(['index', 'id' => $idAgenda]);
    }


    public function actionCancel ($id)
    {
        $model = $this->findModel($id);

        $codigoEstado = $model->estado->codigo;

        // 1 -> Agendamiento 
        if ($codigoEstado != 1){
            Yii::$app->session->setFlash( 'error', 'Error Estado de la Orden de Compra No Permite Cancelar Cita Entrega Mercancía ');
            return $this->redirect(['index', 'id' => $model->idAgenda]);
        }

        $modelestado = Estadoagenda::findOne(['codigo' => 3]);
        $model->idEstado = $modelestado->id;

        if ($model->save()){
            Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
        }else{
            Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro');
        }

        return $this->redirect(['index', 'id' => $model->idAgenda]);
    }

    /**
     * Finds the Agendaentregamercancia model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Agendaentregamercancia the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Agendaentregamercancia::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


    public function actionObtenerDatosOrden($idCentroOperacion, $idTipoDocumento, $numeroOrdenCompra)
    {
        // Buscar la orden de compra en la base de datos
        $ordenCompra = Ordendecompra::findOne(['idCO' => $idCentroOperacion,
                                                'idTipoDocumento' => $idTipoDocumento,
                                                'consecutivo' => $numeroOrdenCompra
                                            ]);

        if ($ordenCompra == null) {
            $model = new Ordendecompra();
            $model->idCO = $idCentroOperacion;
            $model->idTipoDocumento = $idTipoDocumento;

            $ordenCompra = OrdenesCompraWs::sincronizarERP ($model->cO->codigo, $model->tipoDocumento->codigo, $numeroOrdenCompra);
        }

        if ($ordenCompra !== null) {
            // La orden de compra fue encontrada, devolver los datos en formato JSON
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'id' => $ordenCompra->id,
                'fechaOrden' => $ordenCompra->fecha,
                'totalCantidadPedida' => $ordenCompra->totalCantidadPedida,
                'totalCantidadEntrada' => $ordenCompra->totalCantidadEntrada,
                'totalCantidadPendiente' => $ordenCompra->totalCantidadPendiente,
                'nitProveedor' => $ordenCompra->proveedor->nit,
                'razonSocial' => $ordenCompra->proveedor->razonSocial,
                'dataProveedor' => $ordenCompra->proveedor->nit . '- ' . $ordenCompra->proveedor->razonSocial,
                'encontrada' => true,
            ];
        } else {

            // La orden de compra no fue encontrada, devolver un mensaje de error en formato JSON
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'error' => 'La orden de compra no fue encontrada.',
                'encontrada' => false
            ];
        }
    }

    public function actionSchedule($idagenda)
    {
        // $model = new Agendaentregamercancia();
        $model = $this->findModel($id);

        /*$model->fechaContacto = date('Y-m-d h:i');

        $modelagenda = Agendapresupuesto::findOne(['id' => $idagenda]);

        $model->idAgenda = $idagenda;*/

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $id = null;
                $mensajeError = 'Error Actualizando Registro';

                if ($model->validate()){

                    $modelestado = Estadoagenda::findOne(['codigo' => 0]);

                    $modelagendaoc = new Agendaentregamercancia();
                    $modelagendaoc->idAgenda = $idagenda;
                    $modelagendaoc->idOrdenCompra = $model->idOrdenCompra;
                    $modelagendaoc->idEstado = $modelestado->id;

                    if ($modelagendaoc->ordenCompra->totalCantidadPendiente > 0){
                        $id = $modelagendaoc->save();
                    }else{
                        $mensajeError = 'Error Actualizando Registro. Cantidad Debe Ser Mayor a Cero';
                    }                   
                }

                if ($id != null){
                    Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
                }else{
                    Yii::$app->session->setFlash( 'error', $mensajeError);
                }

                return $this->redirect(['index', 'id' => $model->idAgenda]);
            }
        } 

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('create_orden_compra', [
                'model' => $model,
            ]);
        }  
    }


}
