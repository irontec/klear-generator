<?="<?php\n"?>
<?php
$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";

$fields = Generator_Db::describeTable($tableName);

$primaryKey = $fields->getPrimaryKey();

$enumFields = array();
$fsoFields = array();
echo "/**\n";
foreach ($fields as $field) {
    echo " * " . $field->getName() . "\n";
}
echo "*/\n";
?>
use <?=$namespace?>Model as Models;
use <?=$namespace?>Mapper\Sql as Mappers;

class Rest_<?=$tableName?>Controller extends Zend_Rest_Controller
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

    public function getAction()
    {
        $this->_response->setHttpResponseCode(200);
        $this->_helper->json(array('action' => 'get'));
    }

    public function postAction()
    {
        $this->_response->setHttpResponseCode(200);
        $this->_helper->json(array('action' => 'post'));
    }

    public function putAction()
    {
        $this->_response->setHttpResponseCode(200);
        $this->_helper->json(array('action' => 'put'));
    }

    public function deleteAction()
    {

        $id = $this->getRequest()->get('id', false);

        if (!$id) {

            $mapper = new Mappers\<?=$tableName?>();
            $model = $mapper->find($id);

            if (!empty($model)) {

                $this->_response->setHttpResponseCode(200);
                $this->_helper->json(
                    array(
                        'status' => true,
                        'message' => 'item Deleted'
                    )
                );

            } else {

                $this->_response->setHttpResponseCode(400);
                $this->_helper->json(
                    array(
                        'status' => false,
                        'message' => 'item not exist'
                    )
                );

            }

        } else {

            $this->_response->setHttpResponseCode(400);
            $this->_helper->json(
                array(
                    'status' => false,
                    'message' => 'id is required'
                )
            );

        }

    }

}