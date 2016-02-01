<?php

/* @var $this yii\web\View */
use yii\bootstrap\ActiveForm;
use yii\helpers\Json;

$this->title = 'Spaceships test work';
?>
<h1>Api tester</h1>
<div class="row">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <?php $form = ActiveForm::begin([
                            'id' => 'url-form',
                            'action' => null,
                            'layout' => 'horizontal',
                            'options' => [
                                'name' => 'urlForm',
                            ],
                            'fieldConfig' => [
                                'horizontalCssClasses' => [
                                    'label' => 'col-sm-2',
                                    'offset' => 'col-sm-offset-2',
                                    'wrapper' => 'col-sm-10',
                                ],
                            ],
                        ]); ?>

                            <?= $form->field($model, 'url', [
                                'inputTemplate' => '<div class="input-group">' . "\n"
                                    . '<span class="input-group-addon">http://</span>' . "\n"
                                    . '{input}</div>'
                            ]); ?>
                            <?= $form->field($model, 'method', [
                            ])->inline()->radioList([
                                'get' => 'Get',
                                'post' => 'Post'
                            ], [
                                'itemOptions' => [
                                    'ng-model' => 'app.formData.method',
                                    'labelOptions' => [
                                        'class' => 'radio-inline'
                                    ],
                                ]
                            ]); ?>
                          <div class="form-group">
                            <label class="col-md-2 control-label">Parameters</label>
                            <div class="col-md-10">
                              <div class="form-inline" id="param-rows-container">
                                  <script type="text/template" id="param-row-template">
                                  <div class="param-row clearfix">
                                      <?= 
                                        $form->field($model, 'param_names[]', [
                                            'template' => '{input}',
                                            'inputOptions' => [
                                                'ng-model' => 'param.name'
                                            ]
                                      ]);?>
                                      <?= 
                                        $form->field($model, 'param_values[]', [
                                            'template' => '{beginLabel}={endLabel}{input}',
                                            'inputOptions' => [
                                                'ng-model' => 'param.value'
                                            ]
                                      ]);?>
                                      <button type="button" class="btn btn-danger btn-xs remove-param-btn" title="remove">X</button>
                                  </div>
                                  </script>
                              </div>
                                <button id="add-param-btn" class="btn btn-success" type="button">+ Add parameter</button>
                            </div>
                          </div>
                          <div class="form-group">
                              <div class="col-md-10 col-md-offset-2">
                                  <input type="submit" name="submitBtn" value="Send" class="btn btn-default"/>
                              </div>
                          </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel panel-default" id="request-wrapper">
                    <div class="panel-heading">
                        <h2 class="panel-title">Your Requests</h2>
                    </div>
                    <div class="panel-body">
                        <div class="loading">loading...</div>
                    </div>
                    <table class="table" id="request-history">
                        <tbody>
                            <tr id="no-history"><td>no requests yet</td></tr>
                        </tbody>
                    </table>
                    <script type="text/template" id="history-row-template">
                        <tr class="history-tooltip">
                            <td>%date%</td>
                            <td>%method%</td>
                            <td title="%url-title%">%url%</td>
                            <td>%params%</td>
                            <td>
                                <button type="button" title="Repeat" class="btn btn-success btn-sm repeat-request-btn">
                                    <i class="glyphicon glyphicon-refresh"></i>
                                </button>
                            </td>
                        </tr>
                    </script>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default" id="response-wrapper">
            <div class="panel-heading">
                <h2 class="panel-title">Response</h2>
            </div>
            <div class="panel-body">
                <div class="loading">loading...</div>
                <p id="no-response">No response yet</p>
                <p id="status-display">Status: <span></span></p>
                <p id="error-display" class="bg-danger">Error: <span></span></p>
                <pre id="response-container"></pre>
            </div>
        </div>
    </div>
</div>
