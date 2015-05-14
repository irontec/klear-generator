<?="<?php\n"?>
<?php
$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";

$fields = Generator_Db::describeTable($tableName);

$primaryKey = $fields->getPrimaryKey();

$enumFields = array();
$fsoFields = array();

echo "/**\n";
echo " * $tableName\n";
echo " */\n\n";
?>
use <?=$namespace?>Model as Models;
use <?=$namespace?>Mapper\Sql as Mappers;

class Rest_<?=$tableName?>Controller extends Iron_Controller_Rest_BaseController
{

    protected $_limitPage = 10;

    public function optionsAction()
    {

        $this->view->GET = array(
            'description' => '',
            'params' => array(
                '<?=$primaryKey->getName()?>' => array(
                    'type' => '<?=$primaryKey->getType()?>',
                    'required' => true
                )
            )
        );
        $this->view->POST = array(
            'description' => '',
            'params' => array(
<?php
foreach ($fields as $field) {
    echo "                '" . $field->getName() . "' => array(\n";
    echo "                    'type' => '" . $field->getType() . "',\n";
    echo "                    'required' => " . ($field->isNullable() ? 'false' : 'true') . "\n";
    echo "                ),\n";
}
?>
            )
        );
        $this->view->PUT = array(
            'description' => '',
            'params' => array(
<?php
foreach ($fields as $field) {
    echo "                '" . $field->getName() . "' => array(\n";
    echo "                    'type' => '" . $field->getType() . "',\n";
    echo "                    'required' => " . ($field->isNullable() ? 'false' : 'true') . "\n";
    echo "                ),\n";
}
?>
            )
        );
        $this->view->DELETE = array(
            'description' => '',
            'params' => array(
                '<?=$primaryKey->getName()?>' => array(
                    'type' => '<?=$primaryKey->getType()?>',
                    'required' => true
                )
            )
        );

        $this->status->setCode(200);

    }

    public function indexAction()
    {

        $page = $this->getRequest()->getHeader('page', 0);
        $orderParam = $this->getRequest()->getParam('order', false);
        $searchParams = $this->getRequest()->getParam('search', false);

        $order = $this->_prepareOrder($orderParam);
        $where = $this->_prepareWhere($searchParams);

        $offset = $this->_prepareOffset(
            array(
                'page' => $page,
                'limit' => $this->_limitPage
            )
        );

        $mapper = new Mappers\<?=$tableName?>();

        $items = $mapper->fetchList(
            $where,
            $order,
            $this->_limitPage,
            $offset
        );

        $countItems = $mapper->countByQuery($where);

        $this->getResponse()->setHeader('totalItems', $countItems);

        if (empty($items)) {
            $this->status->setCode(204);
            return;
        }

        $data = array();

        foreach ($items as $item) {
            $data[] = $item->toArray();
        }

        $this->addViewData($data);
        $this->status->setCode(200);

    }

    /**
     * [disabled]ApiDescription(section="<?=$tableName?>", description="Get information about <?=$tableName?>")
     * [disabled]ApiMethod(type="get")
     * [disabled]ApiRoute(name="/rest/<?=strtolower($tableName)?>/{<?=$primaryKey->getName()?>}")
<?php echo '     * [disabled]ApiParams(name="' . $primaryKey->getName() . '", type="' . $primaryKey->getType() . '", nullable=' . ($primaryKey->isNullable() ? 'true' : 'false') . ', description="", sample="")' . "\n";?>
     * [disabled]ApiReturnHeaders(sample="HTTP 200 OK")
     * [disabled]ApiReturn(type="object", sample="{
<?php
foreach ($fields as $field) {
echo "     *     '" . $field->getName() ."': '', \n";
}
?>
     * }")
     */
    public function getAction()
    {

        $primaryKey = $this->getRequest()->getParam('<?=$primaryKey->getName()?>', false);

        if ($primaryKey === false) {
            $this->status->setCode(404);
            return;
        }

        $mapper = new Mappers\<?=$tableName?>();
        $model = $mapper->find($primaryKey);

        if (empty($model)) {
            $this->status->setCode(404);
            return;
        }

        $this->status->setCode(200);
        $this->addViewData($model->toArray());

    }

    /**
     * [disabled]ApiDescription(section="<?=$tableName?>", description="Create's a new <?=$tableName?>")
     * [disabled]ApiMethod(type="post")
     * [disabled]ApiRoute(name="/rest/<?=strtolower($tableName)?>/")
<?php
foreach ($fields as $field) {
    if ($field->getName() != $primaryKey->getName()) {
        echo '     * [disabled]ApiParams(name="' . $field->getName() . '", nullable=' . ($field->isNullable() ? 'true' : 'false') . ', type="' . $field->getType() . '", sample="", description="'.$field->getComment().'")' . "\n";
    }
}
?>
     * [disabled]ApiReturnHeaders(sample="HTTP 201")
     * [disabled]ApiReturnHeaders(sample="Location: /rest/<?=strtolower($tableName)?>/{<?=$primaryKey->getName()?>}")
     * [disabled]ApiReturn(type="object", sample="{}")
     */
    public function postAction()
    {

        $params = $this->getRequest()->getParams();

        $model = new Models\<?=$tableName?>();

        try {
            $model->populateFromArray($params);
            $model->save();
            $this->status->setCode(201);

            $location = $this->location() . '/' . $model->getPrimaryKey();

            $this->getResponse()->setHeader('Location', $location);

        } catch (\Exception $e) {
            $this->addViewData($e->getMessage());
            $this->status->setCode(404);
        }

    }

    /**
     * [disabled]ApiDescription(section="<?=$tableName?>", description="Table <?=$tableName?>")
     * [disabled]ApiMethod(type="put")
     * [disabled]ApiRoute(name="/rest/<?=strtolower($tableName)?>/")
<?php
foreach ($fields as $field) {
    echo '     * [disabled]ApiParams(name="' . $field->getName() . '", nullable=' . ($field->isNullable() ? 'true' : 'false') . ', type="' . $field->getType() . '", sample="", description="'.$field->getComment().'")' . "\n";
}
?>
     * [disabled]ApiReturnHeaders(sample="HTTP 200")
     * [disabled]ApiReturn(type="object", sample="{}")
     */
    public function putAction()
    {

        $primaryKey = $this->getRequest()->getParam('<?=$primaryKey->getName()?>', false);

        if ($primaryKey === false) {
            $this->status->setCode(400);
            return;
        }

        $params = $this->getRequest()->getParams();

        $mapper = new Mappers\<?=$tableName?>();
        $model = $mapper->find($primaryKey);

        if (empty($model)) {
            $this->status->setCode(404);
            return;
        }

        try {
            $model->populateFromArray($params);
            $model->save();
            $this->addViewData($model->toArray());
            $this->status->setCode(200);
        } catch (\Exception $e) {
            $this->addViewData($e->getMessage());
            $this->status->setCode(404);
        }

    }

    /**
     * [disabled]ApiDescription(section="<?=$tableName?>", description="Table <?=$tableName?>")
     * [disabled]ApiMethod(type="delete")
     * [disabled]ApiRoute(name="/rest/<?=strtolower($tableName)?>/")
<?php echo '     * [disabled]ApiParams(name="' . $primaryKey->getName() . '", nullable=' . ($primaryKey->isNullable() ? 'true' : 'false') . ', type="' . $primaryKey->getType() . '", sample="", description="")' . "\n";?>
     * [disabled]ApiReturnHeaders(sample="HTTP 204")
     * [disabled]ApiReturn(type="object", sample="{}")
     */
    public function deleteAction()
    {

        $primaryKey = $this->getRequest()->getParam('id', false);

        if ($primaryKey === false) {
            $this->status->setCode(400);
            return;
        }

        $mapper = new Mappers\<?=$tableName?>();
        $model = $mapper->find($primaryKey);

        if (empty($model)) {
            $this->status->setCode(404);
            return;
        }

        try {
            $model->delete();
            $this->status->setCode(204);
        } catch (\Exception $e) {
            $this->addViewData($e->getMessage());
            $this->status->setCode(404);
        }

    }

    /**
     * Offset to pagination
     */
    protected function _prepareOffset($params = array())
    {

        if (isset($params["page"]) && $params["page"] > 0) {
            return ($params["page"] - 1) * $params["limit"];
        }

        return 0;

    }

    /**
     * Order to list
     */
    protected function _prepareOrder($orderParam)
    {

        if ($orderParam === false || trim($orderParam) === '') {
            return '<?=$primaryKey->getName()?> DESC';
        }

        return $orderParam;

    }

    /**
     * Where para busquedas, la variable $search espera un json_encode con los parametros de busqueda.
     */
    protected function _prepareWhere($search)
    {

        if ($search === false || trim($search) === '') {
            return NULL;
        }

        $search = json_decode($search);
        $itemsSearch = array();
        foreach ($search as $key => $val) {
            if ($val != '') {
                $itemsSearch[] = $key . ' = "' . $val . '"';
            }
        }

        if (empty($itemsSearch)) {
            return '';
        }

        $whereSearch = implode(' AND ', $itemsSearch);

        return $whereSearch;

    }

}