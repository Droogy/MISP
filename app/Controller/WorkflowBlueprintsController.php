<?php
App::uses('AppController', 'Controller');

class WorkflowBlueprintsController extends AppController
{
    public $components = array(
        'RequestHandler'
    );

    public function update($force = false)
    {
        $this->request->allowMethod(['post', 'put']);
        $this->WorkflowBlueprint->update($force);
        $message = __('Default workflow blueprints updated');
        if ($this->_isRest()) {
            return $this->RestResponse->saveSuccessResponse('WorkflowBlueprint', 'update', false, $this->response->type(), $message);
        } else {
            $this->Flash->success($message);
            $this->redirect(array('controller' => 'workflowBlueprints', 'action' => 'index'));
        }
    }

    public function index()
    {
        $params = [
            'filters' => ['name', 'uuid', 'timestamp'],
            'quickFilters' => ['name', 'uuid'],
        ];
        $this->CRUD->index($params);
        if ($this->IndexFilter->isRest()) {
            return $this->restResponsePayload;
        }
        $this->set('menuData', ['menuList' => 'workflowBlueprints', 'menuItem' => 'index']);
    }

    public function add($fromEditor = false)
    {
        $params = [
            'beforeSave' => function(array $blueprint) {
                $blueprint['WorkflowBlueprint']['default'] = false;
                return $blueprint;
            },
        ];
        $this->CRUD->add($params);
        if ($this->IndexFilter->isRest()) {
            return $this->restResponsePayload;
        }
        $this->set('fromEditor', !empty($fromEditor));
        $this->set('menuData', ['menuList' => 'workflowBlueprints', 'menuItem' => 'add']);
    }

    public function edit($id)
    {
        $params = [
            'beforeSave' => function (array $blueprint) {
                $blueprint['WorkflowBlueprint']['default'] = false;
                return $blueprint;
            },
        ];
        $this->CRUD->edit($id, $params);
        if ($this->IndexFilter->isRest()) {
            return $this->restResponsePayload;
        }
        $this->request->data['WorkflowBlueprint']['data'] = JsonTool::encode($this->data['WorkflowBlueprint']['data']);
        $this->set('menuData', ['menuList' => 'workflowBlueprints', 'menuItem' => 'edit']);
        $this->set('id', $id);
        $this->render('add');
    }

    public function delete($id)
    {
        $params = [
        ];
        $this->CRUD->delete($id, $params);
        if ($this->IndexFilter->isRest()) {
            return $this->restResponsePayload;
        }
        $this->set('menuData', ['menuList' => 'workflowBlueprints', 'menuItem' => 'delete']);
    }

    public function view($id)
    {
        $filters = $this->IndexFilter->harvestParameters(['format']);
        if (!empty($filters['format'])) {
            if ($filters['format'] == 'dot') {
                $dot = $this->WorkflowBlueprint->getDotNotation($id);
                return $this->RestResponse->viewData($dot, $this->response->type());
            } else if ($filters['format'] == 'mermaid') {
                $mermaid = $this->WorkflowBlueprint->getMermaid($id);
                return $this->RestResponse->viewData($mermaid, $this->response->type());
            }
        }
        $this->CRUD->view($id, [
        ]);
        if ($this->IndexFilter->isRest()) {
            return $this->restResponsePayload;
        }
        $this->set('id', $id);
        $this->set('menuData', ['menuList' => 'workflowBlueprints', 'menuItem' => 'view']);
    }

    public function import()
    {
        if ($this->request->is('post') || $this->request->is('put')) {
            $workflowBlueprintData = JsonTool::decode($this->request->data['WorkflowBlueprint']['data']);
            if ($workflowBlueprintData === null) {
                throw new MethodNotAllowedException(__('Error while decoding JSON'));
            }
            $this->request->data['WorkflowBlueprint']['data'] = JsonTool::encode($workflowBlueprintData);
            $this->add();
        }
    }

    public function export($id)
    {
        $workflowBlueprint = $this->WorkflowBlueprint->find('first', [
            'conditions' => [
                'id' => $id,
            ]
        ]);
        $content = JsonTool::encode($workflowBlueprint, JSON_PRETTY_PRINT);
        $this->response->body($content);
        $this->response->type('json');
        $this->response->download(sprintf('workflowblueprint_%s_%s.json', $workflowBlueprint['WorkflowBlueprint']['name'], time()));
        return $this->response;
    }
}
