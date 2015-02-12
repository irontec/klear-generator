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

class <?=$apiNamespace?>_<?=$tableName?>Controller extends Iron_Controller_Rest_BaseController
{

    public function init()
    {
        parent::init();
    }

    public function optionsAction()
    {

        $options = array(
            'GET' => array(),
            'POST' => array(),
            'PUT' => array(),
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

        $this->status->setCode(200);
        $this->view->options = options;

    }

    public function indexAction()
    {

        $mapper = new Mappers\<?=$tableName?>();
        $items = $mapper->fetchAllToArray();

        $this->status->setCode(200);

        $this->view->message = 'Ok';
        $this->view->total = sizeof($items);
        $this->view-><?=strtolower($tableName)?> = $items;

    }

    /**
     * [disabled]ApiDescription(section="<?=$tableName?>", description="Table <?=$tableName?>")
     * [disabled]ApiMethod(type="get")
     * [disabled]ApiRoute(name="/<?=strtolower($apiNamespace)?>/<?=strtolower($tableName)?>/")
<?php echo '     * [disabled]ApiParams(name="' . $primaryKey->getName() . '", nullable=' . ($primaryKey->isNullable() ? 'true' : 'false') . ', type="' . $primaryKey->getType() . '", sample="", description="")' . "\n";?>
     * [disabled]ApiReturnHeaders(sample="HTTP 200 OK")
     * [disabled]ApiReturn(type="object", sample="{
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

            $this->view->message = 'Ok';

            if (empty($item)) {
                $this->view-><?=strtolower($tableName)?> = array();
            } else {
                $this->view-><?=strtolower($tableName)?> = $item->toArray();
            }

            $this->status->setCode(200);

        } else {

            $this->status->setCode(204);

        }

    }

    /**
     * [disabled]ApiDescription(section="<?=$tableName?>", description="Table <?=$tableName?>")
     * [disabled]ApiMethod(type="post")
     * [disabled]ApiRoute(name="/<?=strtolower($apiNamespace)?>/<?=strtolower($tableName)?>/")
<?php
foreach ($fields as $field) {
    if ($field->getName() != $primaryKey->getName()) {
        echo '     * [disabled]ApiParams(name="' . $field->getName() . '", nullable=' . ($field->isNullable() ? 'true' : 'false') . ', type="' . $field->getType() . '", sample="", description="")' . "\n";
    }
}
?>
     * [disabled]ApiReturnHeaders(sample="HTTP 201 OK")
     * [disabled]ApiReturn(type="object", sample="{
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
        $this->status->setCode(200);
    }

    /**
     * [disabled]ApiDescription(section="<?=$tableName?>", description="Table <?=$tableName?>")
     * [disabled]ApiMethod(type="put")
     * [disabled]ApiRoute(name="/<?=strtolower($apiNamespace)?>/<?=strtolower($tableName)?>/")
<?php
foreach ($fields as $field) {
    echo '     * [disabled]ApiParams(name="' . $field->getName() . '", nullable=' . ($field->isNullable() ? 'true' : 'false') . ', type="' . $field->getType() . '", sample="", description="")' . "\n";
}
?>
     * [disabled]ApiReturnHeaders(sample="HTTP 200 OK")
     * [disabled]ApiReturn(type="object", sample="{
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
        $this->status->setCode(200);
    }

    /**
     * [disabled]ApiDescription(section="<?=$tableName?>", description="Table <?=$tableName?>")
     * [disabled]ApiMethod(type="delete")
     * [disabled]ApiRoute(name="/<?=strtolower($apiNamespace)?>/<?=strtolower($tableName)?>/")
<?php echo '     * [disabled]ApiParams(name="' . $primaryKey->getName() . '", nullable=' . ($primaryKey->isNullable() ? 'true' : 'false') . ', type="' . $primaryKey->getType() . '", sample="", description="")' . "\n";?>
     * [disabled]ApiReturnHeaders(sample="HTTP 200 OK")
     * [disabled]ApiReturn(type="object", sample="{
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

                $this->status->setCode(400);
                $this->view->message = '<?=$primaryKey->getName()?> not exist';

            } else {

                $model->delete();

                $this->status->setCode(200);
                $this->view->message = 'Ok';

            }

        } else {

            $this->status->setCode(400);
            $this->view->message = '<?=$primaryKey->getName()?> is required';

        }

    }

}