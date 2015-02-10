<?="<?php\n"?>
<?php
$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";

$fields = Generator_Db::describeTable($tableName);

$primaryKey = $fields->getPrimaryKey();


$enumFields = array();
$fsoFields = array();

echo "/**\n";
echo " * $tableName\n";
echo " *\n";
echo " */\n\n";
?>
use <?=$namespace?>Model as Models;
use <?=$namespace?>Mapper\Sql as Mappers;

class <?=$apiNamespace?>_<?=$tableName?>Controller extends Zend_Rest_Controller
{

    public function init()
    {

    }

    public function headAction()
    {
        $this->_response->setHttpResponseCode(200);
    }

    public function optionsAction()
    {

        $options = array(
            'GET' => array(

            ),
            'POST' => array(

            ),
            'PUT' => array(

            ),
            'DELETE' => array(
                'description' => '',
                'params' => array(
                    '<?=$primaryKey->getName()?>' => array(
                        'type' => '<?=$primaryKey->getType()?>',
                        'required' => true
                    )
                )
            )
        );

        $this->_response->setHttpResponseCode(200);
        $this->_helper->json($options);

    }

    public function indexAction()
    {

    }

    /**
     * @ApiDescription(section="<?=$tableName?>", description="Table <?=$tableName?>")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/<?=strtolower($apiNamespace)?>/<?=strtolower($tableName)?>/")
<?php echo '     * @ApiParams(name="' . $primaryKey->getName() . '", nullable=' . ($primaryKey->isNullable() ? 'true' : 'false') . ', type="' . $primaryKey->getType() . '", sample="", description="")' . "\n";?>
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *     '<?=strtolower($tableName)?>': [
     *         {
<?php
foreach ($fields as $field) {
echo "     *            '" . $field->getName() ."': '', \n";
}
?>
     *         },
     *     ],
     *     'message': 'OK'
     * }")
     */
    public function getAction()
    {

        $primaryKey = $this->getRequest()->getParam('<?=$primaryKey->getName()?>', false);

        if ($primaryKey !== false) {

            $mapper = new Mappers\<?=$tableName?>();
            $item = $mapper->find($primaryKey);

            $result = array(
                'message' => 'Ok'
            );

            if (empty($item)) {
                $result['<?=strtolower($tableName)?>'] = array();
            } else {
                $result['<?=strtolower($tableName)?>'] = $item->toArray();
            }

            $this->_response->setHttpResponseCode(200);
            $this->_helper->json($result);

        } else {

            $this->_response->setHttpResponseCode(204);
            $this->_helper->json(array());

        }

    }

    /**
     * @ApiDescription(section="<?=$tableName?>", description="Table <?=$tableName?>")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/<?=strtolower($apiNamespace)?>/<?=strtolower($tableName)?>/")
<?php
foreach ($fields as $field) {
    if ($field->getName() != $primaryKey->getName()) {
        echo '     * @ApiParams(name="' . $field->getName() . '", nullable=' . ($field->isNullable() ? 'true' : 'false') . ', type="' . $field->getType() . '", sample="", description="")' . "\n";
    }
}
?>
     * @ApiReturnHeaders(sample="HTTP 201 OK")
     * @ApiReturn(type="object", sample="{
     *     '<?=strtolower($tableName)?>': [
     *         {
<?php
foreach ($fields as $field) {
echo "     *            '" . $field->getName() ."': '', \n";
}
?>
     *         },
     *     ],
     *     'message': 'OK'
     * }")
     */
    public function postAction()
    {
        $this->_response->setHttpResponseCode(201);
        $this->_helper->json(array('action' => 'post'));
    }

    /**
     * @ApiDescription(section="<?=$tableName?>", description="Table <?=$tableName?>")
     * @ApiMethod(type="put")
     * @ApiRoute(name="/<?=strtolower($apiNamespace)?>/<?=strtolower($tableName)?>/")
<?php
foreach ($fields as $field) {
    echo '     * @ApiParams(name="' . $field->getName() . '", nullable=' . ($field->isNullable() ? 'true' : 'false') . ', type="' . $field->getType() . '", sample="", description="")' . "\n";
}
?>
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *     '<?=strtolower($tableName)?>': [
     *         {
<?php
foreach ($fields as $field) {
echo "     *            '" . $field->getName() ."': '', \n";
}
?>
     *         },
     *     ],
     *     'message': 'Ok'
     * }")
     */
    public function putAction()
    {
        $this->_response->setHttpResponseCode(200);
        $this->_helper->json(array('action' => 'put'));
    }

    /**
     * @ApiDescription(section="<?=$tableName?>", description="Table <?=$tableName?>")
     * @ApiMethod(type="delete")
     * @ApiRoute(name="/<?=strtolower($apiNamespace)?>/<?=strtolower($tableName)?>/")
<?php echo '     * @ApiParams(name="' . $primaryKey->getName() . '", nullable=' . ($primaryKey->isNullable() ? 'true' : 'false') . ', type="' . $primaryKey->getType() . '", sample="", description="")' . "\n";?>
     * @ApiReturnHeaders(sample="HTTP 200 OK")
     * @ApiReturn(type="object", sample="{
     *     '<?=strtolower($tableName)?>': '',
     *     'message': 'Ok'
     * }")
     */
    public function deleteAction()
    {

        $primaryKey = $this->getRequest()->getParam('<?=$primaryKey->getName()?>', false);

        if ($primaryKey != false) {

            $mapper = new Mappers\<?=$tableName?>();
            $model = $mapper->find($primaryKey);

            if (empty($model)) {
                $this->_response->setHttpResponseCode(400);
                $this->_helper->json(
                    array(
                        'message' => '<?=$primaryKey->getName()?> not exist'
                    )
                );
            } else {

                $model->delete();

                $this->_response->setHttpResponseCode(200);
                $this->_helper->json(
                    array(
                        '<?=strtolower($tableName)?>' => '',
                        'message' => 'Ok'
                    )
                );

            }

        } else {

            $this->_response->setHttpResponseCode(400);
            $this->_helper->json(
                array(
                    'message' => '<?=$primaryKey->getName()?> is required'
                )
            );

        }

    }

}