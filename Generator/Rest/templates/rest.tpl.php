<?="<?php\n"?>
<?php

$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";

$fields = Generator_Db::describeTable($dbtable);
$primaryKey = $fields->getPrimaryKey();
$fields = $this->_prepareFields($fields);

$fso = array();
foreach ($fields as $field) {
    $fieldName = str_replace('FileSize', '', $field->getName());
    if ($field->getName() === $fieldName . 'FileSize') {
        $fso[] = $fieldName;
    }
}

$docFieldNames = array();
foreach ($fields as $field) {
    $fieldName = str_replace('FileSize', '', $field->getName());
    $docFieldNames[] = "     *     '" . $fieldName ."': ''";
}

echo "/**\n";
echo " * $tableName\n";
echo " */\n\n";
?>
use <?=$namespace?>Model as Models;
use <?=$namespace?>Mapper\Sql as Mappers;

class Rest_<?=$tableName?>Controller extends Iron_Controller_Rest_BaseController
{

    protected $_cache;
    protected $_limitPage = 10;

    public function init()
    {

        parent::init();

        $front = array();
        $back = array();
        $this->_cache = new Iron\Cache\Backend\Mapper($front, $back);

    }

    /**
     * @ApiDescription(section="<?=$tableName?>", description="GET information about all <?=$tableName?>")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/rest/<?=$uri;?>/")
     * @ApiParams(name="page", type="int", nullable=true, description="", sample="")
     * @ApiParams(name="order", type="string", nullable=true, description="", sample="")
     * @ApiParams(name="search", type="json_encode", nullable=true, description="", sample="")
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="[{
<?php
echo implode(", \n", $docFieldNames) . "\n";
?>
     * },{
<?php
echo implode(", \n", $docFieldNames) . "\n";
?>
     * }]")
     */
    public function indexAction()
    {

        $currentEtag = false;
        $ifNoneMatch = $this->getRequest()->getHeader('If-None-Match', false);

        $page = $this->getRequest()->getParam('page', 0);
        $orderParam = $this->getRequest()->getParam('order', false);
        $searchParams = $this->getRequest()->getParam('search', false);

        $fields = $this->getRequest()->getParam('fields', array());
        if (!empty($fields)) {
            $fields = explode(',', $fields);
        } else {
            $fields = array(
<?php
foreach ($fields as $field) :
$fieldName = $field->getName();
$name = '';
if (strpos($fieldName, 'FileSize') == false && strpos($fieldName, 'MimeType') == false && strpos($fieldName, 'BaseName') == false) {

    if (strpos($fieldName, '_')) {
        $dataName = explode('_', $fieldName);
        $name = array_shift($dataName);
        foreach ($dataName as $dataNamePart) {
            $name .= ucfirst($dataNamePart);
        }
    } else {
        $name = $fieldName;
    }
if (!empty($name)) {
    echo "                '" . $name . "',\n";
}

} elseif ($field->getComment() === '[FSO]') {
    $name = str_replace('FileSize', '', $fieldName) . 'Url';
    echo "                //'" . $name . ":@profile', Cambia @profile por el profile del fso.ini\n";
}
endforeach;?>
            );
        }

        $order = $this->_prepareOrder($orderParam);
        $where = $this->_prepareWhere($searchParams);

        $limit = $this->_request->getParam("limit", $this->_limitPage);
        if ($limit > 250) {
            Throw new \Exception("limit argument cannot be larger than 250", 416);
        }

        $offset = $this->_prepareOffset(
            array(
                'page' => $page,
                'limit' => $limit
            )
        );

        $etag = $this->_cache->getEtagVersions('<?=$tableName?>');

        $hashEtag = md5(
            serialize(
                array($fields, $where, $order, $this->_limitPage, $offset)
            )
        );
        $currentEtag = $etag . $hashEtag;

        if ($etag !== false) {
            if ($currentEtag === $ifNoneMatch) {
                $this->status->setCode(304);
                return;
            }
        }

        $mapper = new Mappers\<?=$tableName?>();

        $items = $mapper->fetchList(
            $where,
            $order,
            $limit,
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
            $data[] = $item->toArray($fields);
        }

        $this->addViewData($data);
        $this->status->setCode(200);

        if ($currentEtag !== false) {
            $this->_sendEtag($currentEtag);
        }
    }

    /**
     * @ApiDescription(section="<?=$tableName?>", description="Get information about <?=$tableName?>")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/rest/<?=$uri;?>/{<?=$primaryKey->getName()?>}")
<?php echo '     * @ApiParams(name="' . $primaryKey->getName() . '", type="' . $primaryKey->getType() . '", nullable=' . ($primaryKey->isNullable() ? 'true' : 'false') . ', description="", sample="")' . "\n";?>
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
<?php
echo implode(", \n", $docFieldNames) . "\n";
?>
     * }")
     */
    public function getAction()
    {
        $currentEtag = false;
        $primaryKey = $this->getRequest()->getParam('<?=$primaryKey->getName()?>', false);
        if ($primaryKey === false) {
            $this->status->setCode(404);
            return;
        }

        $fields = $this->getRequest()->getParam('fields', array());
        if (!empty($fields)) {
            $fields = explode(',', $fields);
        } else {
            $fields = array(
<?php
foreach ($fields as $field) :
$fieldName = $field->getName();
$name = '';
if (strpos($fieldName, 'FileSize') == false && strpos($fieldName, 'MimeType') == false && strpos($fieldName, 'BaseName') == false) {

    if (strpos($fieldName, '_')) {
        $dataName = explode('_', $fieldName);
        $name = array_shift($dataName);
        foreach ($dataName as $dataNamePart) {
            $name .= ucfirst($dataNamePart);
        }
    } else {
        $name = $fieldName;
    }
if (!empty($name)) {
    echo "                '" . $name . "',\n";
}

} elseif ($field->getComment() === '[FSO]') {
    $name = str_replace('FileSize', '', $fieldName) . 'Url';
    echo "                //'" . $name . ":@profile', Cambia @profile por el profile del fso.ini\n";
}
endforeach;?>
            );
        }

        $etag = $this->_cache->getEtagVersions('<?=$tableName?>');
        $hashEtag = md5(
            serialize(
                array($fields)
            )
        );
        $currentEtag = $etag . $primaryKey . $hashEtag;

        if (!empty($etag)) {
            $ifNoneMatch = $this->getRequest()->getHeader('If-None-Match', false);
            if ($currentEtag === $ifNoneMatch) {
                $this->status->setCode(304);
                return;
            }
        }

        $mapper = new Mappers\<?=$tableName?>();
        $model = $mapper->find($primaryKey);

        if (empty($model)) {
            $this->status->setCode(404);
            return;
        }

        $this->status->setCode(200);
        $this->addViewData($model->toArray($fields));

        if ($currentEtag !== false) {
            $this->_sendEtag($currentEtag);
        }

    }

    /**
     * @ApiDescription(section="<?=$tableName?>", description="Create's a new <?=$tableName?>")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/rest/<?=$uri;?>/")
<?php
foreach ($fields as $field) {
    if ($field->getName() != $primaryKey->getName()) {
        $fieldName = str_replace('FileSize', '', $field->getName());
        echo '     * @ApiParams(name="' . $fieldName . '", nullable=' . ($field->isNullable() ? 'true' : 'false') . ', type="' . $field->getType() . '", sample="", description="'.$field->getComment().'")' . "\n";
    }
}
?>
     * @ApiReturnHeaders(sample="HTTP 201")
     * @ApiReturnHeaders(sample="Location: /rest/<?=strtolower($tableName)?>/{<?=$primaryKey->getName()?>}")
     * @ApiReturn(type="object", sample="{}")
     */
    public function postAction()
    {

        $params = $this->getRequest()->getParams();

        $model = new Models\<?=$tableName?>();

        try {
<?php
            if (!empty($fso)) {
                foreach ($fso as $file) {
?>
            if (!empty($_FILES['<?=$file?>'])) {
                $<?=$file?> = $_FILES['<?=$file?>'];
                $model->put<?=ucfirst($file)?>($<?=$file?>['tmp_name'], $<?=$file?>['name']);
            }

<?php } } ?>
            $model->populateFromArray($params);
            $model->save();

            $this->status->setCode(201);

            $location = $this->location() . '/' . $model->getPrimaryKey();

            $this->getResponse()->setHeader('Location', $location);

        } catch (\Exception $e) {
            $this->addViewData(
                array('error' => $e->getMessage())
            );
            $this->status->setCode(404);
        }

    }

    /**
     * @ApiDescription(section="<?=$tableName?>", description="Table <?=$tableName?>")
     * @ApiMethod(type="put")
     * @ApiRoute(name="/rest/<?=$uri;?>/")
<?php
foreach ($fields as $field) {
    $fieldName = str_replace('FileSize', '', $field->getName());
    echo '     * @ApiParams(name="' . $fieldName . '", nullable=' . ($field->isNullable() ? 'true' : 'false') . ', type="' . $field->getType() . '", sample="", description="'.$field->getComment().'")' . "\n";
}
?>
     * @ApiReturnHeaders(sample="HTTP 200")
     * @ApiReturn(type="object", sample="{}")
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
<?php
            if (!empty($fso)) {
                foreach ($fso as $file) {
?>
            if (!empty($_FILES['<?=$file?>'])) {
                $<?=$file?> = $_FILES['<?=$file?>'];
                $model->put<?=ucfirst($file)?>($<?=$file?>['tmp_name'], $<?=$file?>['name']);
            }

<?php } } ?>
            $model->populateFromArray($params);
            $model->save();

            $this->addViewData($model->toArray());
            $this->status->setCode(200);
        } catch (\Exception $e) {
            $this->addViewData(
                array('error' => $e->getMessage())
            );
            $this->status->setCode(404);
        }

    }

    /**
     * @ApiDescription(section="<?=$tableName?>", description="Table <?=$tableName?>")
     * @ApiMethod(type="delete")
     * @ApiRoute(name="/rest/<?=$uri;?>/")
<?php echo '     * @ApiParams(name="' . $primaryKey->getName() . '", nullable=' . ($primaryKey->isNullable() ? 'true' : 'false') . ', type="' . $primaryKey->getType() . '", sample="", description="")' . "\n";?>
     * @ApiReturnHeaders(sample="HTTP 204")
     * @ApiReturn(type="object", sample="{}")
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
            $this->addViewData(
                array('error' => $e->getMessage())
            );
            $this->status->setCode(404);
        }

    }


    public function optionsAction()
    {

        $this->view->GET = array(
            'description' => '',
            'params' => array(
                '<?=$primaryKey->getName()?>' => array(
                    'type' => "<?=$primaryKey->getType()?>",
                    'required' => true,
                    'comment' => '[pk]'
                )
            )
        );

        $this->view->POST = array(
            'description' => '',
            'params' => array(
<?php
foreach ($fields as $field) {
    if ($this->_ignoreField($field)) {
        continue;
    }
    if ($primaryKey->getName() !== $field->getName()) {
        $fieldName = str_replace('FileSize', '', $field->getName());
        echo "                '" . $fieldName . "' => array(\n";
        echo "                    'type' => \"" . $field->getType() . "\",\n";
        echo "                    'required' => " . ($field->isNullable() ? 'false' : 'true') . ",\n";
        echo "                    'comment' => '" . $field->getComment() . "',\n";
        echo "                ),\n";
    }
}
?>
            )
        );

        $this->view->PUT = array(
            'description' => '',
            'params' => array(
<?php
foreach ($fields as $field) {
    if ($this->_ignoreField($field)) {
        continue;
    }

    $fieldName = str_replace('FileSize', '', $field->getName());
    echo "                '" . $fieldName . "' => array(\n";
    echo "                    'type' => \"" . $field->getType() . "\",\n";
    echo "                    'required' => " . ($field->isNullable() ? 'false' : 'true') . ",\n";
    echo "                    'comment' => '" . ($primaryKey->getName() === $field->getName() ? '[pk]' : $field->getComment()) . "',\n";
    echo "                ),\n";
}
?>
            )
        );
        $this->view->DELETE = array(
            'description' => '',
            'params' => array(
                '<?=$primaryKey->getName()?>' => array(
                    'type' => "<?=$primaryKey->getType()?>",
                    'required' => true
                )
            )
        );

        $this->status->setCode(200);

    }
}