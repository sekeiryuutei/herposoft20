<?php

namespace frontend\modules\programacion\controllers;

use Yii;
use frontend\models\Conteoentregamercancia;
use frontend\models\search\ConteoentregamercanciaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use frontend\models\Programacionentregamercancia;
use frontend\models\Agendaentregamercancia;
use frontend\models\search\OrdendecompradetalleSearch;

/**
 * ConteoentregamercanciaController implements the CRUD actions for Conteoentregamercancia model.
 */
class ConteoentregamercanciaController extends Controller
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
     * Lists all Conteoentregamercancia models.
     *
     * @return string
     */
    public function actionIndex($idprogramacion)
    {
        $modelprogramacion = Programacionentregamercancia::findOne(['id' => $idprogramacion]);

        $idagenda = $modelprogramacion->agendaEntregaMercancia->id;
        $modelagendaentrega = Agendaentregamercancia::findOne(['id' => $idagenda]);

        $searchModel = new ConteoentregamercanciaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idprogramacion);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelagendaentrega' => $modelagendaentrega,
            'modelprogramacion' => $modelprogramacion
        ]);
    }

    public function actionIndexall()
    {

        $searchModel = new ConteoentregamercanciaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index_all', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Conteoentregamercancia model.
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
     * Creates a new Conteoentregamercancia model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($idprogramacion)
    {
        $modelprogramacion = Programacionentregamercancia::find(['id' => $idprogramacion])->one();

        $idcategoria = $modelprogramacion->agendaEntregaMercancia->idCategoria;
        $categoria = $modelprogramacion->agendaEntregaMercancia->categoria->nombre;
        $idordencompra = $modelprogramacion->agendaEntregaMercancia->idOrdenCompra;

        // var_dump($idordencompra);die($idprogramacion);

        $searchModelDetalleOC = new OrdendecompradetalleSearch();
        $dataProviderDetalleOC = $searchModelDetalleOC->searchDetalleItem($idordencompra, $idcategoria);

        $model = new Conteoentregamercancia();
        $model->idProgramacionEntregaMercancia = $idprogramacion;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        /*if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                if ($model->validate()){
                    $model->save();

                    return $this->redirect(['index', 'idprogramacion' => $idprogramacion]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }*/

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('index_detalleoc', [
                'idprogramacion' => $idprogramacion,
                'categoria' => $categoria,
                'dataProviderDetalleOC' => $dataProviderDetalleOC,
            ]);
        }
    }

    public function actionSelect ($idprogramacion, $iditem){

        $model = new Conteoentregamercancia();
        $model->idProgramacionEntregaMercancia = $idprogramacion;
        $model->idItem = $iditem;
        $model->unidadesConteo = 0;

        $model->save();

        return $this->redirect(['index', 'idprogramacion' => $idprogramacion]);
    }

    /**
     * Updates an existing Conteoentregamercancia model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Conteoentregamercancia model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $idprogramacion = $model->idProgramacionEntregaMercancia;

        $model->delete();

        return $this->redirect(['index', 'idprogramacion' => $idprogramacion]);
    }

    /**
     * Finds the Conteoentregamercancia model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Conteoentregamercancia the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Conteoentregamercancia::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
