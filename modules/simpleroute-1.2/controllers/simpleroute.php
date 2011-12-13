<?php defined('SYSPATH') or die('No direct access allowed.');

class SimpleRoute_Controller extends Bluebox_Controller
{
    protected $baseModel = 'SimpleRoute';

    public function  __construct()
    {
        jquery::addPlugin('uiCSS');

        javascript::add('mustache');

        parent::__construct();
    }

    public function index()
    {
        $this->template->content = new View('generic/grid');

        // Setup the base grid object
        $grid = jgrid::grid($this->baseModel, array(
                'caption' => 'Simple Routes'
            )
        );

        // Add the base model columns to the grid
        $grid->add('simple_route_id', 'ID', array(
                'hidden' => true,
                'key' => true
            )
        );
        $grid->add('name', 'Name');
        //$grid->add('pattern', 'Pattern');

        // Add the actions to the grid
        $grid->addAction('simpleroute/edit', 'Edit', array(
                'arguments' => 'simple_route_id'
            )
        );
        $grid->addAction('simpleroute/delete', 'Delete', array(
                'arguments' => 'simple_route_id'
            )
        );

        // Let plugins populate the grid as well
        $this->grid = $grid;
        plugins::views($this);

        // Produce a grid in the view
        $this->view->grid = $this->grid->produce();
    }

    protected function prepareUpdateView($baseModel = NULL)
    {
        $types = array();

        switch(Telephony::getDriverName())
        {
            case 'FreeSwitch':
                $types = array(
                    SimpleRoute::TYPE_SIMPLE => 'Short Hand',
                    SimpleRoute::TYPE_REGEX => 'Regex'
                );
                break;

            default:
                $types = array(
                    SimpleRoute::TYPE_SIMPLE => 'Short Hand'
                );
        }

        $patternTemplate = new View('simpleroute/pattern.mus', array('mustache_template' => FALSE));

        $patternTemplate = json_encode((string)$patternTemplate);

        $patternTemplate = str_replace(array('\n', '  '), '', $patternTemplate);

        $this->view->patternTemplate = $patternTemplate;

        $this->view->types = $types;

        parent::prepareUpdateView($baseModel);
    }

    public function route_editor() {
	if (array_key_exists("submit",$_REQUEST)) {
		print "
<script type=\"text/javascript\" src=\"/assets/js/jquery/jquery-1.4.2.min.js\"></script>
<script type=\"text/javascript\" src=\"/assets/js/jquery/jquery.helper.js\"></script>
<script type=\"text/javascript\" src=\"/assets/js/jquery/jquery.jgrowl.js\"></script>

<link rel=\"stylesheet\" type=\"text/css\" href=\"/assets/css/jquery/jquery.jgrowl.css\" media=\"screen\" />


NO CONTENT

<script type=\"text/javascript\">
//<![CDATA[
$(document).ready(function () {";
		if (array_key_exists('confirm',$_REQUEST['submit'])) {
			$r=json_encode($_REQUEST['route']);
			if (array_key_exists("magicrownumber",$_REQUEST)) {
				$rownum=$_REQUEST["magicrownumber"];
				$action='updated';
			} else {
				$rownum="null";
				$action='added';
			}
			print "
	update_row($rownum,$r);
	php.success({\"a\":{\"evalScript\":[{\"foo\":\"$.jGrowl('Route $action!', { theme: 'success', life: 5000 });\"}]},\"q\":[]}, true);
";
		}
		print "});\n//]]>\n</script>\n";
		exit;
	}

	# magicrownumber=the row in the table we're updating. It's magic because it is changed when rows are shuffled.
	if (array_key_exists('magicrownumber',$_REQUEST)) {
		$this->view->magicrownumber=$_REQUEST['magicrownumber'];
	} else {
		$this->view->magicrownumber=0;
	}

	if (array_key_exists('route',$_REQUEST)) {
		$this->view->route=$_REQUEST['route'];
	} else {
		$this->view->route=array('destination'=>5,'trunk'=>1,'dialstring'=>'$1','clid_name'=>'','clid_number'=>'');
	}
	$this->view->title="Edit Route";
	$this->view->trunks=array();
        foreach (Doctrine::getTable('Trunk')->findAll(Doctrine::HYDRATE_ARRAY) AS $trunk) {
		$this->view->trunks[$trunk["trunk_id"]]=$trunk["name"];
        }

	$this->view->destinations=array();
        foreach (Doctrine::getTable('SimpleRoute')->findAll(Doctrine::HYDRATE_ARRAY) AS $dest) {
		$this->view->destinations[$dest["simple_route_id"]]=$dest["name"];
        }
    }

    protected function pre_save(&$object)
    {
        $patterns = $object['patterns'];

        foreach($patterns as $key => $pattern)
        {
            if (empty($pattern))
            {
                unset($patterns[$key]);
            }
        }

        if (empty($patterns))
        {
            throw new Exception('You must provide at least one pattern');
        }

        $object['patterns'] = array_values($patterns);

        parent::pre_save($object);
    }

    protected function save_succeeded(&$object)
    {
        parent::save_succeeded($object);
        
        // One of those nasty but functionaly things...
        $trunks = Doctrine::getTable('Trunk')->findAll();

        foreach ($trunks as $trunk)
        {
            if (empty($trunk['plugins']['simpleroute']['patterns']))
            {
                continue;
            }

            if (!isset($trunk['plugins']['simpleroute']['patterns'][$object['simple_route_id']]))
            {
                continue;
            }

            kohana::log('debug', 'Rebuilding trunk ' .$trunk['trunk_id'] .' to apply the changes to simple route ' .$object['simple_route_id']);

            $trunk->markModified('name');

            $trunk->save();
        }
    }

    protected function delete_succeeded(&$object)
    {
        parent::delete_succeeded($object);
        
        $identifier = $object->identifier();

        if (empty($identifier['simple_route_id']))
        {
            return;
        }

        // One of those nasty but functionaly things...
        $trunks = Doctrine::getTable('Trunk')->findAll();

        foreach ($trunks as $trunk)
        {
            if (empty($trunk['plugins']['simpleroute']['patterns']))
            {
                continue;
            }

            if (!isset($trunk['plugins']['simpleroute']['patterns'][$identifier['simple_route_id']]))
            {
                $patterns = $trunk['plugins']['simpleroute']['patterns'];

                unset($patterns[$identifier['simple_route_id']]);

                $trunk['plugins']['simpleroute']['patterns'] = $patterns;
            }

            kohana::log('debug', 'Rebuilding trunk ' .$trunk['trunk_id'] .' to remove simple route ' .$identifier['simple_route_id']);

            $trunk->save();
        }
    }
}
