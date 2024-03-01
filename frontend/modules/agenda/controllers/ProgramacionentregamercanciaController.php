<?php

namespace frontend\modules\agenda\controllers;

use Yii;
use frontend\models\Programacionentregamercancia;
use frontend\models\search\ProgramacionentregamercanciaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use frontend\models\search\AgendaentregamercanciaSearch;
use frontend\models\Estadoagenda;
use frontend\models\Estadoconteo;
use frontend\models\Estadoprogramacion;
use frontend\models\Agendaentregamercancia;
use frontend\models\Conteoentregamercancia;

/**
 * ProgramacionentregamercanciaController implements the CRUD actions for Programacionentregamercancia model.
 */
class ProgramacionentregamercanciaController extends Controller
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
     * Lists all Agendaentregamercancia models.
     *
     * @return string
     */
    public function actionIndexagenda($menu)
    {
        $idsEncontrados = [];
        $programa = 'index_agenda';
        $lista_codigo = [0,1];
        if ($menu == 'recepcion'){
            $programa = 'index_recepcion';
            $lista_codigo = [1,2,3];
        }

        foreach ($lista_codigo as $codigo) {
            // Buscar el modelo Estado por el código

            if ($menu == 'recepcion'){
                $estado = Estadoagenda::findOne(['codigo' => $codigo]);
            }else{
                $estado = Estadoconteo::findOne(['codigo' => $codigo]);                
            }
            
            // Si se encuentra el estado, se agrega su ID al array
            if ($estado !== null) {
                $idsEncontrados[] = $estado->id;
            }
        }

        //var_dump($idsEncontrados);die("hola");

        $searchModel = new AgendaentregamercanciaSearch();
        $dataProvider = $searchModel->searchxEstado($this->request->queryParams, $idsEncontrados, $menu);

        return $this->render($programa, [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'menu' => $menu
        ]);
    }

    /**
     * Lists all Programacionentregamercancia models.
     *
     * @return string
     */
    public function actionIndex($id)
    {
        $modelagendaentrega = Agendaentregamercancia::findOne(['id' => $id]);

        $searchModel = new ProgramacionentregamercanciaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelagendaentrega' => $modelagendaentrega
        ]);
    }

    /**
     * Displays a single Programacionentregamercancia model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView()
    {
        $searchModel = new ProgramacionentregamercanciaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index_programacion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }

    /**
     * Creates a new Programacionentregamercancia model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($id)
    {
        $model = new Programacionentregamercancia();

        $model->idAgendaEntregaMercancia = $id;
        $model->idEstado = 1;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if ($model->validate()){

                    $model->idEmpleadoLogistica = $model->userConteo->idEmpleadoLogistica;
                    
                    if ($model->save()){
                        $modelagendaentrega = Agendaentregamercancia::findOne(['id' => $id]);

                        $numero = $modelagendaentrega->numeroPersonasProgramadas;
                        if ($numero){
                            $codigo = 1;
                            
                            $modelestado = Estadoconteo::findOne(['codigo' => $codigo]);

                            $modelagendaentrega->idEstadoConteo = $modelestado->id;
                            $modelagendaentrega->save();
                        }
                    }
                }

                return $this->redirect(['index', 'id' => $model->idAgendaEntregaMercancia]);
            }
        } else {
            $model->loadDefaultValues();
        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }  
    }

    /**
     * Updates an existing Programacionentregamercancia model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionReceive($idagendaentrega)
    {
        /*$codigo = 1;
        $modelestado = Estadoagenda::findOne(['codigo' => $codigo]);

        $model = Agendaentregamercancia::findOne(['id' => $idagendaentrega ]);

        if ($model->idEstado != $modelestado->id){
            Yii::$app->session->setFlash( 'error', 'Error Estado de la Orden No Permite Asignar NO Cumplio');
            return $this->redirect(['indexagenda', 'menu' => 'recepcion']);
        }*/

        $model = Agendaentregamercancia::findOne(['id' => $idagendaentrega]);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $codigo = 2;
                $modelestado = Estadoagenda::findOne(['codigo' => $codigo]);

                $codigo = 0;
                $modelestadoconteo = Estadoconteo::findOne(['codigo' => $codigo]);

                $model->idEstado = $modelestado->id;
                $model->idEstadoConteo = $modelestadoconteo->id;
                $model->save();

                return $this->redirect(['indexagenda', 'menu' => 'recepcion']);
            }
        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('receive', [
                'model' => $model,
            ]);
        }  
    }

    /**
     * Deletes an existing Programacionentregamercancia model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $idAgendaEntregaMercancia = $model->idAgendaEntregaMercancia;

        $model->delete();

        $modelagendaentrega = Agendaentregamercancia::findOne(['id' => $idAgendaEntregaMercancia ]);

        $numero = $modelagendaentrega->numeroPersonasProgramadas;
        if ($numero){
            $codigo = 1;
            if ($numero > 0){
                $codigo = 4;
            }
            $modelestado = Estadoagenda::findOne(['codigo' => $codigo]);

            $modelagendaentrega->idEstado = $modelestado->id;
            $modelagendaentrega->save();
        }


        return $this->redirect(['index', 'id' => $idAgendaEntregaMercancia]);
    }

    public function actionNocumplio($idagendaentrega)
    {
        $codigo = 1;
        $modelestado = Estadoagenda::findOne(['codigo' => $codigo]);

        $model = Agendaentregamercancia::findOne(['id' => $idagendaentrega ]);

        if ($model->idEstado != $modelestado->id){
            Yii::$app->session->setFlash( 'error', 'Error Estado de la Orden No Permite Asignar NO Cumplio');
            return $this->redirect(['indexagenda', 'menu' => 'recepcion']);
        }

        $codigo = 3;
        $modelestado = Estadoagenda::findOne(['codigo' => $codigo]);

        $model->idEstado = $modelestado->id;
        if ($model->save()){
            Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
        }else{
            Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro');
        }

        return $this->redirect(['indexagenda', 'menu' => 'recepcion']);
    }


    public function actionConteo($id)
    {
        $model = $this->findModel($id);
        $idAgendaEntregaMercancia = $model->idAgendaEntregaMercancia;
        
        $codigo = 4;
        $modelestado = Estadoprogramacion::findOne(['codigo' => $codigo]);

        $model->idEstado = $modelestado->id;

        if ($model->save()){

            $codigo = 5;
            $modelagendaentrega = Agendaentregamercancia::actualizarEstado ($idAgendaEntregaMercancia, $codigo);

            $idordencompra = $modelagendaentrega->idOrdenCompra;
            $modelconteo = Conteoentregamercancia::grabarItemParaConteo ($idordencompra, $idAgendaEntregaMercancia);

            Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
        }else{
            Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro');
        }

        return $this->redirect(['view']);
    }

    /**
     * Finds the Programacionentregamercancia model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Programacionentregamercancia the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Programacionentregamercancia::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
