<?php
/**
 * FeatureCode_Controller.php - FeatureCode Controller
 *
 * Allows you to specify arbitrary XML as a "feature code" and then assign/map those codes to destinations/numbers
 *
 * @author Darren Schreiber
 * @license MPL
 * @package Bluebox
 * @subpackage FeatureCode
 */
class FeatureCode_Controller extends Bluebox_Controller {
  protected $baseModel = 'FeatureCode';
  protected $writable = array(
			      'name'
			      ,'description'
			      ,'registry'
			      );

  public function index() {
    $this->template->content = new View('generic/grid');
    // Build a grid with a hidden device_id, device_type, and add an option for the user to select the display columns
    $this->grid = jgrid::grid($this->baseModel, array(
						      'caption' => 'Feature Codes'
						      ,'multiselect' => TRUE
						      )
			      )
      ->add('feature_code_id', 'ID', array(
					   'hidden' => TRUE
					   ,'key' => TRUE
					   )
	    )
      ->add('name', 'Name', array(
				  'width' => '200'
				  ,'search' => FALSE
				  )
	    )
      ->add('description', 'Description', array(
						'width' => '300'
						,'search' => TRUE
						)
	    )
      ->add('sections', 'Sections', array(
					  'align' => 'center'
					  ,'sortable' => TRUE
					  ,'callback' => array(
							       'function' => array($this, '_showSections')
							       ,'arguments' =>  array('registry')
							       )
					  )
	    )
      ->navButtonAdd('Columns', array(
				      'onClickButton' => 'function () {  $(\'#{table_id}\').setColumns(); }'
				      ,'buttonimg' => url::base() . 'assets/css/jqGrid/table_insert_column.png'
				      ,'title' => 'Show/Hide Columns'
				      ,'noCaption' => TRUE
				      ,'position' => 'first'
				      )
		     )
      ->addAction('featurecode/edit', 'Edit', array(
						    'arguments' => 'feature_code_id'
						    ,'width' => '120'
						    )
		  )
      ->addAction('featurecode/delete', 'Delete', array(
							'arguments' => 'feature_code_id'
							,'width' => '120'
							)
		  )
      ->navGrid(array(
		      'del' => TRUE
		      )
		);

    // dont foget to let the plugins add to the grid!
    plugins::views($this);

    // Produces the grid markup or JSON
    $this->view->grid = $this->grid->produce();
  }

  public function create() {
    javascript::add('editarea/edit_area_full.js');
    javascript::add('featurecode.js');
    stylesheet::add('featurecode.css');

    $this->template->content = new View(Router::$controller . '/update');
    $this->view->title = ucfirst(Router::$method) .' Feature Code';

    $this->featureCode = new FeatureCode();

    // Are we supposed to be saving stuff? (received a form post?)
    if ( $this->submitted() ) {
      if ( $this->formSave($this->featureCode) ) {
	url::redirect(Router_Core::$controller);
      }
    }

    // Allow our device object to be seen by the view
    $this->view->featureCode = $this->featureCode;

    // Execute plugin hooks here, after we've loaded the core data sets
    plugins::views($this);
  }

  public function edit($id = NULL) {
    javascript::add('editarea/edit_area_full.js');
    javascript::add('featurecode.js');
    stylesheet::add('featurecode.css');

    $this->template->content = new View(Router::$controller . '/update');
    $this->view->title = ucfirst(Router::$method) .' Feature Code';
    $this->featureCode = Doctrine::getTable('FeatureCode')->find($id);

    // Was anything retrieved? If no, this may be an invalid request
    if (!$this->featureCode) {
      // Send any errors back to the index
      $error = i18n('Unable to locate Feature Code id %1$d!', $id)->sprintf()->s();
      message::set($error, array(
				 'translate' => false,
				 'redirect' => Router::$controller . '/index'
				 ));
      return true;
    }

    if ($this->submitted()) {
      if ($this->formSave($this->featureCode)) {
	url::redirect(Router_Core::$controller);
      }
    }

    // Allow our feature object to be seen by the view
    $this->view->featurecode = $this->featureCode;

    // Execute plugin hooks here, after we've loaded the core data sets
    plugins::views($this);
  }

  protected function formSave($featureCode, $saveMessage = NULL, $saveEvents = array()) {
    $errors = array();
    $cleaned = array();

    libxml_use_internal_errors(TRUE);

    // Determine name of the base model for the object being saved
    if (get_parent_class($featureCode) == 'Bluebox_Record') {
      $baseClass = get_class($featureCode);
    } else {
      $baseClass = get_parent_class($featureCode);
    }

    // Import any post vars with the key of this model into the object
    $formData = $this->input->post(strtolower($baseClass), array());
    $featureCode->fromArray($formData);

    foreach ( $featureCode->registry as $section => $xml ) {
      $dom = new DOMDocument('1.0');
      $dom->formatOutput = FALSE;

      if ( strlen(trim($xml)) == 0 ) {
	kohana::log('debug', 'Section: ' . $section . ' Empty XML');
	$cleaned[$section] = '';
	continue;
      }
      
      try {
	if ( $dom->loadXML(trim($xml)) ) {
	  $cleaned[$section] = $dom->saveXML();
	  kohana::log('debug', 'Section: ' . $section . ' Cleaned XML: ' . $dom->saveXML());
	  continue;
	} else {
	  $errors[] = ucfirst($section);
	}
      } catch(Exception $e) {
	$errors[] = ucfirst($section);
      }
    }

    if ( count($errors) > 0 ) {
      $errstr = '<br />';

      message::set('Please correct the XML errors in these sections: ' . implode(', ', $errors));
      return FALSE;
    }

    $featureCode->registry = $cleaned;

    kohana::log('debug', 'Successfully validated XML');

    return parent::formSave($featureCode, $saveMessage, $saveEvents);
  }

  public function _showSections($null, $registry) {
    if ( empty($registry) ) {
      return 'None';
    }

    return array_sum(array_map('featurecode_defined_sections', array_keys($registry), array_values($registry))) . ' sections';
  }
}

function featurecode_defined_sections($section, $xml) {
  return strlen($xml) > 0 ? 1 : 0;
}